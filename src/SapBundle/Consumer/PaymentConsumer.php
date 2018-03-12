<?php

namespace FourPaws\SapBundle\Consumer;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\SapBundle\Dto\In\ConfirmPayment\Order;
use FourPaws\SapBundle\Service\Orders\PaymentService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;

/**
 * Class PaymentConsumer
 *
 * @package FourPaws\SapBundle\Consumer
 */
class PaymentConsumer implements ConsumerInterface, LoggerAwareInterface
{
    use LazyLoggerAwareTrait;
    
    /**
     * @var PaymentService
     */
    private $paymentService;
    
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }
    
    /**
     * Consume order
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
        
        $this->log()->log(LogLevel::INFO, 'Обработка задания на оплату');
        
        try {
            $success = true;
            
            $this->paymentService->paymentTaskPerform($paymentInfo);
        } catch (\Exception $e) {
            $success = false;
            
            $this->log()->log(LogLevel::CRITICAL, sprintf('Ошибка обработки задания на оплату: %s', $e->getMessage()));
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
