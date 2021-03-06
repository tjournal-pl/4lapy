<?php

namespace FourPaws\SaleBundle\EventController;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Application as BitrixApplication;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Event as BitrixEvent;
use Bitrix\Main\EventManager;
use Bitrix\Main\EventResult;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\BasketItemCollection;
use Bitrix\Sale\Internals\DiscountCouponTable;
use Bitrix\Sale\Internals\Fields;
use Bitrix\Sale\Order;
use Bitrix\Sale\OrderTable;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaymentCollection;
use Exception;
use FourPaws\App\Application;
use FourPaws\App\BaseServiceHandler;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\MainTemplate;
use FourPaws\App\Tools\StaticLoggerTrait;
use FourPaws\Helpers\BxCollection;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\PersonalBundle\Exception\CouponNotFoundException;
use FourPaws\PersonalBundle\Repository\Table\BasketsDiscountOfferTable;
use FourPaws\PersonalBundle\Service\ChanceService;
use FourPaws\PersonalBundle\Service\CouponService;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use FourPaws\PersonalBundle\Service\PersonalOffersService;
use FourPaws\PersonalBundle\Service\PiggyBankService;
use FourPaws\SaleBundle\Discount\Action\Action\DetachedRowDiscount;
use FourPaws\SaleBundle\Discount\Action\Action\DiscountFromProperty;
use FourPaws\SaleBundle\Discount\Action\Condition\BasketFilter;
use FourPaws\SaleBundle\Discount\Action\Condition\BasketQuantity;
use FourPaws\SaleBundle\Discount\Gift;
use FourPaws\SaleBundle\Discount\Utils\Manager;
use FourPaws\SaleBundle\Enum\OrderStatus;
use FourPaws\SaleBundle\Exception\ForgotBasket\FailedToUpdateException;
use FourPaws\SaleBundle\Repository\CouponStorage\CouponStorageInterface;
use FourPaws\SaleBundle\Repository\OrderNumberTable;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\ForgotBasketService;
use FourPaws\SaleBundle\Service\NotificationService;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\PaymentService;
use FourPaws\SaleBundle\Service\UserAccountService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\NotFoundException;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserSearchInterface;
use FourPaws\UserBundle\Service\UserService;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class Event
 *
 * Обработчики событий
 *
 * @package FourPaws\SaleBundle\EventController
 */
class Event extends BaseServiceHandler
{
    use StaticLoggerTrait;

    protected static $isEventsDisable = false;

    public static function disableEvents(): void
    {
        self::$isEventsDisable = true;
    }

    public static function enableEvents(): void
    {
        self::$isEventsDisable = false;
    }

    /**
     * @param EventManager $eventManager
     *
     * @return mixed|void
     */
    public static function initHandlers(EventManager $eventManager): void
    {
        parent::initHandlers($eventManager);

        static::initHandlerCompatible('OnBuildGlobalMenu', [self::class, 'addRetailRocketOrderReportToAdminMenu',], 'main');

        $module = 'sale';
        ###   Обработчики скидок       ###

        /* Инициализация кастомных правил работы с корзиной */
        static::initHandlerCompatible('OnCondSaleActionsControlBuildList', [Gift::class, 'GetControlDescr'], $module);
        static::initHandlerCompatible('OnCondSaleActionsControlBuildList', [BasketFilter::class, 'GetControlDescr'], $module);
        static::initHandlerCompatible('OnCondSaleActionsControlBuildList', [BasketQuantity::class, 'GetControlDescr'], $module);
        static::initHandlerCompatible('OnCondSaleActionsControlBuildList', [DiscountFromProperty::class, 'GetControlDescr'], $module);
        static::initHandlerCompatible('OnCondSaleActionsControlBuildList', [DetachedRowDiscount::class, 'GetControlDescr'], $module);
        /* Здесь дополнительная обработка акций */
        static::initHandler('OnAfterSaleOrderFinalAction', [Manager::class, 'OnAfterSaleOrderFinalAction'], $module);
        static::initHandler('OnBeforeSaleOrderFinalAction', [Manager::class, 'OnBeforeSaleOrderFinalAction'], $module);

        ###   Обработчики скидок EOF   ###

        /* сброс кеша малой корзины */
        $module = 'sale';
        static::initHandler('OnSaleBasketItemSaved', [self::class, 'resetBasketCache'], $module);
        static::initHandler('OnSaleBasketItemEntityDeleted', [self::class, 'resetBasketCache'], $module);

        /* предотвращение попадания отложенных товаров в заказ */
        static::initHandler('OnSaleBasketItemBeforeSaved', [self::class, 'removeDelayedItems'], $module);

        /* добавление марок в заказ */
//        static::initHandler('OnSaleOrderBeforeSaved', [self::class, 'addMarksToOrderBasket'], $module);

        /* Добавление газеты октябрь (3006893) в заказ */
//        static::initHandler('OnSaleOrderBeforeSaved', [self::class, 'addOctoberNewspaper'], $module);

        /* генерация номера заказа */
        static::initHandlerCompatible('OnBeforeOrderAccountNumberSet', [self::class, 'updateOrderAccountNumber'], $module);
        static::initHandler('OnSaleOrderEntitySaved', [self::class, 'unlockOrderTables'], $module);

        /** отправка email */
        // новый заказ
        static::initHandler('OnSaleOrderEntitySaved', [self::class, 'sendNewOrderMessage'], $module);
        // смена платежной системы у заказа
        static::initHandler('OnSalePaymentEntitySaved', [self::class, 'sendNewOrderMessage'], $module);
        // оплата заказа
        static::initHandler('OnSaleOrderPaid', [self::class, 'sendOrderPaymentMessage'], $module);
        // отмена заказа
        static::initHandler('OnSaleOrderCanceled', [self::class, 'sendOrderCancelMessage'], $module);
        // смена статуса заказа
        static::initHandler('OnSaleStatusOrderChange', [self::class, 'sendOrderStatusMessage'], $module);

        /* отмена заказа, перешедшего в статус отмены */
        static::initHandler('OnSaleStatusOrderChange', [self::class, 'cancelOrder'], $module);

        /* очистка кеша заказа */
        static::initHandler('OnSaleOrderSaved', [self::class, 'clearOrderCache'], $module);

        static::initHandler('OnSaleOrderSaved', [self::class, 'setCouponUsed'], $module);

//        static::initHandler('OnSaleOrderSaved', [self::class, 'addCouponForSecondOrder'], $module);
//        static::initHandler('OnSaleOrderEntitySaved', [self::class, 'addCouponForSecondOrder'], $module);

        /* Сохранение имени пользователя */
        static::initHandler('OnSaleOrderEntitySaved', [self::class, 'setNameAfterOrder'], $module);

        /** обновление бонусного счета пользователя и бонусного процента пользователя */
        $module = 'main';
        static::initHandlerCompatible('OnAfterUserLogin', [self::class, 'updateUserAccountBalance'], $module);
        static::initHandlerCompatible('OnAfterUserAuthorize', [self::class, 'updateUserAccountBalance'], $module);
        static::initHandlerCompatible('OnAfterUserLoginByHash', [self::class, 'updateUserAccountBalance'], $module);

        /* Забытая корзина */
        $module = 'sale';
        static::initHandler('OnSaleBasketItemSaved', [self::class, 'disableForgotBasketReminder'], $module);
        static::initHandler('OnSaleBasketItemEntityDeleted', [self::class, 'disableForgotBasketReminder'], $module);

        /* При добавлении в корзину */
        $module = 'sale';
        static::initHandler('OnBasketAdd', [self::class, 'addDiscountProperties'], $module);

        /* Добавление марок в корзину */
//        $module = 'sale';
//        static::initHandler('OnSaleBasketSaved', [self::class, 'addStampsToBasket'], $module);

        $module = 'sale';
        static::initHandler('OnOrderNewSendEmail', [self::class, 'cancelEventAddition'], $module);
        static::initHandler('OnOrderPaySendEmail', [self::class, 'cancelEventAddition'], $module);
    }

    /**
     * Добавляет свойство для региональных скидок
     * @param $ID
     * @param $arFields
     * @throws ArgumentException
     * @throws ArgumentNullException
     */
    public function addDiscountProperties($ID, $arFields)
    {
        /** @var BasketService $basketService */
        $basketService = Application::getInstance()->getContainer()->get(BasketService::class);

        /** @var BasketItem $basketItem */
        if($basketItem = $basketService->getBasket()->getItemById($ID)){
            $basketService->updateRegionDiscountForBasketItem($basketItem);
        }
    }

    public static function updateUserAccountBalance(): void
    {
        try {
            /** @var MainTemplate $template */
            $template = MainTemplate::getInstance(BitrixApplication::getInstance()
                ->getContext());
            /** выполняем только при пользовательской авторизации(это аякс), либо из письма и обратных ссылок(это personal)
             *  так же чекаем что это не страница заказа
             */
            //FIXME эта проверка работает неправильно из-за расчета делавшего это программиста на наличие "index.php" в url`е авторизации.
            //Сейчас index.php в url нет и метод на авторизации не работает.
            //Нужно переделать апдейт баланса асинхронно, после чего отрефакторить то, что тут происходит
            // UPD: Условия исправлено, теперь это потенциально опасное место
            if (!$template->hasUserAuth()) {
                return;
            }
            $container = Application::getInstance()
                ->getContainer();
            $userService = $container->get(CurrentUserProviderInterface::class);
            $userAccountService = $container->get(UserAccountService::class);
            $user = $userService->getCurrentUser();
            [
                ,
                $bonus
            ] = $userAccountService->refreshUserBalance($user);
            $userService->refreshUserBonusPercent($user, $bonus);
        } catch (NotAuthorizedException $e) {
            // обработка не требуется
        } catch (Exception $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('failed to update user account balance: ' . $e->getMessage());
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ObjectNotFoundException
     * @throws SystemException
     */
    public static function sendNewOrderMessage(BitrixEvent $event): void
    {
        if (self::$isEventsDisable) {
            return;
        }

        $entity = $event->getParameter('ENTITY');

        if ($entity instanceof Order) {
            $order = $entity;
            if ($order->isCanceled()) {
                return;
            }
        } elseif ($entity instanceof Payment) {
            /** @var PaymentCollection $collection */
            $collection = $entity->getCollection();
            $order = $collection->getOrder();
        } else {
            return;
        }

        /** @var OrderService $orderService */
        $orderService = Application::getInstance()
            ->getContainer()
            ->get(
                OrderService::class
            );
        if ($orderService->isManzanaOrder($order)) {
            // пропускаются заказы из манзаны
            return;
        }

        /** @var NotificationService $notificationService */
        $notificationService = Application::getInstance()
            ->getContainer()
            ->get(NotificationService::class);

        $notificationService->sendNewOrderMessage($order);
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ObjectNotFoundException
     * @throws SystemException
     */
    public static function sendOrderPaymentMessage(BitrixEvent $event): void
    {
        if (self::$isEventsDisable) {
            return;
        }

        /** @var Order $order */
        $order = $event->getParameter('ENTITY');
        if ($order->isCanceled()) {
            return;
        }

        /** @var OrderService $orderService */
        $orderService = Application::getInstance()
            ->getContainer()
            ->get(
                OrderService::class
            );
        if ($orderService->isManzanaOrder($order)) {
            return;
        }

        /** @var NotificationService $notificationService */
        $notificationService = Application::getInstance()
            ->getContainer()
            ->get(NotificationService::class);

        $notificationService->sendOrderPaymentMessage($order);
    }

    /**
     * @param BitrixEvent $event
     *
     * @return EventResult
     * @throws ArgumentNullException
     * @throws ArgumentTypeException
     */
    public static function removeDelayedItems(BitrixEvent $event): EventResult
    {
        $basketItem = $event->getParameter('ENTITY');
        $result = new EventResult(EventResult::SUCCESS);
        if ($basketItem instanceof BasketItem) {
            /** @var BasketItemCollection $collection */
            $collection = $basketItem->getCollection();
            if ($collection->getOrderId() && $basketItem->isDelay()) {
                $result = new EventResult(EventResult::ERROR);
            }
        }

        return $result;
    }

    /**
     * @param BitrixEvent $event
     *
     * @return EventResult
     */
    public static function addOctoberNewspaper(BitrixEvent $event): EventResult
    {
        try {
            $curDate = new \DateTime();

            if (!(((int)$curDate->format('Y') === 2019) && ((int)$curDate->format('m') === 10) && ((int)$curDate->format('d') < 28))) {
                return new EventResult(EventResult::SUCCESS);
            }

            /** @var BasketService $basketService */
            $basketService = Application::getInstance()->getContainer()->get(BasketService::class);
            /** @var OrderService $orderService */
            $orderService = Application::getInstance()->getContainer()->get(OrderService::class);

            /** @var Order $order */
            $order = $event->getParameter('ENTITY');

            if ($orderService->isSubscribe($order)) {
                $propCopyOrderId = $orderService->getOrderPropertyByCode($order, 'COPY_ORDER_ID');
                $isFirsSubscribeOrder = ($propCopyOrderId) ? !\boolval($propCopyOrderId->getValue()) : true;

                if (!$isFirsSubscribeOrder) {
                    return new EventResult(EventResult::SUCCESS);
                }
            }

            /** добавляем подарок только для нового заказа */
            if (!$order->isNew()) {
                return new EventResult(EventResult::SUCCESS);
            }

            if ($order instanceof Order) {
                $offer = ElementTable::getList([
                    'select' => ['ID', 'XML_ID'],
                    'filter' => ['XML_ID' => BasketService::GIFT_NOVEMBER_NEWSPAPER_XML_ID],
                    'limit' => 1,
                ])->fetch();

                if (!$offer || empty($offer) || !$offer['ID']) {
                    return new EventResult(EventResult::SUCCESS);
                }

                $offerId = $offer['ID'];

                /** @var Basket $basket */
                $basket = $order->getBasket();

                $items = $basket->getOrderableItems();
                if ($items->isEmpty())
                {
                    return new EventResult(EventResult::ERROR);
                }

                /** @var BasketItem $item */
                foreach ($items as $itemIndex => $item)
                {
                    if ($item->getProductId() === $offerId)
                    {
                        $basketService->deleteOfferFromBasket($item->getId());
                    }
                }

                $basket->save();

                $basketItem = $basketService->addOfferToBasket($offerId, 1, [], true, $basket);

                if ($basketItem instanceof BasketItem) {
                    $order->setFieldNoDemand(
                        'PRICE',
                        $order->getBasket()->getOrderableItems()->getPrice() + $order->getDeliveryPrice()
                    );

                    return new EventResult(EventResult::SUCCESS);
                }
            }
        } catch (\Exception $e) {
            $logger = LoggerFactory::create('october newspaper');
            $logger->critical('failed to add october newspaper for order: ' . $e->getMessage());
        }

        return new EventResult(EventResult::ERROR);
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     */
    public static function sendOrderCancelMessage(BitrixEvent $event): void
    {
        if (self::$isEventsDisable) {
            return;
        }

        /** @var Order $order */
        $order = $event->getParameter('ENTITY');

        // если свойств нет, то это удаление заказа
        if ($order->getPropertyCollection()
            ->isEmpty()) {
            return;
        }

        /** @var OrderService $orderService */
        $orderService = Application::getInstance()
            ->getContainer()
            ->get(
                OrderService::class
            );
        if ($orderService->isManzanaOrder($order)) {
            return;
        }

        /** @var NotificationService $notificationService */
        $notificationService = Application::getInstance()
            ->getContainer()
            ->get(NotificationService::class);

        $notificationService->sendOrderCancelMessage($order);
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws SystemException
     * @throws ObjectPropertyException
     * @throws ArgumentException
     * @throws ApplicationCreateException
     */
    public static function sendOrderStatusMessage(BitrixEvent $event): void
    {
        if (self::$isEventsDisable) {
            return;
        }

        /** @var Order $order */
        $order = $event->getParameter('ENTITY');
        if ($order->isCanceled()) {
            return;
        }

        /** @var OrderService $orderService */
        $orderService = Application::getInstance()
            ->getContainer()
            ->get(
                OrderService::class
            );

        if ($orderService->isManzanaOrder($order)) {
            return;
        }

        /** @var NotificationService $notificationService */
        $notificationService = Application::getInstance()
            ->getContainer()
            ->get(NotificationService::class);

        $notificationService->sendOrderStatusMessage($order);
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws ArgumentException
     * @throws ObjectNotFoundException
     * @throws SystemException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws ObjectException
     * @throws Exception
     */
    public static function cancelOrder(BitrixEvent $event): void
    {
        if (self::$isEventsDisable) {
            return;
        }

        /** @var Order $order */
        $order = $event->getParameter('ENTITY');

        // если свойств нет, то это удаление заказа
        if ($order->getPropertyCollection()
            ->isEmpty()) {
            return;
        }

        if (\in_array(
                $order->getField('STATUS_ID'),
                [
                    OrderStatus::STATUS_CANCEL_COURIER,
                    OrderStatus::STATUS_CANCEL_PICKUP
                ],
                true
            )
            && !$order->isCanceled()) {
            /** @var PaymentService $paymentService */
            $paymentService = Application::getInstance()
                ->getContainer()
                ->get(PaymentService::class);
            $paymentService->cancelPayment($order, 0, false);

            $order->setField('CANCELED', 'Y');
            $order->save();
        }
    }

    /**
     * @param $orderId
     * @param $type
     *
     * @return false|int
     */
    public static function updateOrderAccountNumber($orderId, $type)
    {
        $result = false;

        if ($type === 'NUMBER') {
            try {
                //$defaultNumber = (int)Option::get('sale', 'account_number_data', 0); // "Начальное число" в настройке "Шаблон генерации номера заказа"
                $newNumber = OrderNumberTable::addCustomized($orderId);

                if ($newNumber > 9000000)
                {
                    static::getLogger()
                        ->critical(
                            \sprintf(
                                'с номера 9999999 возникнет пересечение номеров с внешними заказами. Текущий номер: %s',
                                $newNumber
                            )
                        );
                }

                $result = $newNumber;
            } catch (Exception $e) {
                static::getLogger()
                    ->error(
                        sprintf(
                            'failed to set order %s account number: %s: %s',
                            $orderId,
                            \get_class($e),
                            $e->getMessage()
                        )
                    );
            }
        }

        if (OrderService::$isSubscription || OrderSubscribeService::$isSubscription) {
            $result = 'p' . $result;
        }

        return $result;
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws ArgumentTypeException
     */
    public static function unlockOrderTables(BitrixEvent $event)
    {
        /** @var Order $order */
        $order = $event->getParameter('ENTITY');

        if ($order->isCanceled()) {
            return;
        }

        try {
            BitrixApplication::getConnection()
                ->query('UNLOCK TABLES');
        } catch (Exception $e) {
            /** @noinspection NullPointerExceptionInspection */
            static::getLogger()
                ->error('failed to unlock order tables', [
                    'order' => $event->getParameter('ENTITY')
                        ->getId(),
                ]);
        }
    }

    /**
     * @param BitrixEvent $event
     */
    public static function clearOrderCache(BitrixEvent $event): void
    {
        if (self::$isEventsDisable) {
            return;
        }

        /** @var Order $order */
        $order = $event->getParameter('ENTITY');

        TaggedCacheHelper::clearManagedCache([
            'order:' . $order->getField('USER_ID'),
            'personal:order:' . $order->getField('USER_ID'),
            'order:item:' . $order->getId(),
        ]);
    }

    /**
     * @param BitrixEvent $event
     */
    public static function setCouponUsed(BitrixEvent $event): void
    {
        try {
            /** @var CouponStorageInterface $couponStorage */
            $couponStorage = Application::getInstance()->getContainer()->get(CouponStorageInterface::class);
            $couponStorage->clear();

            /** @var Order $order */
            $order = $event->getParameter('ENTITY');
            $propertyCollection = $order->getPropertyCollection();
            $promocode = BxCollection::getOrderPropertyByCode($propertyCollection, 'PROMOCODE');
            if ($promocode) {
                $promocodeValue = $promocode->getValue();
            }

            // Отметка, что корзина по акции "20-20" использована. Следующие заказы пользователя в этот день снова начнут участвовать в розыгрыше купонов
            if (PersonalOffersService::is20thBasketOfferActive()) {
                $discountOfferQuery = BasketsDiscountOfferTable::query()
                    ->where('date_insert', '>=', (new DateTime())->setTime(0, 0, 0))
                    ->setSelect(['id'])
                    //->setLimit(1) // для сохранения логики возобновления участия в розыгрыше купонов нужно отметить флагом order_created все записи за этот день
                    ->setOrder(['id' => 'desc']);

                //if (isset($promocodeValue) && $promocodeValue) {
                    //$discountOfferQuery = $discountOfferQuery->where('promoCode', $promocodeValue);
                //} else {
                    //$discountOfferQuery = $discountOfferQuery->whereNull('promoCode');
                //}

                $userFilter = Query::filter()
                    ->logic('or');

                if ($fUserId = $order->getBasket()->getFUserId()) {
                    $userFilter = $userFilter->where('fUserId', $fUserId);
                }
                if ($userId = $order->getUserId()) {
                    $userFilter = $userFilter->where('userId', $userId);
                }
                if ($fUserId || $userId) {
                    $discountOfferQuery = $discountOfferQuery->where($userFilter);

                    $baskets20thOffer = $discountOfferQuery
                        ->exec()
                        ->fetchAll();
                    $baskets20thOfferIds = array_column($baskets20thOffer, 'id');

                    foreach ($baskets20thOfferIds as $baskets20thOfferId) {
                        BasketsDiscountOfferTable::update($baskets20thOfferId, [
                            'order_created' => 1,
                            'date_update'   => new DateTime(),
                        ]);
                    }
                    $logger = LoggerFactory::create('CouponPoolRepository', '20-20');
                    $logger->info(__FUNCTION__ . '. На основании корзины по акции 20-20 создан заказ. BasketIds: ' . implode(', ', $baskets20thOfferIds) . '. Купон: ' . ($promocodeValue ?? ''));
                }
            }

            if (isset($promocodeValue) && $promocodeValue)
            {
                // Деактивация купона в таблице Битрикса
                $bitrixCouponId = DiscountCouponTable::query()
                    ->setFilter([
                        'COUPON' => $promocodeValue,
                    ])
                    ->setSelect([
                        'ID',
                    ])
                    ->setLimit(1)
                    ->exec()
                    ->fetch();
                $bitrixCouponDeactivateResult = DiscountCouponTable::update($bitrixCouponId, ['ACTIVE' => 'N']);
                if (!$bitrixCouponDeactivateResult->isSuccess()) {
                    static::getLogger()
                        ->error(
                            sprintf(
                                '%s. failed to deactivate bitrix coupon: %s',
                                __FUNCTION__,
                                implode('.', $bitrixCouponDeactivateResult->getErrorMessages())
                            )
                        );
                }

                // Деактивация купона в HL-блоках
                $isPromoCodeProcessed = false;
                try {
                    /** @var CouponService $couponService */
                    $couponService = Application::getInstance()->getContainer()->get('coupon.service');
                    $couponService->setUsedStatusByNumber($promocodeValue);
                    $isPromoCodeProcessed = true;
                } catch (CouponNotFoundException $e) {
                }

                if (!$isPromoCodeProcessed)
                {
                    /** @var PersonalOffersService $personalOffersService */
                    $personalOffersService = Application::getInstance()->getContainer()->get('personal_offers.service');
                    if (!$personalOffersService->isNoUsedStatus($promocodeValue))
                    {
                        $personalOffersService->setUsedStatusByPromoCode($promocodeValue);
                    }
                }
            }
        } catch (Exception $e) {
            static::getLogger()
                ->error(
                    sprintf(
                        'failed to set coupon Used status: %s: %s',
                        \get_class($e),
                        $e->getMessage()
                    )
                );
        }
    }

    /**
     * @param BitrixEvent $event
     */
    public static function addCouponForSecondOrder(BitrixEvent $event): void
    {
        // --------- Проверка, что заказ оплачен, завершен, не отменен и хотя бы одно из этих полей было изменено ---------
        $isCheckNeeded = false;
        $eventType = $event->getEventType();
        if ($eventType === 'OnSaleOrderEntitySaved' && \FourPaws\PersonalBundle\Service\OrderService::STATUS_FINAL) {
            $isCheckNeeded = true;
        } elseif ($eventType === 'OnSaleOrderSaved') {
            $isNew = $event->getParameter('IS_NEW');
            $isChanged = $event->getParameter('IS_CHANGED');
            if ($isNew || $isChanged) {
                $isCheckNeeded = true;
            }
        }

        if (!$isCheckNeeded) {
            return;
        }

        /** @var Order $order */
        $order = $event->getParameter('ENTITY');
        /** @var Fields $fields */
        $fields = $order->getFields();
        $changedFields = $fields->getChangedKeys();

        if ($order->isCanceled()
            || (
                !in_array('PAYED', $changedFields, true)
                && !in_array('STATUS_ID', $changedFields, true)
                && !in_array('CANCELED', $changedFields, true)
            )
        ) {
            return;
        }

        $isPaid = $order->isPaid();
        $isFinished = in_array($order->getField('STATUS_ID'), \FourPaws\PersonalBundle\Service\OrderService::STATUS_FINAL, true);

        if (!$isPaid || !$isFinished) {
            return;
        }

        /** @var UserService $userService */
        $userService = Application::getInstance()->getContainer()->get(UserSearchInterface::class);
        $userId = $order->getUserId();
        try
        {
            $user = $userService->findOne($userId);
        } catch (NotFoundException $e)
        {
        }
        if (isset($user) && !$user->isGotSecondOrderCoupon()) { // Если еще не был выдан купон на второй заказ
            // --------- Проверка, что это первый завершенный заказ пользователя ---------
            $finishedOrdersCount = (int)OrderTable::getCount([
                    '=USER_ID' => $userId,
                    '=PAYED' => 'Y',
                    '=CANCELED' => 'N',
                    '=STATUS_ID' => \FourPaws\PersonalBundle\Service\OrderService::STATUS_FINAL,
                ]);
            if ($finishedOrdersCount === 1) {
                /** @var PersonalOffersService $personalOffersService */
                $personalOffersService = Application::getInstance()->getContainer()->get('personal_offers.service');
                try
                {
                    $personalOffersService->addUniqueOfferCoupon($userId, $personalOffersService::SECOND_ORDER_OFFER_CODE);

                    $user->setGotSecondOrderCoupon(true);
                    Application::getInstance()->getContainer()->get(UserRepository::class)->update($user);
                } catch (Exception $e) {
                    self::getLogger()
                        ->critical(\sprintf(
                            '%s. Ошибка добавления купона на второй заказ: %s',
                            __FUNCTION__,
                            $e->getMessage()
                        ));
                }
            }
        }
    }


    /**
     * @param BitrixEvent $event
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ObjectNotFoundException
     * @throws SystemException
     */
    public static function setNameAfterOrder(BitrixEvent $event): void
    {
        if (self::$isEventsDisable) {
            return;
        }

        $entity = $event->getParameter('ENTITY');

        if ($entity instanceof Order) {
            $order = $entity;

            if ($order->isCanceled()) {
                return;
            }
        } else {
            return;
        }

        $container = Application::getInstance()
            ->getContainer();
        $userService = $container->get(CurrentUserProviderInterface::class);

        if (!$userService->isAuthorized()) {
            return;
        }

        try {
            $currentUser = $userService->getCurrentUser();
        } catch (Exception $e) {
            return;
        }

        if (!$currentUser->getName()) {
            $orderService = $container->get(OrderService::class);
            $userRepository = $container->get(UserRepository::class);

            if ($orderService->isSubscribe($order) || $orderService->isManzanaOrder($order)) {
                // пропускаются заказы, созданные по подписке
                return;
            }

            $name = BxCollection::getOrderPropertyByCode($order->getPropertyCollection(), 'NAME');

            if ($name) {
                $currentUser->setName($name->getValue());
            }

            try {
                $userRepository->update($currentUser);
            } catch (Exception $e) {
                self::getLogger()
                    ->error(\sprintf(
                        'User name update error: %s',
                        $e->getMessage()
                    ));
            }
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws ArgumentException
     * @throws ArgumentTypeException
     * @throws FailedToUpdateException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws \LogicException
     */
    public static function disableForgotBasketReminder(BitrixEvent $event)
    {
        $entity = $event->getParameter('ENTITY');
        $userId = null;
        /** @var CurrentUserProviderInterface $currentUserProvider */
        $currentUserProvider = Application::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
        if ($entity instanceof BasketItem) {
            try {
                $userId = $currentUserProvider->getCurrentUserId();
            } catch (NotAuthorizedException $e) {
            }
        }

        if ($userId) {
            /** @var ForgotBasketService $forgotBasketService */
            $forgotBasketService = Application::getInstance()->getContainer()->get(ForgotBasketService::class);
            $forgotBasketService->disableUserTasks($userId);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws ArgumentException
     * @throws ArgumentTypeException
     * @throws \RuntimeException
     */
    public static function resetBasketCache(BitrixEvent $event)
    {
        $entity = $event->getParameter('ENTITY');
        if ($entity instanceof BasketItem) {
            TaggedCacheHelper::clearManagedCache(['basket:' . $entity->getFUserId()]);
        }
    }

    /**
     * @param $adminMenu
     * @param $moduleMenu
     */
    public static function addRetailRocketOrderReportToAdminMenu(&$adminMenu, &$moduleMenu)
    {
        foreach ($moduleMenu as $i => $menuItem) {
            if ($menuItem['parent_menu'] === 'global_menu_store' &&
                $menuItem['items_id'] === 'menu_sale_stat'
            ) {
                $moduleMenu[$i]['items'][] = [
                    'text' => 'Отчет по заказам для RetailRocket',
                    'title' => 'Отчет по заказам для RetailRocket',
                    'url' => '/bitrix/admin/fourpaws_retail_rocket_orders_report.php?lang=' . LANG,
                    'more_url' => ''
                ];
            }
        }
    }

    /**
     * @todo Не просто добавлять в корзину марку, а перерасчитывать их количество (удалять/добавлять)
     * @todo добавлять марки и при первичном добавлении товара в корзину (до того, как меняем количество). Сейчас марки сразу не добавляются
     * @todo Скрыть марки из вывода в корзине
     * @todo Обработка Exception
     * @todo Марки отправляются в SAP, только если перезагрузить страницу корзины перед тем, как начать оформление
     *
     * @param BitrixEvent $event
     */
//    public static function addStampsToBasket(BitrixEvent $event): void
//    {
//        $piggyBankService = ////////////////;
//        try {
//            /** @var Basket $basket */
//            $basket = $event->getParameter('ENTITY');
//            if ($basket instanceof Basket) {
//                $stampsQuantity = 1; //TODO calculate
//
//                /** @var BasketService $basketService */
//                $basketService = Application::getInstance()->getContainer()->get(BasketService::class);
//                $basketItem = $basketService->addOfferToBasket(
//                    $piggyBankService->getVirtualMarkId(),
//                    //$piggyBankService->getPhysicalMarkId(),
//                    $stampsQuantity,
//                    [],
//                    true,
//                    $basket
//                );
//            }
//        } catch (\Exception $e) {
//            //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/_dev_e123123123.txt', print_r($e->getTrace(), true), FILE_APPEND); //TODO: delete
//            //TODO Обработка Exception
//        }
//    }

    /**
     * Добавляет марки
     * @todo 3 раза отрабатывает в момент регистрации заказа. Разобраться, ограничить применение одним разом
     * @todo вынести в Service
     *
     * @param BitrixEvent $event
     */
    public function addMarksToOrderBasket(BitrixEvent $event): void
    {
        if (self::$isEventsDisable) {
            return;
        }

        try {
            /** @var PiggyBankService $piggyBankService */
            $piggyBankService = Application::getInstance()->getContainer()->get('piggy_bank.service');

            global $USER;
            if ($piggyBankService->isPiggyBankDateExpired())// && !$USER->IsAdmin())
            {
                return;
            }

            /** @var Order $order */
            $order = $event->getParameter('ENTITY');

            /** подсчитываем марки только при создании нового заказе */
            if (!$order->isNew()) {
                return;
            }

            /** @var PiggyBankService $piggyBankService */
            $piggyBankService = Application::getInstance()->getContainer()->get('piggy_bank.service');

            $manzanaNumberValue = '';
            $isIsManzanaOrderReady = false;
            $isManzanaNumberValueReady = false;
            foreach ($order->getPropertyCollection()->getArray()['properties'] as $prop) {
                if ($isIsManzanaOrderReady && $isManzanaNumberValueReady) {
                    break;
                }

                if (!$isIsManzanaOrderReady && $prop['CODE'] === 'IS_MANZANA_ORDER') {
                    $isManzanaOrder = ($prop['VALUE'][0] === BitrixUtils::BX_BOOL_TRUE);
                    $isIsManzanaOrderReady = true;
                }

                if (!$isManzanaNumberValueReady && $prop['CODE'] === 'MANZANA_NUMBER') {
                    $manzanaNumberValue = $prop['VALUE'][0];
                    $isManzanaNumberValueReady = true;
                }
            }

            if ($isManzanaOrder || strpos($manzanaNumberValue, 'NEW') !== false) return; // проверка NEW оставлена для старых заказов, где еще не заполнялось свойство IS_MANZANA_ORDER

            if ($order instanceof Order) {
                /** @var BasketService $basketService */
                $basketService = Application::getInstance()->getContainer()->get(BasketService::class);

                $basket = $order->getBasket();
                $items = $basket->getOrderableItems();
                if ($items->isEmpty())
                {
                    return;
                }
                $offersIds = [];
                foreach ($items as $itemIndex => $item)
                {
                    $offersIds[] = $item->getProductId();
                }
                if ($offersIds)
                {
                    $itemsProps = $piggyBankService->fetchItems($offersIds);
                }

                $sumVetapteka = 0;
                $sumNotVetapteka = 0;
                /** @var BasketItem $item */
                foreach ($items as $itemIndex => $item)
                {
                    $productId = $item->getProductId();

                    if (in_array($productId, $piggyBankService->getMarksIds(), false))
                    {
                        $basketService->deleteOfferFromBasket($item->getId());
                        continue;
                    }

                    $price = $item->getPrice() * $item->getQuantity();
                    if ($itemsProps[$productId]['IS_VETAPTEKA'])
                    {
                        $sumVetapteka += $price;
                    }
                    else
                    {
                        $sumNotVetapteka += $price;
                    }
                }

                $marksToAdd = floor($sumVetapteka / $piggyBankService::MARK_RATE) * $piggyBankService::MARKS_PER_RATE_VETAPTEKA;
                $marksToAdd += floor($sumNotVetapteka / $piggyBankService::MARK_RATE) * $piggyBankService::MARKS_PER_RATE;

                $basket->save();

                if ($marksToAdd > 0)
                {
                    $basketItem = $basketService->addOfferToBasket(
                        $piggyBankService->getVirtualMarkId(),
                        //$piggyBankService->getPhysicalMarkId(),
                        $marksToAdd,
                        [],
                        true,
                        $basket
                    );
                }

                $order->setFieldNoDemand(
                    'PRICE',
                    $order->getBasket()->getOrderableItems()->getPrice() + $order->getDeliveryPrice()
                );

            }
        } catch (Exception $e) {
            $logger = LoggerFactory::create('piggyBank');
            $logger->critical('failed to add PiggyBank marks for order: ' . $e->getMessage());
        }
    }

    public function cancelEventAddition($orderId, string $eventName)
    {
        if (in_array($eventName, ['SALE_NEW_ORDER', 'SALE_ORDER_PAID'])) // проверка избыточна, но на всякий случай сделана, чтобы не заблочили другие события
        {
            return false;
        }
    }
}
