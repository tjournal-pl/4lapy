<?php

namespace FourPaws\SaleBundle\Service;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Error;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\PaySystem\Manager as PaySystemManager;
use Bitrix\Sale\UserMessageException;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResultInterface;
use FourPaws\DeliveryBundle\Exception\NotFoundException as DeliveryNotFoundException;
use FourPaws\DeliveryBundle\Exception\TerminalNotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\DeliveryBundle\Service\IntervalService;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\PersonalBundle\Entity\OrderSubscribeItem;
use FourPaws\PersonalBundle\Exception\OrderSubscribeException;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Enum\OrderPayment;
use FourPaws\SaleBundle\Enum\OrderStorage as OrderStorageEnum;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\OrderStorageSaveException;
use FourPaws\SaleBundle\Exception\OrderStorageValidationException;
use FourPaws\SaleBundle\Repository\OrderStorage\DatabaseStorageRepository;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class OrderStorageService
{
    use LazyLoggerAwareTrait;

    public const SESSION_EXPIRED_VIOLATION = 'session-expired';

    /**
     * @var BasketService
     */
    protected $basketService;

    /**
     * @var CurrentUserProviderInterface
     */
    protected $currentUserProvider;

    /**
     * @var PaymentCollection
     */
    protected $paymentCollection;

    /**
     * @var DatabaseStorageRepository
     */
    protected $storageRepository;

    /**
     * @var UserAccountService
     */
    protected $userAccountService;

    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    /**
     * @var CalculationResultInterface[]
     */
    protected $deliveries;

    /**
     * @var StoreService
     */
    protected $storeService;

    /**
     * @var OrderSubscribeService
     */
    protected $orderSubscribeService;

    /**
     * OrderStorageService constructor.
     *
     * @param BasketService                $basketService
     * @param CurrentUserProviderInterface $currentUserProvider
     * @param DatabaseStorageRepository    $storageRepository
     * @param DeliveryService              $deliveryService
     * @param StoreService                 $storeService
     */
    public function __construct(
        BasketService $basketService,
        CurrentUserProviderInterface $currentUserProvider,
        DatabaseStorageRepository $storageRepository,
        DeliveryService $deliveryService,
        StoreService $storeService,
        OrderSubscribeService $orderSubscribeService
    )
    {
        $this->basketService = $basketService;
        $this->currentUserProvider = $currentUserProvider;
        $this->storageRepository = $storageRepository;
        $this->deliveryService = $deliveryService;
        $this->storeService = $storeService;
        $this->orderSubscribeService = $orderSubscribeService;
    }

    /**
     * Вычисляет шаг оформления заказа в соответствии с состоянием хранилища
     *
     * @param OrderStorage $storage
     * @param string       $startStep
     *
     * @return string
     */
    public function validateStorage(OrderStorage $storage, string $startStep = OrderStorageEnum::AUTH_STEP): string
    {
        return $this->storageRepository->validateAllStepsBefore($storage, $startStep)
            ->getRealStep();
    }

    /**
     * @param int|null $fuserId
     *
     * @throws OrderStorageSaveException
     * @return bool|OrderStorage
     */
    public function getStorage(int $fuserId = null)
    {
        if (!$fuserId) {
            $fuserId = $this->currentUserProvider->getCurrentFUserId();
        }

        try {
            return $this->storageRepository->findByFuser($fuserId);
        } catch (NotFoundException $e) {
            return false;
        }
    }

    /**
     * @param OrderStorage $storage
     * @param Request $request
     * @param string $step
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws OrderSubscribeException
     * @return OrderStorage
     */
    public function setStorageValuesFromRequest(OrderStorage $storage, Request $request, string $step): OrderStorage
    {
        $data = $request->request->all();

        $mapping = [
            'order-pick-time' => 'split',
            'shopId'          => 'deliveryPlaceCode',
            'pay-type'        => 'paymentId',
            'cardNumber'      => 'discountCardNumber',
        ];

        foreach ($data as $name => $value) {
            if (isset($mapping[$name])) {
                $data[$mapping[$name]] = $value;
                unset($data[$name]);
            }
        }

        $deliveryId = (int)($data['deliveryTypeId']) ? ($data['deliveryTypeId']) : $data['deliveryId'];

        if (isset($data['deliveryCoords'])) {
            $coords = explode(',', $data['deliveryCoords']);
            $data['lng'] = $coords[0];
            $data['lat'] = $coords[1];
        }

        /**
         * Чтобы нельзя было, например, обойти проверку капчи,
         * отправив в POST данные со всех форм разом
         */
        $availableValues = [];
        switch ($step) {
            case OrderStorageEnum::AUTH_STEP:
                $availableValues = [
                    'name',
                    'altPhone',
                    'communicationWay',
                    'captchaFilled',
                ];

                if ($storage->getUserId()) {
                    try {
                        $user = $this->currentUserProvider->getCurrentUser();

                        if ($user && !$user->getEmail() && $storage->getUserId() === $user->getId()
                        ) {
                            $availableValues[] = 'email';
                        }
                    } catch (NotAuthorizedException | UsernameNotFoundException $e) {}
                } else {
                    $availableValues[] = 'phone';
                    $availableValues[] = 'email';
                }

                break;
            case OrderStorageEnum::DELIVERY_STEP:
                try {
                    $deliveryCode = $this->deliveryService->getDeliveryCodeById($deliveryId);
                    if (\in_array($deliveryCode, array_merge(DeliveryService::DELIVERY_CODES, [DeliveryService::DELIVERY_DOSTAVISTA_CODE]), true)) {
                        switch ($data['delyveryType']) {
                            case 'twoDeliveries':
                                $data['deliveryInterval'] = $data['deliveryInterval1'];
                                $data['secondDeliveryInterval'] = $data['deliveryInterval2'];
                                $data['deliveryDate'] = $data['deliveryDate1'];
                                $data['secondDeliveryDate'] = $data['deliveryDate2'];
                                if ($deliveryCode == DeliveryService::DELIVERY_DOSTAVISTA_CODE) {
                                    $data['comment'] = $data['comment_dostavista'];
                                } else {
                                    $data['comment'] = $data['comment1'];
                                    $data['secondComment'] = $data['comment2'];
                                }
                                $data['split'] = 1;
                                break;
                            default:
                                $data['split'] = 0;
                        }
                    } elseif ((int)$data['split'] === 1) {
                        $tmpStorage = clone $storage;
                        $tmpStorage->setDeliveryId($deliveryId);
                        $pickup = clone $this->getSelectedDelivery($tmpStorage);
                        if ($pickup instanceof PickupResultInterface) {
                            $availableStores = $pickup->getBestShops();
                            $storeXmlId = $data['deliveryPlaceCode'];
                            $selectedStore = null;
                            /** @var Store $store */
                            foreach ($availableStores as $store) {
                                if ($store->getXmlId() === $storeXmlId) {
                                    $selectedStore = $store;
                                    $pickup->setSelectedShop($selectedStore);
                                    break;
                                }
                            }
                        }
                        $orderSplitService = Application::getInstance()
                            ->getContainer()
                            ->get(OrderSplitService::class);
                        if (!$orderSplitService->canSplitOrder($pickup)
                            && !$orderSplitService->canGetPartial($pickup)) {
                            $data['split'] = 0;
                        }
                    }
                } catch (DeliveryNotFoundException $e) {
                }

                // создание подписки на доставку
                if($storage->isSubscribe()){
                    $result = $this->createSubscription($storage, $data);
                    if(!$result->isSuccess()){
                        $this->log()->error(implode(";\r\n", $result->getErrorMessages()));
                        throw new OrderSubscribeException("Произошла ошибка оформления подписки на доставку, пожалуйста, обратитесь к администратору");
                    }
                }

                $availableValues = [
                    'deliveryId',
                    'addressId',
                    'street',
                    'house',
                    'building',
                    'porch',
                    'floor',
                    'apartment',
                    'deliveryDate',
                    'deliveryInterval',
                    'deliveryPlaceCode',
                    'comment',
                    'partialGet',
                    'shopId',
                    'split',
                    'secondDeliveryDate',
                    'secondDeliveryInterval',
                    'secondComment',
                    'lng',
                    'lat'
                ];
                break;
            case OrderStorageEnum::PAYMENT_STEP:

                // установка свойства "Списывать все баллы по подписке"
                if($storage->isSubscribe() && $data['subscribeBonus']){
                    $subscribe = $this->orderSubscribeService->getById($storage->getSubscribeId());
                    if(!$subscribe){
                        $this->log()->error(sprintf("Failed to get subscribe: %s", $storage->getSubscribeId()));
                        throw new OrderSubscribeException("Произошла ошибка оформления подписки на доставку, пожалуйста, обратитесь к администратору");
                    }
                    $subscribe->setPayWithbonus(true);
                    $this->orderSubscribeService->update($subscribe);
                }

                $availableValues = [
                    'paymentId',
                    'bonus',
                ];
                break;
            case OrderStorageEnum::PAYMENT_STEP_CARD:
                $availableValues = [
                    'discountCardNumber',
                ];
                break;
        }

        foreach ($data as $name => $value) {
            if (null === $value) {
                continue;
            }

            if (!\in_array($name, $availableValues, true)) {
                continue;
            }

            if (($name === 'bonus') && (!is_numeric($value))) {
                continue;
            }

            $setter = 'set' . ucfirst($name);
            if (method_exists($storage, $setter)) {
                switch ($name) {
                    case 'deliveryId':
                        $storage->$setter($data['deliveryTypeId']);
                        break;
                    case 'comment':
                        if($step == OrderStorageEnum::DELIVERY_STEP && $deliveryCode == DeliveryService::DELIVERY_DOSTAVISTA_CODE){
                            $storage->$setter($data['comment_dostavista']);
                        } elseif(isset($data['comment'])) {
                            $storage->$setter($data['comment']);
                        }
                        break;
                    default:
                        $storage->$setter($value);
                }
            }
        }

        return $storage;
    }

    /**
     * @param OrderStorage $storage
     * @param string       $step
     *
     * @throws OrderStorageValidationException
     * @return bool
     */
    public function updateStorage(OrderStorage $storage, string $step = OrderStorageEnum::AUTH_STEP): bool
    {
        try {
            return $this->storageRepository->save($storage, $step);
        } catch (NotFoundException $e) {
            return false;
        }
    }

    /**
     * @param $storage
     *
     * @return bool
     */
    public function clearStorage($storage): bool
    {
        try {
            return $this->storageRepository->clear($storage);
        } catch (NotFoundException $e) {
            return false;
        }
    }

    /**
     * @param OrderStorage $storage
     *
     * @throws NotFoundException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws ObjectNotFoundException
     * @return PaymentCollection
     */
    public function getPayments(OrderStorage $storage): PaymentCollection
    {
        if (!$deliveryId = $storage->getDeliveryId()) {
            throw new NotFoundException('No payments available');
        }

        if (!$this->paymentCollection) {
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $order = Order::create(
                SITE_ID,
                null,
                CurrencyManager::getBaseCurrency()
            );
            $this->paymentCollection = $order->getPaymentCollection();
            $sum = $this->basketService->getBasket()
                ->getOrderableItems()
                ->getPrice();

            if ($storage->getBonus()) {
                if (!$innerPayment = $this->paymentCollection->getInnerPayment()) {
                    $innerPayment = $this->paymentCollection->createInnerPayment();
                }
                $innerPayment->setField('SUM', $storage->getBonus());
                $sum -= $storage->getBonus();
            }

            $extPayment = $this->paymentCollection->createItem();
            $extPayment->setField('SUM', $sum);
        }

        return $this->paymentCollection;
    }

    /**
     * @param OrderStorage $storage
     * @param bool         $withInner
     * @param bool         $filter
     * @param float        $basketPrice
     *
     * @return array
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws DeliveryNotFoundException
     * @throws NotImplementedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getAvailablePayments(OrderStorage $storage, $withInner = false, $filter = true, float $basketPrice = 0): array
    {
        $paymentCollection = $this->getPayments($storage);

        $payments = [];
        /** @var Payment $payment */
        foreach ($paymentCollection as $payment) {
            if ($payment->isInner()) {
                continue;
            }

            $payments = PaySystemManager::getListWithRestrictions($payment);
        }

        /**
         * Для заказа по подписке доступна только оплата при получении
         */
        if($storage->isSubscribe()){
            $payments = array_filter($payments, function($item){
                return in_array($item['CODE'], [OrderPayment::PAYMENT_CASH, OrderPayment::PAYMENT_CASH_OR_CARD]);
            });
        }

        /**
         * Если выбран самовывоз DPD и терминал, то оставляем только доступные в этом терминале способы оплаты
         */
        if ($storage->getDeliveryPlaceCode() && $storage->getDeliveryId()) {
            $deliveryCode = $this->deliveryService->getDeliveryCodeById($storage->getDeliveryId());
            if ($this->deliveryService->isDpdPickupCode($deliveryCode)
                && $terminal = $this->deliveryService->getDpdTerminalByCode($storage->getDeliveryPlaceCode())
            ) {
                foreach ($payments as $id => $payment) {
                    $delete = $basketPrice > $terminal->getNppValue();
                    switch ($payment['CODE']) {
                        case OrderPayment::PAYMENT_CASH_OR_CARD:
                            $delete |= !$terminal->hasCardPayment();
                            break;
                        case OrderPayment::PAYMENT_CASH:
                            $delete |= !$terminal->hasCashPayment();
                            break;
                        default:
                            $delete = false;
                    }

                    if ($delete) {
                        unset($payments[$id]);
                    }
                }
            }
        }

        if (!$withInner) {
            $innerPaySystemId = (int)PaySystemManager::getInnerPaySystemId();
            /** @var Payment $payment */
            foreach ($payments as $id => $payment) {
                if ($innerPaySystemId === $id) {
                    unset($payments[$id]);
                    break;
                }
            }
        }

        /**
         * Если есть оплата "наличными или картой", удаляем оплату "наличными" - если не достависта,
         * Если достависта - "удаляем наличными или картой", оставляем "наличными"
         */
        $deliveryCode = false;
        if ($storage->getDeliveryId()) {
            $deliveryCode = $this->deliveryService->getDeliveryCodeById($storage->getDeliveryId());
        }
        if ($deliveryCode === false || !$this->deliveryService->isDostavistaDeliveryCode($deliveryCode)) {
            if ($filter
                && !empty(\array_filter($payments, function ($item) {
                    return $item['CODE'] === OrderPayment::PAYMENT_CASH_OR_CARD;
                }))) {
                foreach ($payments as $id => $payment) {
                    if ($payment['CODE'] === OrderPayment::PAYMENT_CASH) {
                        unset($payments[$id]);
                        break;
                    }
                }
            }
        } else {
            if ($filter
                && !empty(\array_filter($payments, function ($item) {
                    return $item['CODE'] === OrderPayment::PAYMENT_CASH;
                }))) {
                foreach ($payments as $id => $payment) {
                    if ($payment['CODE'] === OrderPayment::PAYMENT_CASH_OR_CARD) {
                        unset($payments[$id]);
                        break;
                    }
                }
            }
        }

        return $payments;
    }

    /**
     * @param OrderStorage $storage
     * @param bool         $reload
     *
     * @throws ArgumentException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws UserMessageException
     * @throws ApplicationCreateException
     * @throws DeliveryNotFoundException
     * @throws StoreNotFoundException
     * @return CalculationResultInterface[]
     */
    public function getDeliveries(OrderStorage $storage, $reload = false): array
    {
        if (null === $this->deliveries || $reload) {
            $basket = $this->basketService->getBasket();
            if (null === $basket->getOrder()) {
                $order = Order::create(SITE_ID, $storage->getUserId() ?: null);
                $order->setBasket($basket);
            }

            $this->deliveries = $this->deliveryService->getByBasket(
                $basket,
                $storage->getCityCode(),
                [],
                $storage->getCurrentDate()
            );
        }

        return $this->deliveries;
    }

    /**
     * @param OrderStorage $storage
     *
     * @return PickupResultInterface|null
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws DeliveryNotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws StoreNotFoundException
     * @throws UserMessageException
     */
    public function getPickupDelivery(OrderStorage $storage): ?PickupResultInterface
    {
        $result = null;
        foreach ($this->getDeliveries($storage) as $delivery) {
            if ($delivery instanceof PickupResultInterface) {
                $result = $delivery;
                break;
            }
        }

        return $result;
    }

    /**
     * @param OrderStorage $storage
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws DeliveryNotFoundException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws StoreNotFoundException
     * @throws UserMessageException
     * @return CalculationResultInterface
     */
    public function getSelectedDelivery(OrderStorage $storage): CalculationResultInterface
    {
        $deliveries = $this->getDeliveries($storage);
        $selectedDelivery = current($deliveries);
        if ($deliveryId = $storage->getDeliveryId()) {
            /** @var CalculationResultInterface $delivery */
            foreach ($deliveries as $delivery) {
                if ($storage->getDeliveryId() === $delivery->getDeliveryId()) {
                    $selectedDelivery = $delivery;
                    break;
                }
            }
        }

        if (!$selectedDelivery) {
            throw new NotFoundException('No deliveries available');
        }

        if ($selectedDelivery instanceof PickupResultInterface && $storage->getDeliveryPlaceCode()) {
            $selectedDelivery->setSelectedShop($this->getSelectedShop($storage, $selectedDelivery));
            if (!$selectedDelivery->isSuccess()) {
                $selectedDelivery->setSelectedShop($selectedDelivery->getBestShops()
                    ->first());
            }
        }

        return $selectedDelivery;
    }

    /**
     * @param OrderStorage          $storage
     * @param PickupResultInterface $delivery
     *
     * @throws ArgumentException
     * @return Store
     */
    public function getSelectedShop(OrderStorage $storage, PickupResultInterface $delivery): Store
    {
        $result = null;
        if ($storage->getDeliveryPlaceCode()) {
            try {
                if ($this->deliveryService->isDpdPickup($delivery)) {
                    $stores = $this->deliveryService->getDpdTerminalsByLocation($storage->getCityCode());
                    $selectedStore = $this->deliveryService->getDpdTerminalByCode($storage->getDeliveryPlaceCode());
                } else {
                    $stores = $this->storeService->getShopsByLocation($storage->getCityCode());
                    $selectedStore = $this->storeService->getStoreByXmlId($storage->getDeliveryPlaceCode());
                }

                if ($selectedStore && $stores->hasStore($selectedStore)) {
                    $result = $selectedStore;
                }
            } catch (StoreNotFoundException | TerminalNotFoundException $e) {
                // обработка не требуется. срабатывает при смене зоны доставки / деактивации склада
            }
        }

        return $result ?? $delivery->getBestShops()
                ->first();
    }

    /**
     * @param OrderStorage $storage
     *
     * @return array
     */
    public function storageToArray(OrderStorage $storage): array
    {
        return $this->storageRepository->toArray($storage);
    }

    /**
     * @param OrderStorage $storage
     * @param $data
     * @return Result
     */
    private function createSubscription(OrderStorage $storage, $data): Result
    {
        $result = new Result();

        try {
            /** @var IntervalService $intervalService */
            $intervalService = Application::getInstance()->getContainer()->get(IntervalService::class);

            $deliveryId = (int)($data['deliveryTypeId']) ? ($data['deliveryTypeId']) : $data['deliveryId'];

            if(!$storage->getSubscribeId()) {
                $subscribe = (new OrderSubscribe());
            }
            else{
                $subscribe = $this->orderSubscribeService->getById($storage->getSubscribeId());
            }

            $interval = $data['deliveryInterval'] ? $intervalService->getIntervalByCode($data['deliveryInterval']) : '';

            $subscribe->setDeliveryDay($data['subscribeDay'])
                ->setDeliveryId($deliveryId)
                ->setFrequency($data['subscribeFrequency'])
                ->setDeliveryTime($interval)
                ->setActive(false);

            if($this->deliveryService->isDelivery($this->getSelectedDelivery($storage))){
                $subscribe->setDeliveryPlace($data['addressId']);
            }
            elseif($this->deliveryService->isPickup($this->getSelectedDelivery($storage))){
                $subscribe->setDeliveryPlace($data['deliveryPlaceCode']);
            }

            if(!$subscribe->getDeliveryPlace()){
                throw new OrderSubscribeException("Не указано место доставки");
            }

            if($subscribe->getId() > 0){
                $this->orderSubscribeService->countNextDate($subscribe);
                $this->orderSubscribeService->update($subscribe);
            }
            else{
                $result = $this->orderSubscribeService->add($subscribe);

                if(!$result->isSuccess()){
                    throw new OrderSubscribeException(sprintf('Failed to create order subscribe: %s', print_r($result->getErrorMessages(),true)));
                }

                $items = $this->basketService->getBasket()->getOrderableItems();
                /** @var BasketItem $basketItem */
                foreach($items as $basketItem){
                    $subscribeItem = (new OrderSubscribeItem())
                        ->setOfferId($basketItem->getProductId())
                        ->setQuantity($basketItem->getQuantity());

                    if(!$this->orderSubscribeService->addSubscribeItem($subscribe, $subscribeItem)){
                        throw new OrderSubscribeException(sprintf('Failed to create order subscribe item: %s', print_r($data,true)));
                    }
                }

                $storage->setSubscribeId($subscribe->getId());
            }
        } catch (\Exception $e) {
            $result->addError(new Error($e->getMessage(), 'createSubscription'));
        }

        return $result;
    }
}
