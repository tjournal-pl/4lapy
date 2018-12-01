<?
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockType;
use FourPaws\Enum\IblockCode;

class city_nearest extends APIServer
{
	/*
	lat, lon - координаты точки
	*/

	public function get($arInput)
	{
		\Bitrix\Main\Loader::includeModule('iblock');

		if (!isset($arInput['lat']) || !isset($arInput['lon'])
			|| strlen($arInput['lat']) == 0 || strlen($arInput['lon']) == 0
		) {
			$this->addError($this->ERROR['required_params_missed']);
			return null;
		} else {
			$lon = floatval($arInput['lon']);
			$lat = floatval($arInput['lat']);
		}

		$arResult = array();

		if (!$this->hasErrors()) {
			$arResult = self::getNearest($lat, $lon);
		}

		return $arResult;
	}

	public static function getNearest($lat, $lon)
	{
		$arResult = array();
		$minDist = null;
		$cityId = null;

		$oElements = \CIBlockElement::GetList(
			array(),
			array(
				'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::REFERENCE_BOOKS, IblockCode::AREA_CITY),
				'ACTIVE' => 'Y',
				'!PROPERTY_GPS' => false,
			),
			false,
			false,
			array('ID', 'PROPERTY_GPS')
		);

		while ($arElement = $oElements->Fetch()) {
			list($latDb, $lonDb) = explode(',', $arElement['PROPERTY_GPS_VALUE']);
			$dist = pow($lon - $lonDb, 2) + pow($lat - $latDb, 2);

			if (is_null($minDist) || $minDist > $dist) {
				$minDist = $dist;
				$cityId = $arElement['ID'];
			}
		}

		if (!is_null($cityId)) {
			$cityId = \city::convGeo1toGeo2($cityId);

			if (!is_null($cityId)) {
				$arResult['city'] = \city::getById($cityId);
			}
		}

		return $arResult;
	}

	public static function getNearestId($id)
	{
		$cityId = 0;

		$arLocation = \Bitrix\Sale\Location\LocationTable::getList(array(
			'order' => array('PARENTS.LEFT_MARGIN' => 'ASC'),
			'filter' => array(
				'=ID' => $id,
				'=NAME.LANGUAGE_ID' => 'ru',
				'=PARENTS.NAME.LANGUAGE_ID' => 'ru',
				'=PARENTS.TYPE.CODE' => array('CITY', 'REGION'),
			),
			'select' => array('ID', 'PARENTS_NAME' => 'PARENTS.NAME.NAME', 'CITY_NAME' => 'NAME.NAME', 'LATITUDE', 'LONGITUDE'),
			'limit' => 1
		))->fetch();

		if ($arLocation) {
			if ($arLocation['LATITUDE'] != '0.000000' && $arLocation['LONGITUDE'] != '0.000000') {
				$lat = $arLocation['LATITUDE'];
				$lon = $arLocation['LONGITUDE'];
				$arCity = self::getNearest($lat, $lon);
				$cityId = $arCity['city']['id'];
			} else {
				$sOSM = file_get_contents('https://nominatim.openstreetmap.org/search?q='.urlencode($arLocation['CITY_NAME'].', '.$arLocation['PARENTS_NAME']).'&format=json&addressdetails=0&limit=1');
				$arOSM = json_decode($sOSM, true);
				if (is_array($arOSM) && !empty($arOSM)) {
					$lat = $arOSM[0]['lat'];
					$lon = $arOSM[0]['lon'];
					\Bitrix\Sale\Location\LocationTable::update($arLocation['ID'], array(
						'LATITUDE' => $lat,
						'LONGITUDE' => $lon
					));
					$arCity = self::getNearest($lat, $lon);
					$cityId = $arCity['city']['id'];
				}
			}
		}

		if ($cityId == 0) {
			$cityId = 12;
		}

		return $cityId;
	}
}