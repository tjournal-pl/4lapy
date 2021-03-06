<?php

namespace FourPaws\Helpers;


class ProtectorHelper
{
    const TYPE_REGISTER_SMS_SEND = 'registerSendSms';
    const TYPE_REGISTER_SMS_RESEND = 'registerResendSms';
    const TYPE_FAST_ORDER_CREATE = 'fastOrderCreate';
    const TYPE_GRANDIN_REQUEST_ADD = 'grandinRequestAdd';
    const TYPE_FESTIVAL_REQUEST_ADD = 'festivalRequestAdd';
    const TYPE_PIGGY_BANK_EMAIL_SEND = 'piggyBankEmailSend';
    const TYPE_PERSONAL_OFFERS_EMAIL_SEND = 'personalOffersEmailSend';
    const TYPE_MEALFEEL_REQUEST_ADD = 'mealfeelRequestAdd';
    const TYPE_AUTH = 'auth';

    static $types = [
        self::TYPE_REGISTER_SMS_SEND,
        self::TYPE_REGISTER_SMS_RESEND,
        self::TYPE_FAST_ORDER_CREATE,
        self::TYPE_GRANDIN_REQUEST_ADD,
        self::TYPE_FESTIVAL_REQUEST_ADD,
        self::TYPE_PIGGY_BANK_EMAIL_SEND,
        self::TYPE_PERSONAL_OFFERS_EMAIL_SEND,
        self::TYPE_AUTH,
        self::TYPE_MEALFEEL_REQUEST_ADD,
    ];

    /**
     * @param $type
     * @return bool
     */
    static private function checkType($type) {

        if (!in_array($type, self::$types)) {
            return false;
        }

        return true;
    }

    /**
     * @param $type
     * @return array
     * @throws \Exception
     */
    static public function generateToken($type) {

        $token = [
            'token' => bin2hex(random_bytes(30)),
            'field' => bin2hex(random_bytes(30)),
        ];

        $_SESSION['protector'][$type] = $token;

        return $token;
    }

    static public function getField($type) {

        if (!self::checkType($type)) {
            return false;
        }

        return $_SESSION['protector'][$type]['field'] ?: self::TYPE_REGISTER_SMS_SEND;
    }

    /**
     * @param $token
     * @param $type
     * @return bool
     */
    static public function checkToken($token, $type) {

        if (!self::checkType($type)) {
            return false;
        }

        if ($token == '' || is_null($token) || $token == false) {
            return false;
        }

        if (!isset($_SESSION['protector'][$type]['token'])) {
            return false;
        }

        if ($_SESSION['protector'][$type]['token'] != $token) {
            return false;
        }

        unset($_SESSION['protector'][$type]);

        return true;
    }
}
