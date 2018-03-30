<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Service\Orders;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\NotImplementedException;
use Bitrix\Sale\BusinessValue;
use Bitrix\Sale\Internals\PaySystemActionTable;
use Bitrix\Sale\Order as SaleOrder;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\PaymentException as SalePaymentException;
use FourPaws\SaleBundle\Payment\Sberbank;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\PaymentService as SalePaymentService;
use FourPaws\SapBundle\Dto\In\ConfirmPayment\Debit;
use FourPaws\SapBundle\Dto\In\ConfirmPayment\Item;
use FourPaws\SapBundle\Dto\In\ConfirmPayment\Order;
use FourPaws\SapBundle\Exception\NotFoundOrderUserException;
use FourPaws\SapBundle\Exception\PaymentException;
use FourPaws\SapBundle\Service\SapOutFile;
use FourPaws\SapBundle\Service\SapOutInterface;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Service\UserService;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class PaymentService
 *
 * @package FourPaws\SapBundle\Service\Orders
 */
class PaymentService implements LoggerAwareInterface, SapOutInterface
{
    use LazyLoggerAwareTrait, SapOutFile;

    private const MODULE_PROVIDER_CODE = 'sberbank.ecom';
    private const OPTION_FISCALIZATION_CODE = 'FISCALIZATION';

    /**
     * @var OrderService
     */
    private $orderService;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var string
     */
    private $outPath;
    /**
     * @var string
     */
    private $outPrefix;
    /**
     * @var SalePaymentService
     */
    private $salePaymentService;
    /**
     * @var UserService
     */
    private $userService;
    /**
     * @var Sberbank
     */
    private $sberbankProcessing;

    /**
     * PaymentService constructor.
     *
     * @param OrderService $orderService
     * @param SerializerInterface $serializer
     * @param Filesystem $filesystem
     * @param SalePaymentService $salePaymentService
     * @param UserService $userService
     */
    public function __construct(
        OrderService $orderService,
        SerializerInterface $serializer,
        Filesystem $filesystem,
        SalePaymentService $salePaymentService,
        UserService $userService
    )
    {
        $this->orderService = $orderService;
        $this->serializer = $serializer;
        $this->salePaymentService = $salePaymentService;
        $this->userService = $userService;
        $this->setFilesystem($filesystem);

        $this->initPayment();
    }

    /**
     * @param Order $paymentTask
     *
     * @throws ArgumentNullException
     * @throws NotImplementedException
     * @throws NotFoundException
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     * @throws NotFoundOrderUserException
     * @throws ArgumentOutOfRangeException
     * @throws PaymentException
     * @throws ArgumentException
     * @throws \FourPaws\SaleBundle\Exception\PaymentException
     */
    public function paymentTaskPerform(Order $paymentTask)
    {
        /**
         * Check order existence
         */
        $order = $this->orderService->getOrderById($paymentTask->getBitrixOrderId());
        $user = $this->userService->getUserRepository()->find($order->getUserId());

        if (null === $user) {
            throw new NotFoundOrderUserException(
                \sprintf(
                    'User with id %s is not found',
                    $order->getUserId()
                )
            );
        }

        if (!$paymentTask->getSumPayed() && !$paymentTask->getSumTotal()) {
            throw new PaymentException('Сумма на списание и сумма заказа равны нулю');
        }

        $fiscalization = $this->getFiscalization($order, $user, $paymentTask);
        $amount = $paymentTask->getSumPayed();
        $orderId = $order->getId();

        $this->response(function () use ($orderId, $amount, $fiscalization) {
            return $this->sberbankProcessing->depositPayment($orderId, $amount, $fiscalization);
        });
    }

    /**
     * @param Debit $debit
     *
     * @throws IOException
     */
    public function out(Debit $debit)
    {
        $xml = $this->serializer->serialize($debit, 'xml');

        $this->filesystem->dumpFile($this->getFileName($debit), $xml);
    }

    /**
     * @param Order $order
     */
    public function tryPaymentRefund(Order $order)
    {
        /**
         * @todo refund
         */
    }

    /**
     * @param Debit $debit
     *
     * @return string
     */
    public function getFileName($debit): string
    {
        return \sprintf(
            '/%s/%s-%s_%s',
            \trim($this->outPath, '/'),
            $debit->getPaymentDate()->format('Ymd'),
            $this->outPrefix,
            $debit->getBitrixOrderId()
        );
    }

    /**
     * Init payment
     *
     * @return void
     */
    public function initPayment(): void
    {
        /** @noinspection PhpIncludeInspection */
        require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sberbank.ecom/config.php';
        dump(BusinessValue::get('PAYSYSTEM_3'));
        dump(PaySystemActionTable::getById(3)->fetch());


        /** @noinspection PhpDeprecationInspection */
        $paySystemAction = new \CSalePaySystemAction();



        $this->sberbankProcessing = new Sberbank(
            $paySystemAction->GetParamValue('USER_NAME'),
            $paySystemAction->GetParamValue('PASSWORD'),
            $paySystemAction->GetParamValue('TEST_MODE') === 'Y',
            true,
            true
        );
        dump($this->sberbankProcessing);
    }

    /**
     * @param SaleOrder $order
     * @param User $user
     * @param Order $paymentTask
     *
     * @return array|null
     *
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    private function getFiscalization(SaleOrder $order, User $user, Order $paymentTask): ?array {
        $config = Option::get(self::MODULE_PROVIDER_CODE, self::OPTION_FISCALIZATION_CODE, []);
        $config = \unserialize($config, []);

        if ($config['ENABLE'] !== 'Y') {
            return null;
        }

        $fiscalization = $this->salePaymentService->getFiscalization($order, ['name' => $user->getFullName(), 'email' => $user->getEmail()], (int)$config['TAX_SYSTEM']);
        $map = $fiscalization['itemMap'];
        $itemsAfter = [];

        /** @noinspection ForeachSourceInspection */
        foreach ($fiscalization['fiscal']['orderBundle']['cartItems']['items'] as $item) {
            $itemsAfter[] = $paymentTask->getItems()->map(function (Item $v) use ($map, $item) {
                if (
                    /* Доставка */
                    ($v->getOfferXmlId() >= 2000000 && $item['name'] === null)
                    /* или товар */
                    || $map[$v->getOfferXmlId()] === $item['itemCode']
                ) {
                    $newItem = [];
                    $newItem['quantity']['value'] = $v->getQuantity();
                    $newItem['itemPrice']['value'] = $v->getPrice() * 100;
                    $newItem['itemAmount'] = $v->getSumPrice() * 100;

                    return \array_merge($item, $newItem);
                }

                return null;
            })->filter(function ($v) {
                return null !== $v;
            })->toArray();
        }

        $fiscalization['fiscal']['orderBundle']['cartItems']['items'] = \array_reduce($itemsAfter, function ($to, $from) {
            $to = $to ?? [];

            if ($from) {
                return \array_merge($to, $from);
            }

            return $to;
        });

        return $fiscalization;
    }

    /**
     * @todo CopyPaste from Sberbank pay system.
     * Do refactor.
     *
     * @param callable $responseCallback
     *
     * @return bool
     *
     * @throws SalePaymentException
     */
    private function response(callable $responseCallback): bool
    {
        $response = ['Fake response'];

        for ($i = 0; $i <= 10; $i++) {
            $response = $responseCallback();

            if ((int)$response['errorCode'] !== 1) {
                break;
            }
        }

        return $this->sberbankProcessing->parseResponse($response);
    }
}
