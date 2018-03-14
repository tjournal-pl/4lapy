<?php

namespace FourPaws\SaleBundle\EventController;

use Bitrix\Main\Event as BitrixEvent;
use Bitrix\Main\EventManager;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use FourPaws\App\Application;
use FourPaws\App\ServiceHandlerInterface;
use FourPaws\SaleBundle\Discount\Action\Action\DetachedRowDiscount;
use FourPaws\SaleBundle\Discount\Action\Action\DiscountFromProperty;
use FourPaws\SaleBundle\Discount\Action\Condition\BasketQuantity;
use FourPaws\SaleBundle\Discount\Action\Condition\BasketFilter;
use FourPaws\SaleBundle\Discount\Utils\Manager;
use FourPaws\SaleBundle\Discount\Gift;
use FourPaws\SaleBundle\Discount\Gifter;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\NotificationService;
use FourPaws\SaleBundle\Service\UserAccountService;

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
     * @param \Bitrix\Main\EventManager $eventManager
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


        self::initHandler('OnSaleBasketItemRefreshData', [static::class, 'updateItemAvailability']);

        self::initHandler('OnSaleOrderSaved', [static::class, 'sendNewOrderMessage']);
        self::initHandler('OnSaleOrderPaid', [static::class, 'sendOrderPaymentMessage']);
        self::initHandler('OnSaleOrderCanceled', [static::class, 'sendOrderCancelMessage']);
        self::initHandler('OnSaleStatusOrderChange', [static::class, 'sendOrderStatusMessage']);

        self::initHandler('OnAfterUserLogin', [static::class, 'updateUserAccountBalance'], 'main');
        self::initHandler('OnAfterUserAuthorize', [static::class, 'updateUserAccountBalance'], 'main');
        self::initHandler('OnAfterUserLoginByHash', [static::class, 'updateUserAccountBalance'], 'main');
    }

    /**
     *
     *
     * @param string $eventName
     * @param callable $callback
     * @param string $module
     *
     */
    public static function initHandler(string $eventName, callable $callback, string $module = 'sale')
    {
        self::$eventManager->addEventHandler(
            $module,
            $eventName,
            $callback
        );
    }

    /**
     *
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\SaleBundle\Exception\ValidationException
     */
    public static function updateUserAccountBalance()
    {
        /* @todo по ТЗ должно выполняться в фоновом режиме */
        Application::getInstance()->getContainer()->get(UserAccountService::class)->refreshUserBalance();
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \FourPaws\SaleBundle\Exception\InvalidArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Exception
     */
    public static function updateItemAvailability(BitrixEvent $event)
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
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public static function sendNewOrderMessage(BitrixEvent $event)
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
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public static function sendOrderPaymentMessage(BitrixEvent $event)
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
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public static function sendOrderCancelMessage(BitrixEvent $event)
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
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public static function sendOrderStatusMessage(BitrixEvent $event)
    {
        /** @var Order $order */
        $order = $event->getParameter('ENTITY');

        /** @var NotificationService $notificationService */
        $notificationService = Application::getInstance()
                                          ->getContainer()
                                          ->get(NotificationService::class);

        $notificationService->sendOrderStatusMessage($order);
    }
}
