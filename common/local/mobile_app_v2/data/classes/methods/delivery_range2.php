<?
class delivery_range2 extends \APIServer
{
	const INTERVAL_DAYS = 6; // за сколько дней, включая текущий, выводить интервалы доставки

	// получение списка интервалов и дат доставки
	public function get($arInput)
	{
		\Bitrix\Main\Loader::includeModule('sale');

		$arResult = null;

		if (!(isset($arInput['city_id']) && $cityId = intval($arInput['city_id']))) {
			$this->addError($this->ERROR['required_params_missed']);
		}

		if (!$this->hasErrors()) {
			$arDeliveryList = \MyCAjax::GetDeliveryList($cityId, '', '', 0);
			$arDeliveryList = ($arDeliveryList['result'] ? $arDeliveryList['data'] : array());

			if (!empty($arDeliveryList)
				&& array_intersect(\order::DELIVERY_ID_COURIER_DPD, array_keys($arDeliveryList))
			) {
				$arDeliveryInfo = \CDeliveryDPD::Calculate(
					\CDeliveryDPD::GetDeliveryCode().'_TD',
					array('without_cache' => false),
					array(
						'LOCATION_FROM' =>  \Bitrix\Main\Config\Option::get('sale', 'location', 12),
						'LOCATION_TO' => CSaleLocation::getLocationCODEbyID($cityId),
						'FUSER_ID' => $this->getFuserId(),
					)
				);

				if ($arDeliveryInfo && $arDeliveryInfo['days'] && $arDeliveryInfo['RESULT'] != 'ERROR') {
					$oDate = new \Bitrix\Main\Type\Date();
					$oDateDelivery = new \Bitrix\Main\Type\Date();
					$oDate->add('1 days');
					$oDateDelivery->add("{$arDeliveryInfo['days']} days")->add('1 days');

					$arResult[] = array(
						'id' => 666,
						'title' => "Срок доставки от {$arDeliveryInfo['days']} раб. дн.",
						'delivery_date' => $oDateDelivery->format(API_DATE_FORMAT),
						'available' => array(
							'day' => $oDate->format(API_DATE_FORMAT),
							'time' => $oDate->format(API_TIME_FORMAT),
						)
					);
				} else {
					$arResult['feedback_text'] = 'К сожалению, в вашей корзине нет товаров, которые мы могли бы доставить в указанный город';
				}
			} else {
				if ($arDeliveryTime = \GeoCatalog::GetDeliveryTimeV2($cityId, ($this->User)?$this->User['basket_id']:'')) {
					foreach ($arDeliveryTime as $key_deliv_day => $value_deliv_day) {
						if ($value_deliv_day['empty']) {
							unset($arDeliveryTime[$key_deliv_day]);
						}
					}
					foreach ($arDeliveryTime as $key_deliv_day => $value_deliv_day) {
						if ($value_deliv_day['date'] == date("d.m.Y", mktime(0, 0, 0, date("m"), date("d")+$this::INTERVAL_DAYS, date("Y")))) {
							break;
						}

						if ($value_deliv_day['date'] == date("d.m.Y", mktime(0, 0, 0, date("m"), date("d"), date("Y")))) {
							$title = 'Сегодня';
						} elseif ($value_deliv_day['date'] == date("d.m.Y", mktime(0, 0, 0, date("m"), date("d")+1, date("Y")))) {
							$title = 'Завтра';
						} else {
							$title = $value_deliv_day['date'];
						}

						foreach ($value_deliv_day['times'] as $key_deliv_time => $value_deliv_time) {
							if ($value_deliv_time['date'] == date('d.m.Y') && $value_deliv_time['time'] < date('H:i')) {
								continue;
							}
							$arResult[] = array(
								'id' => $value_deliv_time['val'],
								'title' => $title.' '.$value_deliv_time['label'],
								// 'sort' => $oDate->format('Y-m-d').' '.$arItem['available']['time'],
								'delivery_date' => $value_deliv_day['date'],
								'complete' => $value_deliv_day['complete'],
								'available' => array(
									'day' => $value_deliv_time['date'],
									'time' => $value_deliv_time['time'],
								)
							);
						}
					}
				}
			}
		}

		return $arResult;
	}
}
