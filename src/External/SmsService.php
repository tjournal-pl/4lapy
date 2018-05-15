<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\External;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Exception\SmsSendErrorException;
use FourPaws\External\SmsTraffic\Client;
use FourPaws\External\SmsTraffic\Exception\SmsTrafficApiException;
use FourPaws\External\SmsTraffic\Sms\IndividualSms;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\LogDoc\SmsLogDoc;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class SmsService
 *
 * @package FourPaws\External
 */
class SmsService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Client
     */
    protected $client;

    protected $parameters;

    /**
     * SmsService constructor.
     *
     * @throws ApplicationCreateException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws InvalidArgumentException
     * @throws \RuntimeException
     */
    public function __construct()
    {
        $container = Application::getInstance()->getContainer();
        $this->parameters = $container->getParameter('sms');

        $this->client = new Client($this->parameters['login'], $this->parameters['password'], $this->parameters['originator']);

        $this->setLogger(LoggerFactory::create('sms'));
    }

    /**
     * @param string $text
     * @param string $number
     *
     */
    public function sendSmsImmediate(string $text, string $number)
    {
        $this->sendSms($text, $number, true);
    }

    /**
     * @param string $text
     * @param string $number
     * @param bool $immediate
     */
    public function sendSms(string $text, string $number, bool $immediate = false)
    {
        try {
            $sms = new IndividualSms(
                [
                    [
                        $this->clearPhone($number),
                        $text,
                    ],
                ]
            );

            if ($immediate) {
                $this->client->setLogin($this->parameters['login.immediate']);
                $this->client->setPassword($this->parameters['password.immediate']);
            } else {
                $sms->updateParameters(
                    [
                        'start_date' => $this->buildQueueTime($this->parameters['start_messaging']),
                        'stop_date' => $this->buildQueueTime($this->parameters['stop_messaging']),
                        'isSendNextDay' => '1',
                        'isAbonentLocaleTime' => '1',
                    ]
                );
            }

            try {
                $this->client->send($sms);
            } catch (SmsTrafficApiException $e) {
                throw new SmsSendErrorException($e->getMessage(), $e->getCode(), $e);
            }
        } catch (SmsSendErrorException $e) {
            $this->logger->error(\sprintf('Sms send error: %s.', $e->getMessage()));
        }
    }

    /**
     * @param string $phone
     *
     * @throws SmsSendErrorException
     * @return string
     *
     */
    protected function clearPhone(string $phone): string
    {
        try {
            $formattedPhone = PhoneHelper::normalizePhone($phone);
            $phone = '7' . $formattedPhone;

            if (\mb_strlen($phone) === 11) {
                return $phone;
            }
        } catch (WrongPhoneNumberException $e) {
        }

        throw new SmsSendErrorException(\sprintf('Неверный формат номера телефона (%s)', $phone));
    }

    /**
     * @param string $time
     *
     * @return string
     */
    protected function buildQueueTime(string $time): string
    {
        return (new \DateTime($time))->format('Y-m-d H:i:s');
    }

    /**
     * Проверка метки, что уведомление уже отправлено
     *
     * @param string $smsEventName
     * @param string $smsEventKey
     * @return bool
     */
    public function isAlreadySent(string $smsEventName, string $smsEventKey): bool
    {
        $result = false;
        $smsDocLog = new SmsLogDoc();
        $doc = $smsDocLog->get($smsEventName, $smsEventKey);
        if ($doc) {
            $result = true;
        }

        return $result;
    }

    /**
     * Сохранение метки, что уведомление уже отправлено
     *
     * @param string $smsEventName
     * @param string $smsEventKey
     * @return bool
     */
    public function markAlreadySent(string $smsEventName, string $smsEventKey)
    {
        $result = false;
        $smsDocLog = new SmsLogDoc();
        $res = $smsDocLog->add($smsEventName, $smsEventKey);
        if ($res && $res->isSuccess()) {
            $result = true;
        }

        return $result;
    }
}
