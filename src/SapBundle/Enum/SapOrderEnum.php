<?php

namespace FourPaws\SapBundle\Enum;


final class SapOrderEnum
{
    /**
     * Способ получения заказа
     *
     * 01 – Курьерская доставка из РЦ;
     * 02 – Самовывоз из магазина;
     * 06 – Курьерская доставка из магазина;
     * 07 – Доставка внешним подрядчиком (курьер или самовывоз из пункта выдачи заказов);
     * 08 – РЦ – магазин – домой.
     */
    const DELIVERY_TYPE_COURIER_RC = '01';
    const DELIVERY_TYPE_PICKUP = '02';
    const DELIVERY_TYPE_COURIER_SHOP = '06';
    const DELIVERY_TYPE_CONTRACTOR = '07';
    const DELIVERY_TYPE_ROUTE = '08';
    
    /**
     * Тип доставки подрядчиком
     * Поле должно быть заполнено, если выбран способ получения заказа 07.
     *
     * ТД – от терминала до двери покупателя;
     * ТТ – от терминала до пункта выдачи заказов.
     */
    const DELIVERY_TYPE_CONTRACTOR_DELIVERY = 'ТД';
    const DELIVERY_TYPE_CONTRACTOR_PICKUP = 'ТТ';
    const DELIVERY_CONTRACTOR_CODE = '0000802070';
    
    const ORDER_PAYMENT_ONLINE_MERCHANT_ID = '850000314610';
    const ORDER_PAYMENT_ONLINE_CODE = '05';
    const ORDER_PAYMENT_STATUS_PAYED = '01';
    const ORDER_PAYMENT_STATUS_NOT_PAYED = '02';
    const ORDER_PAYMENT_STATUS_PRE_PAYED = '03';
    
    const PAYMENT_SYSTEM_ONLINE_ID = 3;
    
    const UNIT_PTC_CODE = 'PCE';
    
}
