<?php

namespace FourPaws\SaleBundle\EventController;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Event as BitrixEvent;
use Bitrix\Main\EventManager;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Exception;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\ServiceHandlerInterface;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\SaleBundle\Discount\Action\Action\DetachedRowDiscount;
use FourPaws\SaleBundle\Discount\Action\Action\DiscountFromProperty;
use FourPaws\SaleBundle\Discount\Action\Condition\BasketFilter;
use FourPaws\SaleBundle\Discount\Action\Condition\BasketQuantity;
use FourPaws\SaleBundle\Discount\Gift;
use FourPaws\SaleBundle\Discount\Gifter;
use FourPaws\SaleBundle\Discount\Utils\Manager;
use FourPaws\SaleBundle\Exception\InvalidArgumentException;
use FourPaws\SaleBundle\Exception\ValidationException;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\NotificationService;
use FourPaws\SaleBundle\Service\UserAccountService;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class Event
 *
 * Обработчики событий
 *
 * @package FourPaws\SaleBundle\EventController
 */
class Event implements ServiceHandlerInterface
{
    /**
     * @var EventManager
     */
    protected static $eventManager;

    /**
     * @param EventManager $eventManager
     *
     * @return mixed|void
     */
    public static function initHandlers(EventManager $eventManager): void
    {
        self::$eventManager = $eventManager;

        ###   Обработчики скидок       ###

        /** Инициализация кастомных правил работы с корзиной */
        self::initHandler('OnCondSaleActionsControlBuildList', [Gift::class, 'GetControlDescr']);
        self::initHandler('OnCondSaleActionsControlBuildList', [Gifter::class, 'GetControlDescr']);
        self::initHandler('OnCondSaleActionsControlBuildList', [BasketFilter::class, 'GetControlDescr']);
        self::initHandler('OnCondSaleActionsControlBuildList', [BasketQuantity::class, 'GetControlDescr']);
        self::initHandler('OnCondSaleActionsControlBuildList', [DiscountFromProperty::class, 'GetControlDescr']);
        self::initHandler('OnCondSaleActionsControlBuildList', [DetachedRowDiscount::class, 'GetControlDescr']);
        /** Здесь дополнительная обработка акций */
        self::initHandler('OnAfterSaleOrderFinalAction', [Manager::class, 'OnAfterSaleOrderFinalAction']);

        ###   Обработчики скидок EOF   ###

        /** отправка email */
        self::initHandler('OnSaleOrderSaved', [static::class, 'sendNewOrderMessage']);
        self::initHandler('OnSaleOrderPaid', [static::class, 'sendOrderPaymentMessage']);
        self::initHandler('OnSaleOrderCanceled', [static::class, 'sendOrderCancelMessage']);
        self::initHandler('OnSaleStatusOrderChange', [static::class, 'sendOrderStatusMessage']);

        /** обновление бонусного счета пользователя и бонусного процента пользователя */
        self::initHandler('OnAfterUserLogin', [static::class, 'updateUserAccountBalance'], 'main');
        self::initHandler('OnAfterUserAuthorize', [static::class, 'updateUserAccountBalance'], 'main');
        self::initHandler('OnAfterUserLoginByHash', [static::class, 'updateUserAccountBalance'], 'main');

        /** очистка кеша заказа */
        self::initHandler('OnSaleOrderSaved', [static::class, 'clearOrderCache']);
    }

    /**
     * @param string $eventName
     * @param callable $callback
     * @param string $module
     *
     */
    public static function initHandler(string $eventName, callable $callback, string $module = 'sale'): void
    {
        self::$eventManager->addEventHandler(
            $module,
            $eventName,
            $callback
        );
    }

    public static function updateUserAccountBalance(): void
    {
        try {
            $container = Application::getInstance()->getContainer();
            $userService = $container->get(CurrentUserProviderInterface::class);
            $userAccountService = $container->get(UserAccountService::class);
            $user = $userService->getCurrentUser();
            list(, $bonus) = $userAccountService->refreshUserBalance($user);
            $userService->refreshUserBonusPercent($user, $bonus);
        } catch (NotAuthorizedException $e) {
            // обработка не требуется
        } catch (\Exception $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('failed to update user account balance: ' . $e->getMessage());
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws InvalidArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws Exception
     * @throws ArgumentException
     */
    public static function updateItemAvailability(BitrixEvent $event): void
    {
        $basketItem = $event->getParameter('ENTITY');
        Application::getInstance()
                   ->getContainer()
                   ->get(BasketService::class)
                   ->refreshItemAvailability($basketItem);
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     */
    public static function sendNewOrderMessage(BitrixEvent $event): void
    {
        /** @var Order $order */
        $order = $event->getParameter('ENTITY');
        $isNew = $event->getParameter('IS_NEW');
        if (!$isNew) {
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
     */
    public static function sendOrderPaymentMessage(BitrixEvent $event): void
    {
        /** @var Payment $payment */
        $order = $event->getParameter('ENTITY');

        /** @var NotificationService $notificationService */
        $notificationService = Application::getInstance()
                                          ->getContainer()
                                          ->get(NotificationService::class);

        $notificationService->sendOrderPaymentMessage($order);
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
        /** @var Order $order */
        $order = $event->getParameter('ENTITY');

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
     * @throws ArgumentOutOfRangeException
     * @throws ApplicationCreateException
     */
    public static function sendOrderStatusMessage(BitrixEvent $event): void
    {
        /** @var Order $order */
        $order = $event->getParameter('ENTITY');

        /** @var NotificationService $notificationService */
        $notificationService = Application::getInstance()
                                          ->getContainer()
                                          ->get(NotificationService::class);

        $notificationService->sendOrderStatusMessage($order);
    }

    /**
     * @param BitrixEvent $event
     */
    public function clearOrderCache(BitrixEvent $event): void
    {
        /** @var Order $order */
        $order = $event->getParameter('ENTITY');

        TaggedCacheHelper::clearManagedCache([
            'order:' . $order->getField('USER_ID'),
            'personal:order:' . $order->getField('USER_ID')
        ]);
    }
}
