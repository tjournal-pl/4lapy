<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\External;

use FourPaws\External\Exception\FireBaseCloudMessagingException;
use GuzzleHttp\Client as HttpClient;
use paragraph1\phpFCM\Client;
use paragraph1\phpFCM\Message;
use paragraph1\phpFCM\Recipient\Device;
use paragraph1\phpFCM\Notification;
use FourPaws\App\Application;

class FireBaseCloudMessagingService
{
    const API_KEY = 'AAAAxDpl7KU:APA91bF5dwjOWylTSf7v5CzXcWUkVdZIYHyoj78W1uhfo5TOXXuNrJDCMC7loFO2xnAiw1sFz0khQaZuNgLuLr2La9cGjkA88YpM0zB2QDmavShPJ-sFbZd2jmMrLp1Ki9fJS8T6_WWE';
    
    /**
     * @var HttpClient
     */
    protected $transport;
    
    /**
     * FireBaseCloudMessagingService constructor.
     */
    public function __construct()
    {
        $this->transport = new HttpClient();
    }
    
    /**
     * @param $token
     * @param $messageText
     * @param $messageId
     * @param $messageType
     * @param $messageTitle
     * @param $photoUrl
     * @return \Psr\Http\Message\ResponseInterface
     * @throws FireBaseCloudMessagingException
     */
    public function sendNotification($token, $messageText, $messageId, $messageType, $messageTitle = '', $photoUrl = '')
    {
        $categoryTitle = '';
        $client = new Client();
        $client->setApiKey(static::API_KEY);
        $client->injectHttpClient(new HttpClient());
        
        $message = new Message();
        $message->addRecipient(new Device($token));

        if ($messageType == 'category') {
            $categoryTitle = \Bitrix\Iblock\SectionTable::getList([
                'select' => ['NAME'],
                'filter' => ['=ID' => $messageId]
            ])->fetch()['NAME'];
        }

        $message->setData([
            'body' => [
                // Обязательная часть (названия полей в данном случае важно) :
                'aps'     => [
                    'badge' => 1, // красный кружок на иконке приложения с количеством оповещений
                    'title' => $messageTitle,
                    'alert' => $messageText, // текст, который будет показан пользователю в push- сообщении
                    'sound' => 'default', // можно указать звук при получении пуша
                ],
                // Опции
                'options' => [
                    'id'   => $messageId, // Идентификатор события
                    'url'  => $photoUrl ? getenv('SITE_URL') . $photoUrl : '',
                    'type' => $messageType, // Тип события
                    'title' => $categoryTitle // Заголовок категории
                ],
            ],
        ]);

        $response = $client->send($message);
        if ($response->getStatusCode() !== 200) {
            throw new FireBaseCloudMessagingException($response->getReasonPhrase(), $response->getStatusCode());
        }
        return $response;
    }
}