<?php

namespace FourPaws\SapBundle\Consumer;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\SapBundle\Dto\In\Orders\Order;
use FourPaws\SapBundle\Exception\CantUpdateOrderException;
use FourPaws\SapBundle\Service\Orders\OrderService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;

/**
 * Class OrderStatusConsumer
 *
 * @package FourPaws\SapBundle\Consumer
 */
class OrderStatusConsumer implements ConsumerInterface, LoggerAwareInterface
{
    use LazyLoggerAwareTrait;
    
    /**
     * @var OrderService
     */
    private $orderService;
    
    /**
     * OrderStatusConsumer constructor.
     *
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
    
    /**
     * Consume order info (save sap order`s change)
     *
     * @param $paymentInfo
     *
     * @return bool
     */
    public function consume($paymentInfo): bool
    {
        if (!$this->support($paymentInfo)) {
            return false;
        }
        
        $this->log()->log(LogLevel::INFO, 'Импортируется статус заказа');
        
        try {
            $success = true;
            
            $order = $this->orderService->transformDtoToOrder($paymentInfo);
            $result = $order->save();
            
            if (!$result->isSuccess()) {
                throw new CantUpdateOrderException(sprintf(
                    'Не удалось обновить заказ #%s: %s',
                    $order->getId(),
                    implode(', ', $result->getErrorMessages())
                ));
            }
        } catch (\Exception $e) {
            $success = false;
            
            $this->log()->log(LogLevel::ERROR, sprintf('Ошибка импорта статуса заказа: %s', $e->getMessage()));
        }
        
        return $success;
    }
    
    /**
     * @param $data
     *
     * @return bool
     */
    public function support($data): bool
    {
        return \is_object($data) && $data instanceof Order;
    }
}
