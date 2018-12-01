<?

class user_cart_calc extends \APIServer
{
	//расчет корзины
	public function post($arInput)
	{
		// log_($arInput);
		$arResult = null;

		$card = (string)$arInput['cart_param']['card'];
		$cardUsed = intval($arInput['cart_param']['card_used']);
		$deliveryId = intval($arInput['cart_param']['delivery_type']);
		$deliveryPlace = (array)$arInput['cart_param']['delivery_place'];
		$deliveryTimeId = (string)$arInput['cart_param']['delivery_range_id'];
		$deliveryDate = (string)$arInput['cart_param']['delivery_range_date'];
		$pickupShopId = (string)$arInput['cart_param']['pickup_place'];
		$comment = (string)$arInput['cart_param']['comment'];
		$userPhone = (string)$arInput['cart_param']['user_phone'];
		$userExtraPhone = (string)$arInput['cart_param']['extra_phone'];
		$promocode = (string)$arInput['cart_param']['promocode'];

		//
		$oReqProducts = new \collection;

		foreach ($arInput['cart_param']['goods'] as $arGoods) {
			$oReqProdQty = new \req_product_qty($arGoods);

			if ($oReqProdQty->hasErrors()) {
				$this->addError('required_params_missed');
			} else {
				if ($oProd = $oReqProducts->get($oReqProdQty->getField('PRODUCT_ID'))) {
					$oProd->setField('QUANTITY', $oProd->getField('QUANTITY') + $oReqProdQty->getField('QUANTITY'));
				} else {
					$oReqProducts->addItem($oReqProdQty->getField('PRODUCT_ID'), $oReqProdQty);
				}
			}
		}

		// общие параметры
		if (!$oReqProducts->count()
			|| !$deliveryId
			|| (!$card && $cardUsed)
			|| !in_array($deliveryId, array_merge(\order::DELIVERY_ID_PICKUP, \order::DELIVERY_ID_COURIER))
		) {
			$this->addError('required_params_missed');
		}

		// параметры для самовывоза
		if (in_array($deliveryId, \order::DELIVERY_ID_PICKUP) && !$pickupShopId) {
			$this->addError('required_params_missed');
		}

		// параметры для курьера
		if (in_array($deliveryId, \order::DELIVERY_ID_COURIER)
			&& (!$deliveryPlace['city']['id']
				|| !$deliveryPlace['street_name']
				|| !$deliveryPlace['house']
				|| !isset($deliveryTimeId)
			)
		) {
			$this->addError('required_params_missed');
		}

		if (!$this->hasErrors()) {
			$shopCode = (in_array($deliveryId, \order::DELIVERY_ID_PICKUP) ? $pickupShopId : \GeoCatalog::GetCurierStore($deliveryPlace['city']['id'])['sStoreCode']);
			$arAvailable = \shop::getAvailable($shopCode, $oReqProducts, (in_array($deliveryId, \order::DELIVERY_ID_PICKUP)));
			// тут КОСТЫЛЬ для довоза товара с РЦ для местных курьеров
			// если это курьер, и желаемая дата доставки больше, чем ближайшая дата поставки
			// то довозимые товары кидаем в массив available
			if(
				in_array($deliveryId, \order::DELIVERY_ID_COURIER) and
				// in_array($arAvailable['availability_status'], array('available_later', 'available')) and 
				(strtotime($arAvailable['availability_date']) <= strtotime($deliveryDate))
			){

				$arAvailable = \shop::getAvailable($shopCode, $oReqProducts, true);
			}

			if ($arAvailable) {
				foreach ($arAvailable['not_available'] as $arProduct) {
					if ($oProd = $oReqProducts->get($arProduct['goods']['id'])) {
						$oProd->setField('AVAILABLE', 'N');
						$oProd->setField('QUANTITY', $arProduct['qty']);
					}
				}

				foreach ($arAvailable['available'] as $arProduct) {
					if ($oProd = $oReqProducts->get($arProduct['goods']['id'])) {
						$oProd->setField('AVAILABLE', 'Y');
						$oProd->setField('QUANTITY', $arProduct['qty']);
					}
				}

				if (!$deliveryDate) {
					if ($arAvailable['availability_date']) {
						$deliveryDate = $arAvailable['availability_date'];
					} else {
						$oDate = new \Bitrix\Main\Type\Date;
						$deliveryDate = $oDate->format(API_DATE_FORMAT);
					}
				}

				$arResult['available_goods'] = (array)$arAvailable['available'];
				$arResult['not_available_goods'] = (array)$arAvailable['not_available'];
			} else {
				$this->addError('shop_available_error');
				return null;
			}

			if ($this->getUserId()) {
				$oUser = new \user($this->getUserId());
			} else {
				$oUser = new \user();
			}

			$oUser->setField('FUSER_ID', $this->getFuserId());

			if($promocode)
				$promocode_result = MyCAjax::AddCouponAPI($promocode, $this->getFuserId(), $this->getUserId());

			$oOrder = new \order;
			$oOrder->setUser($oUser);

			$oOrder->setFields(array(
				'DELIVERY_ID' => $deliveryId,
				'SITE_ID' => SITE_ID,
				'PERSON_TYPE_ID' => 1,
				'PAY_SYSTEM_ID' => 1,
				'SUM_PAID' => $cardUsed,
			));

			//
			$summBasket = 0;

			foreach ($oOrder->getBasket()->getBasketItems() as $oBasketItem) {
				$productId = $oBasketItem->getField('PRODUCT_ID');

				if ($oProd = $oReqProducts->get($productId)) {
					if ($oProd->getField('AVAILABLE') == 'Y') {
						if ($oProd->getField('QUANTITY') != $oBasketItem->getField('QUANTITY')) {
							$oBasketItem->setField('QUANTITY', $oProd->getField('QUANTITY'));
						}

						$summBasket += round($oBasketItem->getField('PRICE'), 2) * $oBasketItem->getField('QUANTITY');
					} else {
						$oOrder->getBasket()->deleteBasketItem($oBasketItem);
					}
				} else {
					$oOrder->getBasket()->deleteBasketItem($oBasketItem);
				}
			}

			if (in_array($oOrder->getField('DELIVERY_ID'), \order::DELIVERY_ID_COURIER)) {
				foreach ($oOrder->getProperties() as $oProperty) {
					if ($oProperty->getField('CODE') == 'DELIVERY_CITY') {
						$oProperty->setValue($deliveryPlace['city']['id']);
					}
				}
			}

			$arDoCalculateOrder = $oOrder->doCalculate();

			//
			$summBonus = 0;

			foreach ($arResult['available_goods'] as &$arProduct) {
				foreach ($oOrder->getBasket()->getBasketItems() as $oBasketItem) {
					if ($oBasketItem->getField('PRODUCT_ID') == $arProduct['goods']['id']) {
						$oBasketItem->user=$this->User;
						$arProduct = $oBasketItem->getData();
						$summBonus += $oBasketItem->getField('QUANTITY') * $arProduct['goods']['bonus_user'];
						break;
					}
				}
			}

			foreach ($arDoCalculateOrder['card_details'] as &$arCardDetail) {
				if ($arCardDetail['id'] = 'bonus_add') {
					$arCardDetail['value'] = $summBonus;
					break;
				}
			}

			unset($arProduct, $arCardDetail);

			//
			foreach ($arResult['not_available_goods'] as &$arProduct) {
				foreach ($oOrder->getBasket()->getBasketItems() as $oBasketItem) {
					if ($oBasketItem->getField('PRODUCT_ID') == $arProduct['goods']['id']) {
						$arProduct = $oBasketItem->getData();
						break;
					}
				}
			}

			unset($arProduct);

			$arResult['total_price'] = (array)$arDoCalculateOrder['total_price'];
			$arResult['price_details'] = (array)$arDoCalculateOrder['price_details'];
			$arResult['card_details'] = (array)$arDoCalculateOrder['card_details'];

			$arResult['promocode_result'] = ($promocode and $promocode_result["result"])?$promocode:'';

			switch ($oOrder->getField('DELIVERY_ID'))
			{
				case '1':
				case '3':
				case '4':
				case '6':
					$should_show_communication_choice = true;
					break;

				default:
					$should_show_communication_choice = false;
					break;
			}
			$arResult['should_show_communication_choice'] = $should_show_communication_choice;
		}
		// log_($arResult);
		// log_('-------------------------------------------------------------------');
		// echo "<pre>";print_r($_SESSION);echo "</pre>"."\r\n";
		return $arResult;
	}
}
