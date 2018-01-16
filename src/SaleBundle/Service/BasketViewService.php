<?php
/**
 * Created by PhpStorm.
 * Date: 15.01.2018
 * Time: 16:38
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Service;


/**
 * Class BasketViewService
 * @package FourPaws\SaleBundle\Service
 */
class BasketViewService
{
    /**
     * @param bool $isAjax
     *
     * @return string
     */
    public function getMiniBasketHtml(bool $isAjax = false): string
    {
        global $APPLICATION;
        ob_start();
        $APPLICATION->IncludeComponent(
            'bitrix:sale.basket.basket.line',
            'header.basket',
            [
                'COMPONENT_TEMPLATE' => 'header.basket',
                'PATH_TO_BASKET' => '/cart/',
                'PATH_TO_ORDER' => '/order/make/',
                'SHOW_NUM_PRODUCTS' => 'Y',
                'SHOW_TOTAL_PRICE' => 'Y',
                'SHOW_EMPTY_VALUES' => 'Y',
                'SHOW_PERSONAL_LINK' => 'Y',
                'PATH_TO_PERSONAL' => '/personal/',
                'SHOW_AUTHOR' => 'N',
                'PATH_TO_REGISTER' => '',
                'PATH_TO_AUTHORIZE' => '',
                'PATH_TO_PROFILE' => '/personal/',
                'SHOW_PRODUCTS' => 'Y',
                'SHOW_DELAY' => 'N',
                'SHOW_NOTAVAIL' => 'Y',
                'SHOW_IMAGE' => 'Y',
                'SHOW_PRICE' => 'Y',
                'SHOW_SUMMARY' => 'N',
                'POSITION_FIXED' => 'N',
                'HIDE_ON_BASKET_PAGES' => 'N',
                'IS_AJAX' => $isAjax
            ],
            false,
            ['HIDE_ICONS' => 'Y']
        );
        return ob_get_clean();
    }

    /**
     *
     *
     * @return string
     */
    public function getBasketHtml(): string
    {
        global $APPLICATION;
        ob_start();
        $APPLICATION->IncludeComponent(
            'fourpaws:basket',
            '',
            [],
            false,
            ['HIDE_ICONS' => 'Y']
        );
        return ob_get_clean();
    }
}