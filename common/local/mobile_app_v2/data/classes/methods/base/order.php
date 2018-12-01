<?

class order extends \stdClass
{
	const DELIVERY_ID_PICKUP = array(3, 5);
	const DELIVERY_ID_RESERVE = array(5);
	const DELIVERY_ID_COURIER = array(1, 4, 6, 7, 8, 11, 12);
	const DELIVERY_ID_REG_COURIER = array(7, 8, 11, 12);
	const DELIVERY_ID_COURIER_DPD = array(9);
	const DELIVERY_ID_PICKUP_DPD = array(10);
	const MAP_DELIVERY_ID_INTO_COMMUNIC = array(
		5 => '01', 7 => '01', 8 => '01', 11 => '01', 12 => '01',
		1 => '02', 3 => '01', 4 => '02', 6 => '02', 9 => '02', 10 => '02',
	);
	const PAYMENT_CODE = array('cash', 'cashless', 'applepay', 'android');
	const MAP_PAYMENT_CODE_INTO_PAYMENT_ID = array(
		'cash' => '1', 
		'cashless' => '3', 
		'applepay' => '3',
		'android' => '3'
	);

	private $id;
	private $fields;
	private $props;
	private $basket;
	private $user;
	private $is_new = true;
	private static $status_list;
	private static $delivery_list;

	function __construct($id = 0)
	{
		\Bitrix\Main\Loader::includeModule('sale');

		if ($id && $this->id = intval($id)) {
			$this->is_new = false;
			$this->fields = \Bitrix\Sale\OrderTable::getRowById($this->id);
			if ($arOrder = CSaleOrder::GetByID($this->id)) {
				$this->fields['PS_STATUS_CODE'] = $arOrder['PS_STATUS_CODE'];
			}
		}
	}

	public static function getStatusIdFinal()
	{
		return array('G', 'A', 'Y', 'K', 'P', 'O', 'J');
	}

	public static function getDisallowStatusId()
	{
		// return array('A', 'K');
		return array();
	}

	public static function getStatusList()
	{
		\Bitrix\Main\Loader::includeModule('sale');

		if (is_null(self::$status_list)) {
			$oStatuses = \Bitrix\Sale\StatusLangTable::getList(array(
				'filter' => array('=LID' => 'ru'),
				'select' => array('ID', 'NAME'),
			));

			while ($arStatus = $oStatuses->fetch()) {
				self::$status_list[$arStatus['ID']] = $arStatus;
			}
		}

		return self::$status_list;
	}

	public static function getStatusById($statusId)
	{
		if ($statusId) {
			$arStatusList = self::getStatusList();

			if (isset($arStatusList[$statusId])) {
				return $arStatusList[$statusId];
			}
		}

		return null;
	}

	public static function getDeliveryList()
	{
		\Bitrix\Main\Loader::includeModule('sale');

		if (is_null(self::$delivery_list)) {
			$oDeliveries = \Bitrix\Sale\DeliveryTable::getList(array(
				'select' => array('ID', 'NAME'),
			));

			while ($arDelivery = $oDeliveries->fetch()) {
				self::$delivery_list[$arDelivery['ID']] = $arDelivery;
			}
		}

		return self::$delivery_list;
	}

	public static function getDeliveryById($deliveryId)
	{
		if ($deliveryId) {
			$arDeliveryList = self::getDeliveryList();

			if (isset($arDeliveryList[$deliveryId])) {
				return $arDeliveryList[$deliveryId];
			}
		}

		return null;
	}

	public static function hasReviews($orderId)
	{
		$result = true;

		if ($orderId && $orderId = intval($orderId)) {
			$connection = \Bitrix\Main\Application::getConnection();
			$sqlHelper = $connection->getSqlHelper();

			$sql = "SELECT opros_4, opros_5, opros_8 FROM opros_checks WHERE check_id='".$sqlHelper->forSql($orderId, 50)."'";

			$result = (bool)($connection->query($sql)->getSelectedRowsCount());
		}

		return $result;
	}

	public function getProperties()
	{
		if (is_null($this->props)) {
			$arPropsId = array();
			$oProps = \CSaleOrderProps::GetList(
				array(),
				array(
					'CODE' => array(
						'delivery_address',
						'delivery_date',
						'delivery_time',
						'DELIVERY_CITY',
						'STREET',
						'HOME',
						'KVART',
						'DETAILS',
						'contact_phone',
						'contact_phone_alt',
						'contact_person',
						'fromAPP',
						'communic',
						'region_courier_from_dc',
						'device_type'
					)
				)
			);

			while ($arProp = $oProps->Fetch()) {
				$this->props[] = new \order_property($arProp);
				$arPropsId[] = $arProp['ID'];
			}

			if (!$this->in_new) {
				$arPropsValue = array();
				$oPropsValue = \CSaleOrderPropsValue::GetList(
					array(),
					array(
						'ORDER_ID' => $this->id,
						'ORDER_PROPS_ID' => $arPropsId
					),
					false,
					false,
					array('ORDER_PROPS_ID', 'VALUE')
				);

				while ($arPropValue = $oPropsValue->Fetch()) {
					$arPropsValue[$arPropValue['ORDER_PROPS_ID']] = $arPropValue['VALUE'];
					$this->props_value[$arPropValue['ORDER_PROPS_ID']]['VALUE'] = $arPropValue['VALUE'];
				}

				foreach ($this->props as $oProp) {
					if (isset($arPropsValue[$oProp->getField('ID')])) {
						$oProp->setValue($arPropsValue[$oProp->getField('ID')]);
					}
				}
			}
		}

		return $this->props;
	}

	public function getField($fieldName)
	{
		return $this->fields[$fieldName];
	}

	public function setUser(\user $oUser)
	{
		$this->user = $oUser;
		$this->fields['USER_ID'] = $oUser->getField('ID');
	}

	public function setField($fieldName, $fieldValue)
	{
		$this->fields[$fieldName] = $fieldValue;
	}

	public function setFields($arFieldsValue)
	{
		foreach ($arFieldsValue as $fieldName => $fieldValue) {
			$this->setField($fieldName, $fieldValue);
		}
	}

	public function doCalculate()
	{
		if ($arCalculateOrder = $this->getCalculate()) {
			return $this->getDataCalculate($arCalculateOrder);
		}

		return null;
	}

	public function doSave()
	{
		$GLOBALS["DB"]->StartUsingMasterOnly();
		if ($arCalculateOrder = $this->getCalculate()) {
			$errors = array();
			$this->fields['LID'] = $arCalculateOrder['LID'];
			$this->fields['PERSON_TYPE_ID'] = $arCalculateOrder['PERSON_TYPE_ID'];
			$this->fields['PAYED'] = 'N';
			$this->fields['CANCELED'] = 'N';
			$this->fields['STATUS_ID'] = 'N';
			$this->fields['PRICE'] = $arCalculateOrder['PRICE'];
			$this->fields['CURRENCY'] = $arCalculateOrder['CURRENCY'];
			$this->fields['USER_ID'] = $arCalculateOrder['USER_ID'];
			$this->fields['DELIVERY_PRICE'] = $arCalculateOrder['DELIVERY_PRICE'];
			$this->fields['PRICE_DELIVERY'] = $arCalculateOrder['DELIVERY_PRICE'];
			$this->fields['ADDITIONAL_INFO'] = '';
			$arCoupons = $_SESSION['CATALOG_USER_COUPONS'];

			log_MP_order(array(
				'корзина перед dosave 0' => $arCalculateOrder
			));
			$GLOBALS["DB"]->StartUsingMasterOnly();
			$this->id = \CSaleOrder::DoSaveOrder($arCalculateOrder, $this->fields, 0, $errors, $arCoupons, $arStoreBarcodeOrderFormData, $bSaveBarcodes);
			
			$oOrderSave666 = new \order($this->id);

			log_MP_order(array(
				'корзина после dosave 1' => $oOrderSave666->getBasket()->getBasketItems()
			));

			if ($this->id) {
				$this->is_new = false;

				$oGoodsList = new \goods_list;

				foreach ($this->getBasket()->getBasketItems() as $oBasketItem) {

					$arProdInfo = $oGoodsList->GetProdInfo($oBasketItem->getField('PRODUCT_ID'));
					$arProdInfo = $arProdInfo[$oBasketItem->getField('PRODUCT_ID')];

					//формируем стоимость позиции
					$arProdInfo['price'] = array(
						'actual' => $oBasketItem->getField('PRICE'),
						'old' => ($oBasketItem->getField('DISCOUNT_PRICE') > 0 ? $oBasketItem->getField('DISCOUNT_PRICE') + $oBasketItem->getField('PRICE') : '')
					);

					//получаем количество бонусов по позиции
					$arProductBonus = $oGoodsList->GetProductBonus($arProdInfo['price'],$arProdInfo);

					$arProps = array();

					$arProps[] = array(
						"NAME" => 'BONUS_IN',
						"CODE" => 'BONUS_IN',
						"VALUE" => $arProductBonus['bonus_user'],
						"SORT" => '500'
					);

					\CSaleBasket::Update(
						$oBasketItem->getField('ID'), 
						array(
							'CUSTOM_PRICE' => 'Y',
							'PROPS' => $arProps
							)
						);
				}

				$arUpdateFields = array();

				// SUM_PAID
				if ($this->fields['SUM_PAID']) {
					$withdrawSum = \CSaleUserAccount::Withdraw(
						$this->fields['USER_ID'],
						$this->fields['SUM_PAID'],
						'RUB',
						$this->id
					);

					if ($withdrawSum > 0 && $withdrawSum != $this->fields['SUM_PAID']) {
						$arUpdateFields['SUM_PAID'] = $withdrawSum;
					}
				}

				// Update
				if (!empty($arUpdateFields)) {
					\CSaleOrder::Update($this->id, $arUpdateFields);
				}

				//
				$this->fields = \Bitrix\Sale\OrderTable::getRowById($this->id);

				$oEvents = \GetModuleEvents('main', 'ProfBissOnAfterOrderAdd');

				while ($arEvent = $oEvents->Fetch()) {
					$arResult = \ExecuteModuleEvent($arEvent, $this->id, array(
						'ORDER_ID' => $this->id,
						'PAY_SYSTEM_ID' => $this->fields['PAY_SYSTEM_ID'],
					));
				}

				// $this->basket->clear();
				//если заказ создан успешно, помещаем юзера в группу "Делал заказ в МП"
				MyCUser::setMakingOrderInMP($arCalculateOrder['USER_ID']);
				return $this->id;
			}

			return null;
		}

		return null;
	}

	private function getCalculate()
	{
		$arBasketItems = array();

		foreach ($this->getBasket()->getBasketItems() as $oBasketItem) {
			$arBasketItems[] = array(
				'ID' => $oBasketItem->getField('ID'),
				'MODULE' => 'catalog',
				'PRICE' => $oBasketItem->getField('PRICE'),
				'DISCOUNT_PRICE' => $oBasketItem->getField('DISCOUNT_PRICE'),
				'PRODUCT_ID' => $oBasketItem->getField('PRODUCT_ID'),
				'QUANTITY' => $oBasketItem->getField('QUANTITY'),
			);
		}

		$arPropsValue = array();

		foreach ($this->getProperties() as $oProperty) {
			$arPropsValue[$oProperty->getField('ID')] = $oProperty->getValue();
		}

		$arErrors = array();
		$arWarnings = array();

		$arResult = \CSaleOrder::DoCalculateOrder(
			$this->fields['SITE_ID'],
			$this->fields['USER_ID'],
			$arBasketItems,
			$this->fields['PERSON_TYPE_ID'],
			$arPropsValue,
			$this->fields['DELIVERY_ID'],
			$this->fields['PAY_SYSTEM_ID'],
			array(),
			$arErrors,
			$arWarnings
		);

		if (empty($arErrors)) {
			foreach ($arResult['BASKET_ITEMS'] as $arProduct) {
				foreach ($this->basket->getBasketItems() as $oBasketItem) {
					if ($oBasketItem->getField('PRODUCT_ID') == $arProduct['PRODUCT_ID']) {
						$oBasketItem->setFields(array(
							'PRICE' => round($arProduct['PRICE'], 2),
							'DISCOUNT_PRICE' => $arProduct['PRICE'] + $arProduct['DISCOUNT_PRICE'] - round($arProduct['PRICE'], 2),
						));
						break;
					}
				}
			}

			// пересчитываем способ доставки для курьера
			if (in_array($arResult['DELIVERY_ID'], \order::DELIVERY_ID_COURIER)) {
				$arDeliveryList = \MyCAjax::GetDeliveryList($arResult['DELIVERY_LOCATION'], '', '', $arResult['ORDER_PRICE']);
				$arDeliveryList = ($arDeliveryList['result'] ? $arDeliveryList['data'] : array());

				if (!empty($arDeliveryList)) {
					$hasDpdDelivery = false;

					foreach ($arDeliveryList as $arDelivery) {
						if (in_array($arDelivery['ID'], self::DELIVERY_ID_COURIER_DPD)) {
							// DPD
							$hasDpdDelivery = true;

							$arDeliveryInfo = \CDeliveryDPD::Calculate(
								\CDeliveryDPD::GetDeliveryCode().'_DD',
								array('without_cache' => false),
								array(
									'LOCATION_FROM' => \Bitrix\Main\Config\Option::get('sale', 'location', 12),
									// 'LOCATION_TO' => $arResult['DELIVERY_LOCATION'],
									'LOCATION_TO' => \CSaleLocation::getLocationCODEbyID($arResult['DELIVERY_LOCATION']),
									'FUSER_ID' => $this->user->getField('FUSER_ID'),
									'ORDER_PRICE' => $arResult['ORDER_PRICE'],
								)
							);

							if ($arDeliveryInfo && isset($arDeliveryInfo['cost'])) {
								$this->fields['DELIVERY_ID'] = $arResult['DELIVERY_ID'] = $arDelivery['ID'];
								$arResult['PRICE'] = $arResult['PRICE'] - $arResult['PRICE_DELIVERY'];
								$arResult['PRICE_DELIVERY'] = $arResult['DELIVERY_PRICE'] = round($arDeliveryInfo['cost'], 2);
								$arResult['PRICE'] += $arResult['PRICE_DELIVERY'];
							}

							break;
						}
					}

					if (!$hasDpdDelivery) {
						foreach ($arDeliveryList as $arDelivery) {
							if ($arDelivery['DESCRIPTION'] == 'courier') {
								// обычный курьер
								$this->fields['DELIVERY_ID'] = $arResult['DELIVERY_ID'] = $arDelivery['ID'];
								$arResult['PRICE'] = $arResult['PRICE'] - $arResult['PRICE_DELIVERY'];
								$arResult['PRICE_DELIVERY'] = $arResult['DELIVERY_PRICE'] = round($arDelivery['PRICE'], 2);
								$arResult['PRICE'] += $arResult['PRICE_DELIVERY'];
								break;
							}
						}
					}
				}
			}

			//08.09.17 убираем автоподстановку типа коммуникации
			foreach ($this->getProperties() as $oProperty) {
				if ($oProperty->getField('CODE') == 'communic') {
					if($oProperty->getValue())
					{

					}
					else
					{
						$oProperty->setValue($this::MAP_DELIVERY_ID_INTO_COMMUNIC[$this->fields['DELIVERY_ID']]);
					}


					$arResult['ORDER_PROP'][$oProperty->getField('ID')] = $oProperty->getValue();
				}
			}

			$arResult['SUM_PAID'] = $this->fields['SUM_PAID'];
			// $arResult['PRICE'] = $arResult['PRICE'] - $arResult['SUM_PAID'];

			return $arResult;
		} else {
			return null;
		}
	}

	private function getDataCalculate($arCalculateOrder)
	{
		$basketPrice = 0;
		$basketDiscountPrice = 0;

		foreach ($arCalculateOrder['BASKET_ITEMS'] as $arBasketItem) {
			$basketPrice += $arBasketItem['QUANTITY'] * $arBasketItem['PRICE'];
			$basketDiscountPrice += $arBasketItem['QUANTITY'] * $arBasketItem['DISCOUNT_PRICE'];
		}

		$basketDiscountPrice = $basketPrice + $basketDiscountPrice - round($basketPrice, 2);
		$basketDiscountPrice = round($basketDiscountPrice, 2);
		$basketPrice = round($basketPrice, 2);
		$summPaid = ($arCalculateOrder['SUM_PAID'] ?: 0);
		$orderPrice = round($arCalculateOrder['PRICE'], 2);

		return array(
			'total_price' => array(
				'actual' => $orderPrice - $summPaid,
				'old' => $orderPrice + $basketDiscountPrice
			),
			'price_details' => array(
				array(
					'id' => 'cart_price_old', 'title'	=> 'Стоимость товаров без скидки',
					'value'	=> $basketPrice + $basketDiscountPrice
				),
				array(
					'id' => 'cart_price', 'title'	=> 'Стоимость товаров со скидкой',
					'value'	=> $basketPrice
				),
				array(
					'id' => 'discount', 'title'	=> 'Скидка',
					'value'	=> ($basketDiscountPrice ?: 0)
				),
				array(
					'id' => 'delivery', 'title'	=> 'Стоимость доставки',
					'value'	=> (round($arCalculateOrder['PRICE_DELIVERY'], 2) ?: 0)
				)
			),
			'card_details' => array(
				array(
					'id' => 'bonus_add', 'title'	=> 'Начислено',
					'value'	=> 0
				),
				array(
					'id' => 'bonus_sub', 'title'	=> 'Списано',
					'value'	=> $summPaid
				)
			)
		);
	}

	public function getBasket()
	{
		if (is_null($this->basket)) {
			if ($this->is_new) {
				if ($this->user) {
					$this->basket = new \basket(array('fuser_id' => $this->user->getFuserId()));
				} else {
					$this->basket = new \basket();
				}
			} else {
				$this->basket = new \basket(array('order_id' => $this->id));
			}
		}

		return $this->basket;
	}

	public function setBasket(\basket $oBasket)
	{
		$this->basket = $oBasket;
	}

	public function getData()
	{
		$arResult = null;

		if (!is_null($this->fields)) {
			$arUser = \user::getById($this->fields['USER_ID']);

			$basketPrice = 0;
			$basketDiscountPrice = 0;

			if ($this->fields['CHEQUE_ID']) {
				$basketPrice = round($this->fields['PRICE'], 2);
				$basketDiscountPrice = round($this->fields['PRICE'] - $this->fields['PRICE_DISCOUNTED'], 2);
			} else {
				foreach ($this->getBasket()->getBasketItems() as $oBasketItem) {
					$basketPrice += $oBasketItem->getField('QUANTITY') * $oBasketItem->getField('PRICE');
					$basketDiscountPrice += $oBasketItem->getField('QUANTITY') * $oBasketItem->getField('DISCOUNT_PRICE');
				}

				$basketDiscountPrice = $basketPrice + $basketDiscountPrice - round($basketPrice, 2);
				$basketPrice = round($basketPrice, 2);
			}
			$orderPrice = round($this->fields['PRICE'], 2);
			$summPaid = ($this->fields['SUM_PAID'] ?: 0);

			$arResult = array(
				'id' => $this->id,
				'cart_param' => array(
					'goods' => array(),
					'card' => ($arUser['card']['number'] ?: ''),
					'card_used' => ($this->fields['SUM_PAID'] > 0 ? $this->fields['SUM_PAID'] : ''),
					'delivery_type' => '',
					'delivery_place' => array('id' => '', 'city' => '', 'street_name' => '', 'house' => '', 'flat' => '', 'details' => ''),
					'delivery_range_id' => '',
					'delivery_range_date' => '',
					'pickup_place' => '',
					'comment' => '',
					'user_phone' => '',
					'user_phone_alt' => '',
				),
				'cart_calc' => array(
					'total_price' => array(
						'actual' => $orderPrice - $summPaid,
						'old' => $orderPrice + $basketDiscountPrice
					),
					'price_details' => array(
						array(
							'id' => 'cart_price_old', 'title'	=> 'Стоимость товаров без скидки',
							'value'	=> $basketPrice + $basketDiscountPrice
						),
						array(
							'id' => 'cart_price', 'title'	=> 'Стоимость товаров со скидкой',
							'value'	=> $basketPrice
						),
						array(
							'id' => 'discount', 'title'	=> 'Скидка',
							'value'	=> ($basketDiscountPrice ?: '')
						),
						array(
							'id' => 'delivery', 'title'	=> 'Стоимость доставки',
							'value'	=> (round($this->fields['PRICE_DELIVERY'], 2) ?: 0)
						),
					),
					'card_details' => array(
						array(
							'id' => 'bonus_add', 'title'	=> 'Начислено',
							'value'	=> ($this->fields['SUM_ACCRUED'] ?: 0)
						),
						array(
							'id' => 'bonus_sub', 'title'	=> 'Списано',
							'value'	=> $summPaid
						)
					),
				),
				'date' => $this->fields['DATE_INSERT']->format(API_DATE_FORMAT),
				'time' => $this->fields['DATE_INSERT']->format(API_TIME_FORMAT),
				'status' => '',
				'review_enabled' => (in_array($this->fields['STATUS_ID'], array('K', 'A')))?false:!self::hasReviews($this->id),
				'completed' => (in_array($this->fields['STATUS_ID'], $this->getStatusIdFinal())),
				'is_online' => !(isset($this->fields['IS_ROZN']) && $this->fields['IS_ROZN']),
				'paid' => ($this->fields['PS_STATUS_CODE'] == 'Hold' || $this->fields['PS_STATUS_CODE'] == 'Pay')
			);

			//
			if ($arStatus = self::getStatusById($this->fields['STATUS_ID'])) {
				// $arResult['status'] = $arStatus['NAME'];
				$arResult['status'] = array('code'=>$this->fields['STATUS_ID'], 'title'=>$arStatus['NAME']);
			}

			//
			if (in_array($this->fields['DELIVERY_ID'], self::DELIVERY_ID_RESERVE)) {
				$arResult['cart_param']['delivery_type'] = 5;
			} elseif (in_array($this->fields['DELIVERY_ID'], self::DELIVERY_ID_PICKUP)) {
				$arResult['cart_param']['delivery_type'] = 3;
			} else {
				$arResult['cart_param']['delivery_type'] = 1;
			}

			//
			foreach ($this->getProperties() as $oProperty) {
				$propValue = $oProperty->getValue();
				$propCode = $oProperty->getField('CODE');

				if ($propValue) {
					if ($propCode == 'delivery_date') {
						try {
							$oDate = new \Bitrix\Main\Type\Date($propValue);
							$arResult['cart_param']['delivery_range_date'] = $oDate->format(API_DATE_FORMAT);
						} catch (Exception $e) {
						}
					} elseif ($propCode == 'delivery_time') {
						$arResult['cart_param']['delivery_range_id'] = $propValue;
					} elseif ($propCode == 'delivery_address') {
						if (in_array($this->fields['DELIVERY_ID'], self::DELIVERY_ID_PICKUP)) {
							$arShop = \shop::getByCode($propValue);
							$arResult['cart_param']['pickup_place'] = "{$arShop['title']}, {$arShop['address']}";
						} else {
							$arResult['cart_param']['pickup_place'] =  $propValue;
						}
					} elseif ($propCode == 'contact_phone') {
						$arResult['cart_param']['user_phone'] = $propValue;
					} elseif ($propCode == 'contact_phone_alt') {
						$arResult['cart_param']['user_phone_alt'] = $propValue;
					} elseif ($propCode == 'DELIVERY_CITY') {
						$arResult['cart_param']['delivery_place']['city'] = \city::getById($propValue);
					} elseif ($propCode == 'STREET') {
						$arResult['cart_param']['delivery_place']['street_name'] = $propValue;
					} elseif ($propCode == 'HOME') {
						$arResult['cart_param']['delivery_place']['house'] = $propValue;
					} elseif ($propCode == 'KVART') {
						$arResult['cart_param']['delivery_place']['flat'] = $propValue;
					} elseif ($propCode == 'DETAILS') {
						$arResult['cart_param']['delivery_place']['details'] = $propValue;
					}
				}
			}

			$arResult['cart_param']['goods'] = $this->getBasket()->getData();
		}

		return $arResult;
	}

	public static function isExist($orderId)
	{
		\Bitrix\Main\Loader::includeModule('sale');

		return (bool)\Bitrix\Sale\OrderTable::getRowById($orderId);
	}
}