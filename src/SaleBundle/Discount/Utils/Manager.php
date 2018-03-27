<?php
/**
 * Created by PhpStorm.
 * Date: 08.02.2018
 * Time: 18:11
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Discount\Utils;

use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Event;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\BasketPropertyItem;
use Bitrix\Sale\Order;
use Exception;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Exception\ManzanaPromocodeUnavailableException;
use FourPaws\SaleBundle\Discount\Manzana;
use FourPaws\SaleBundle\Exception\InvalidArgumentException;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Service\BasketService;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class Manager
 * @package FourPaws\SaleBundle\Discount\Utils
 */
class Manager
{
    protected static $finalActionEnabled = true;

    /**
     *
     *
     * @param Event|null $event
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws RuntimeException
     * @throws NotFoundException
     * @throws InvalidArgumentException
     * @throws ApplicationCreateException
     * @throws Exception
     * @throws ObjectNotFoundException
     * @throws NotSupportedException
     * @throws ArgumentOutOfRangeException
     * @throws ManzanaPromocodeUnavailableException
     */
    public static function OnAfterSaleOrderFinalAction(Event $event = null): void
    {
        static $execution;
        if (!$execution && self::$finalActionEnabled) {
            $execution = true;
            if ($event instanceof Event) {
                /** @var Order $order */
                $order = $event->getParameter('ENTITY');
                if ($order instanceof Order) {
                    $container = Application::getInstance()->getContainer();
                    $basketService = $container->get(BasketService::class);
                    $manzana = $container->get(Manzana::class);

                    // Автоматически добавляем подарки
                    $basketService
                        ->getAdder('gift')
                        ->processOrder();

                    // Удаляем подарки, акции которых не выполнились
                    $basketService
                        ->getCleaner('gift')
                        ->processOrder();

                    // Автоматически добавляем подарки
                    $basketService
                        ->getAdder('detach')
                        ->processOrder();

                    // Удаляем подарки, акции которых не выполнились
                    $basketService
                        ->getCleaner('detach')
                        ->processOrder();

                    /**
                     * @todo
                     */
                    $promocode = '';
                    if ($promocode) {
                        $manzana->setPromocode($promocode);
                    }
                    $manzana->calculate();
                }
            }
            $execution = false;
        }
    }

    /**
     *
     *
     */
    public static function disableProcessingFinalAction(): void
    {
        self::$finalActionEnabled = false;
    }

    /**
     *
     *
     */
    public static function enableProcessingFinalAction(): void
    {
        self::$finalActionEnabled = true;
    }

    /**
     *
     *
     * @param Order $order
     *
     * @return array
     */
    public static function getExistGifts(Order $order): array
    {
        $result = [];
        if ($basket = $order->getBasket()) {
            /** @var BasketItem $basketItem */
            foreach ($basket->getBasketItems() as $basketItem) {
                /** @var BasketPropertyItem $basketPropertyItem */
                foreach ($basketItem->getPropertyCollection() as $basketPropertyItem) {
                    if ($basketPropertyItem->getField('CODE') === 'IS_GIFT') {
                        $result[$basketItem->getId()]['quantity'] = (int)$basketItem->getQuantity();
                        $result[$basketItem->getId()]['discountId'] = (int)$basketPropertyItem->getField('VALUE');
                        $result[$basketItem->getId()]['offerId'] = (int)$basketItem->getProductId();
                        $result[$basketItem->getId()]['basketId'] = (int)$basketItem->getId();
                    }
                    if ($basketPropertyItem->getField('CODE') === 'IS_GIFT_SELECTED') {
                        $result[$basketItem->getId()]['selected'] = $basketPropertyItem->getField('VALUE');
                    }
                }
            }
        }
        return $result;
    }
}
