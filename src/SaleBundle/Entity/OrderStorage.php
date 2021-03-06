<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Entity;

use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\LocationBundle\LocationService;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\PersonalBundle\Service\AddressService;
use FourPaws\SaleBundle\Enum\OrderStorage as OrderStorageEnum;
use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\SaleBundle\Validation as SaleValidation;
use JMS\Serializer\Annotation as Serializer;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;
use \DateTime;

/**
 * Class OrderStorage
 * @package FourPaws\SaleBundle\Entity
 * @SaleValidation\OrderDelivery(groups={"delivery","payment"})
 * @SaleValidation\OrderAddress(groups={"delivery","payment"})
 * @SaleValidation\OrderPaymentSystem(groups={"payment"})
 * @SaleValidation\OrderBonusPayment(groups={"payment"})
 * @SaleValidation\OrderBonusCard(groups={"payment-card"})
 */
class OrderStorage
{
    /**
     * ID пользователя корзины
     *
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_FUSER_ID")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Assert\NotBlank(groups={"auth","delivery","payment"})
     */
    protected $fuserId = 0;

    /**
     * ID пользователя
     *
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_USER_ID")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $userId = 0;

    /**
     * Заполнял ли пользователь капчу
     *
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("CAPTCHA_FILLED")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Assert\IsTrue(groups={"auth","delivery","payment"}, message="Заполните капчу")
     */
    protected $captchaFilled = false;

    /**
     * ID типа оплаты
     *
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("PAY_SYSTEM_ID")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $paymentId = 0;

    /**
     * ID типа доставки
     *
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("DELIVERY_ID")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Assert\NotBlank(groups={"payment","delivery"})
     */
    protected $deliveryId = 0;

    /**
     * Комментарий к заказу
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("USER_DESCRIPTION")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $comment = '';

    /**
     * Имя
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_NAME")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Assert\NotBlank(groups={"auth", "payment","delivery"}, message="Укажите ваше имя")
     */
    protected $name = '';

    /**
     * Телефон
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_PHONE")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Assert\NotBlank(groups={"auth", "payment","delivery"}, message="Укажите ваш номер телефона")
     * @PhoneNumber(defaultRegion="RU",type="mobile")
     */
    protected $phone = '';

    /**
     * Доп. телефон
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_PHONE_ALT")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @PhoneNumber(defaultRegion="RU",type="mobile")
     */
    protected $altPhone = '';

    /**
     * E-mail
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_EMAIL")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Assert\Email(groups={"auth", "payment","delivery"})
     */
    protected $email = '';

    /**
     * Адрес (ID)
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("ADDRESS_ID")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $addressId = 0;

    /**
     * Улица
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_STREET")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $street = '';

    /**
     * Дом
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_HOUSE")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $house = '';

    /**
     * Корпус
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_BUILDING")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $building = '';

    /**
     * Квартира
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_APARTMENT")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $apartment = '';

    /**
     * Подъезд
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_PORCH")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $porch = '';

    /**
     * Этаж
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_FLOOR")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $floor = '';

    /**
     * Дата доставки (индекс выбранного значения из select'а)
     *
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("DELIVERY_DATE")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $deliveryDate = 0;

    /**
     * Интервал доставки (индекс выбранного значения из select'а)
     *
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("DELIVERY_INTERVAL")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $deliveryInterval = 0;

    /**
     * Разделение заказов
     *
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("ORDER_SPLIT")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $split = false;

    /**
     * Дата доставки для второго заказа (индекс выбранного значения из select'а)
     *
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("DELIVERY_DATE2")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $secondDeliveryDate = 0;

    /**
     * Интервал доставки для второго заказа (индекс выбранного значения из select'а)
     *
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("DELIVERY_INTERVAL2")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $secondDeliveryInterval = 0;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("USER_DESCRIPTION2")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $secondComment = '';

    /**
     * Код места доставки (или код терминала DPD)
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("DELIVERY_PLACE_CODE")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $deliveryPlaceCode = '';

    /**
     * Способ коммуникации
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_COM_WAY")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @SaleValidation\OrderPropertyVariant(propertyCode ="COM_WAY", groups={"auth", "payment","delivery"})
     */
    protected $communicationWay = '';

    /**
     * Код источника заказа
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_SOURCE_CODE")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $sourceCode = '';

    /**
     * Код партнера
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_PARTNER_CODE")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $partnerCode = '';

    /**
     * Город
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_CITY")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $city = '';

    /**
     * Город (местоположение)
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_CITY_CODE")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $cityCode = '';

    /**
     * Сумма оплаты бонусами
     *
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("BONUS")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $bonus = 0;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("DISCOUNT_CARD_NUMBER")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $discountCardNumber = '';

    /**
     * @var DateTime
     * @Serializer\Type("DateTime")
     * @Serializer\SerializedName("CURRENT_DATE")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $currentDate;

    /**
     * Бысрый заказ или нет
     *
     * @var bool
     */
    protected $fastOrder = false;

    /**
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("FROM_APP")
     * @Serializer\Groups(groups={"read","create"})
     */
    protected $fromApp = false;

    /**
     * Тип устройства
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_FROM_APP_DEVICE")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @SaleValidation\OrderPropertyVariant(propertyCode ="FROM_APP_DEVICE")
     */
    protected $fromAppDevice = '';

    /**
     * Долгота
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_LNG")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $lng = '';

    /**
     * Широта
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROPERTY_LAT")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $lat = '';

    /**
     * Заказ по подписке
     *
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("PROPERTY_SUBSCRIBE")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $subscribe = false;

    /**
     * Заказ по подписке
     *
     * @var int
     * @Serializer\Type("int")
     * @Serializer\Groups(groups={"read","update","delete"})
     */
    protected $subscribeId;

    /**
     * Промокод. Используется в МП вместое CouponStorage т.к. в МП нет сессий
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("PROMO_CODE")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @var string
     */
    protected $promoCode = '';

    /**
     * Район Москвы (код местоположения)
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("MOSCOW_DISTRICT_CODE")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @var string
     */
    protected $moscowDistrictCode = '';

    /**
     * Штрих-код приюта для доставки Добролап
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("SHELTER")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @var string
     */
    protected $shelter = '';

    /**
     * @return int
     */
    public function getFuserId(): int
    {
        return $this->fuserId ?? 0;
    }

    /**
     * @param int $fuserId
     *
     * @return OrderStorage
     */
    public function setFuserId(int $fuserId): OrderStorage
    {
        $this->fuserId = $fuserId;

        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId ?? 0;
    }

    /**
     * @param int $userId
     *
     * @return OrderStorage
     */
    public function setUserId(int $userId): OrderStorage
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCaptchaFilled(): bool
    {
        return $this->captchaFilled ?? false;
    }

    /**
     * @param bool $filled
     *
     * @return OrderStorage
     */
    public function setCaptchaFilled(bool $filled): OrderStorage
    {
        $this->captchaFilled = $filled;

        return $this;
    }

    /**
     * @return int
     */
    public function getPaymentId(): int
    {
        return $this->paymentId ?? 0;
    }

    /**
     * @param int $paymentId
     *
     * @return OrderStorage
     */
    public function setPaymentId(int $paymentId): OrderStorage
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    /**
     * @return int
     */
    public function getDeliveryId(): int
    {
        return $this->deliveryId ?? 0;
    }

    /**
     * @param int $deliveryId
     *
     * @return OrderStorage
     */
    public function setDeliveryId(int $deliveryId): OrderStorage
    {
        $this->deliveryId = $deliveryId;

        return $this;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment ?? '';
    }

    /**
     * @param string $comment
     *
     * @return OrderStorage
     */
    public function setComment(string $comment): OrderStorage
    {
        $this->comment = trim($comment);

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name ?? '';
    }

    /**
     * @param string $name
     *
     * @return OrderStorage
     */
    public function setName(string $name): OrderStorage
    {
        $this->name = trim($name);

        return $this;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone ?? '';
    }

    /**
     * @param string $phone
     *
     * @return OrderStorage
     */
    public function setPhone(string $phone): OrderStorage
    {
        try {
            $this->phone = PhoneHelper::normalizePhone($phone);
        } catch (WrongPhoneNumberException $e) {
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getAltPhone(): string
    {
        return $this->altPhone ?? '';
    }

    /**
     * @param string $altPhone
     *
     * @return OrderStorage
     */
    public function setAltPhone(string $altPhone): OrderStorage
    {
        try {
            $this->altPhone = PhoneHelper::normalizePhone($altPhone);
        } catch (WrongPhoneNumberException $e) {
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email ?? '';
    }

    /**
     * @param string $email
     *
     * @return OrderStorage
     */
    public function setEmail(string $email): OrderStorage
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return int
     */
    public function getAddressId(): int
    {
        return $this->addressId ?? 0;
    }

    /**
     * @param int $addressId
     *
     * @return OrderStorage
     */
    public function setAddressId(int $addressId): OrderStorage
    {
        $this->addressId = $addressId;

        return $this;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street ?? '';
    }

    /**
     * @param string $street
     *
     * @return OrderStorage
     */
    public function setStreet(string $street): OrderStorage
    {
        $this->street = trim($street);

        return $this;
    }

    /**
     * @return string
     */
    public function getHouse(): string
    {
        return $this->house ?? '';
    }

    /**
     * @param string $house
     *
     * @return OrderStorage
     */
    public function setHouse(string $house): OrderStorage
    {
        $this->house = trim($house);

        return $this;
    }

    /**
     * @return string
     */
    public function getBuilding(): string
    {
        return $this->building ?? '';
    }

    /**
     * @param string $building
     *
     * @return OrderStorage
     */
    public function setBuilding(string $building): OrderStorage
    {
        $this->building = trim($building);

        return $this;
    }

    /**
     * @return string
     */
    public function getApartment(): string
    {
        return $this->apartment ?? '';
    }

    /**
     * @param string $apartment
     *
     * @return OrderStorage
     */
    public function setApartment(string $apartment): OrderStorage
    {
        $this->apartment = trim($apartment);

        return $this;
    }

    /**
     * @return string
     */
    public function getPorch(): string
    {
        return $this->porch ?? '';
    }

    /**
     * @param string $porch
     *
     * @return OrderStorage
     */
    public function setPorch(string $porch): OrderStorage
    {
        $this->porch = trim($porch);

        return $this;
    }

    /**
     * @return string
     */
    public function getFloor(): string
    {
        return $this->floor ?? '';
    }

    /**
     * @param string $floor
     *
     * @return OrderStorage
     */
    public function setFloor(string $floor): OrderStorage
    {
        $this->floor = trim($floor);

        return $this;
    }

    /**
     * @return int
     */
    public function getDeliveryDate(): int
    {
        return $this->deliveryDate ?? 0;
    }

    /**
     * @param int $deliveryDate
     *
     * @return OrderStorage
     */
    public function setDeliveryDate(int $deliveryDate): OrderStorage
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }

    /**
     * @return int
     */
    public function getDeliveryInterval(): int
    {
        return $this->deliveryInterval ?? 0;
    }

    /**
     * @param int $deliveryInterval
     *
     * @return OrderStorage
     */
    public function setDeliveryInterval(int $deliveryInterval): OrderStorage
    {
        $this->deliveryInterval = $deliveryInterval;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSplit(): bool
    {
        return $this->split;
    }

    /**
     * @param bool $split
     * @return OrderStorage
     */
    public function setSplit(bool $split): OrderStorage
    {
        $this->split = $split;
        return $this;
    }

    /**
     * @return int
     */
    public function getSecondDeliveryDate(): int
    {
        return $this->secondDeliveryDate;
    }

    /**
     * @param int $secondDeliveryDate
     * @return OrderStorage
     */
    public function setSecondDeliveryDate(int $secondDeliveryDate): OrderStorage
    {
        $this->secondDeliveryDate = $secondDeliveryDate;
        return $this;
    }

    /**
     * @return int
     */
    public function getSecondDeliveryInterval(): int
    {
        return $this->secondDeliveryInterval;
    }

    /**
     * @param int $secondDeliveryInterval
     * @return OrderStorage
     */
    public function setSecondDeliveryInterval(int $secondDeliveryInterval): OrderStorage
    {
        $this->secondDeliveryInterval = $secondDeliveryInterval;
        return $this;
    }

    /**
     * @return string
     */
    public function getSecondComment(): string
    {
        return $this->secondComment;
    }

    /**
     * @param string $secondComment
     * @return OrderStorage
     */
    public function setSecondComment(string $secondComment): OrderStorage
    {
        $this->secondComment = $secondComment;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryPlaceCode(): string
    {
        return $this->deliveryPlaceCode ?? '';
    }

    /**
     * @param string $deliveryPlaceCode
     *
     * @return OrderStorage
     */
    public function setDeliveryPlaceCode(string $deliveryPlaceCode): OrderStorage
    {
        $this->deliveryPlaceCode = trim($deliveryPlaceCode);

        return $this;
    }

    /**
     * @return string
     */
    public function getCommunicationWay(): string
    {
        return $this->communicationWay ?? '';
    }

    /**
     * @param string $communicationWay
     *
     * @return OrderStorage
     */
    public function setCommunicationWay(string $communicationWay): OrderStorage
    {
        $this->communicationWay = trim($communicationWay);

        return $this;
    }

    /**
     * @return string
     */
    public function getSourceCode(): string
    {
        return $this->sourceCode ?? '';
    }

    /**
     * @param string $sourceCode
     *
     * @return OrderStorage
     */
    public function setSourceCode(string $sourceCode): OrderStorage
    {
        $this->sourceCode = trim($sourceCode);

        return $this;
    }

    /**
     * @return string
     */
    public function getPartnerCode(): string
    {
        return $this->partnerCode ?? '';
    }

    /**
     * @param string $partnerCode
     *
     * @return OrderStorage
     */
    public function setPartnerCode(string $partnerCode): OrderStorage
    {
        $this->partnerCode = trim($partnerCode);

        return $this;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city ?? '';
    }

    /**
     * @param string $city
     *
     * @return OrderStorage
     */
    public function setCity(string $city): OrderStorage
    {
        $this->city = trim($city);

        return $this;
    }

    /**
     * @return string
     */
    public function getCityCode(): string
    {
        return $this->cityCode ?? '';
    }

    /**
     * @param string $cityCode
     *
     * @return OrderStorage
     */
    public function setCityCode(string $cityCode): OrderStorage
    {
        $this->cityCode = trim($cityCode);

        return $this;
    }

    /**
     * @return int
     */
    public function getBonus(): int
    {
        return $this->bonus ?? 0;
    }

    /**
     * @param int $bonus
     *
     * @return OrderStorage
     */
    public function setBonus(int $bonus): OrderStorage
    {
        $this->bonus = $bonus;

        return $this;
    }

    /**
     * @return string
     */
    public function getDiscountCardNumber(): string
    {
        return $this->discountCardNumber;
    }

    /**
     * @param string $discountCardNumber
     *
     * @return OrderStorage
     */
    public function setDiscountCardNumber(string $discountCardNumber): OrderStorage
    {
        $this->discountCardNumber = trim($discountCardNumber);

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCurrentDate(): DateTime
    {
        if (!$this->currentDate) {
            $this->currentDate = new DateTime();
        }

        return $this->currentDate;
    }

    /**
     * @param DateTime $currentDate
     * @return OrderStorage
     */
    public function setCurrentDate(DateTime $currentDate): OrderStorage
    {
        $this->currentDate = $currentDate;
        return $this;
    }

    /**
     * @return bool
     */
    public function isFastOrder(): bool
    {
        return $this->fastOrder ?? false;
    }

    /**
     * @param bool $fastOrder
     *
     * @return OrderStorage
     */
    public function setFastOrder(bool $fastOrder): OrderStorage
    {
        $this->fastOrder = $fastOrder;
        return $this;
    }

    /**
     * @return string
     */
    public function getLng(): string
    {
        return $this->lng;
    }

    /**
     * @param string $lng
     * @return OrderStorage
     */
    public function setLng(string $lng): OrderStorage
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * @return string
     */
    public function getLat(): string
    {
        return $this->lat;
    }

    /**
     * @param string $lat
     * @return OrderStorage
     */
    public function setLat(string $lat): OrderStorage
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSubscribe(): bool
    {
        return $this->subscribe;
    }

    /**
     * @param bool $subscribe
     * @return OrderStorage
     */
    public function setSubscribe(bool $subscribe): OrderStorage
    {
        $this->subscribe = $subscribe;
        return $this;
    }

    /**
     * @return int
     */
    public function getSubscribeId()
    {
        return $this->subscribeId;
    }

    /**
     * @param $subscribeId
     * @return OrderStorage
     */
    public function setSubscribeId($subscribeId): OrderStorage
    {
        $this->subscribeId = $subscribeId;
        return $this;
    }


    /**
     * @return bool
     */
    public function isFromApp(): bool
    {
        return $this->fromApp ?? false;
    }

    /**
     * @param bool $fromApp
     *
     * @return OrderStorage
     */
    public function setFromApp(bool $fromApp): OrderStorage
    {
        $this->fromApp = $fromApp;
        return $this;
    }

    /**
     * @return string
     */
    public function getFromAppDevice(): string
    {
        return $this->fromAppDevice ?? '';
    }

    /**
     * @param string $fromAppDevice
     *
     * @return OrderStorage
     */
    public function setFromAppDevice(string $fromAppDevice): OrderStorage
    {
        $this->fromAppDevice = $fromAppDevice;
        return $this;
    }

    /**
     * @return string
     */
    public function getPromoCode(): string
    {
        return $this->promoCode ?: '';
    }

    /**
     * @param string $promoCode
     * @return OrderStorage
     */
    public function setPromoCode(string $promoCode): OrderStorage
    {
        $this->promoCode = $promoCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getMoscowDistrictCode(): string
    {
        return $this->moscowDistrictCode;
    }

    /**
     * @param string $moscowDistrictCode
     *
     * @return OrderStorage
     */
    public function setMoscowDistrictCode(string $moscowDistrictCode): OrderStorage
    {
        $this->moscowDistrictCode = $moscowDistrictCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getShelter(): string
    {
        return $this->shelter;
    }

    /**
     * @param string $shelter
     *
     * @return OrderStorage
     */
    public function setShelter(string $shelter): OrderStorage
    {
        $this->shelter = $shelter;

        return $this;
    }

    public function updateAddressBySaveAddress(AddressService $addressService, LocationService $locationService, OrderStorageService $orderStorageService)
    {
        $city = (!empty($this->getCity())) ? $this->getCity() : 'Москва';
        $orderStreet = $this->getStreet();
        $orderHouse = $this->getHouse();

        if ($this->addressId) {
            $saveAddress = $addressService->getById($this->addressId);
            $orderStreet = $saveAddress->getStreet();
            $orderHouse = $saveAddress->getHouse();
            $this->setStreet($orderStreet);
            $this->setHouse($orderHouse);

            $this->setBuilding($saveAddress->getHousing())
                ->setFloor($saveAddress->getFloor())
                ->setApartment($saveAddress->getFlat())
                ->setPorch($saveAddress->getEntrance());

            //$this->addressId = 0;
        }

        $strAddress = sprintf('%s, %s, %s', $city, $orderStreet, $orderHouse);

        try {
            $okato = $locationService->getDadataLocationOkato($strAddress);
            $locations = $locationService->findLocationByExtService(LocationService::OKATO_SERVICE_CODE, $okato);

            if (count($locations)) {
                $location = current($locations);
//                if ($location['TYPE_ID'] == 9) {
//                    $this->setCityCode(end($location['PATH'])['CODE']);
//                    $this->setCity(end($location['PATH'])['NAME']);
//                } else {
//                    $this->setCityCode($location['CODE']);
//                }
                $this->setMoscowDistrictCode($location['CODE']);
                $this->setCityCode($location['CODE']);
                $orderStorageService->updateStorage($this, OrderStorageEnum::NOVALIDATE_STEP);
            }
        } catch (\Exception $e) {
        }
    }

    public function updateAddressBySaveAddressByMoscowDistrict(AddressService $addressService, LocationService $locationService)
    {
        $city = (!empty($this->getCity())) ? $this->getCity() : 'Москва';
        $orderStreet = $this->getStreet();
        $orderHouse = $this->getHouse();

        if ($this->addressId) {
            $saveAddress = $addressService->getById($this->addressId);
            $orderStreet = $saveAddress->getStreet();
            $orderHouse = $saveAddress->getHouse();
            $this->setStreet($orderStreet);
            $this->setHouse($orderHouse);
            $this->setBuilding($saveAddress->getHousing())
                ->setFloor($saveAddress->getFloor())
                ->setApartment($saveAddress->getFlat())
                ->setPorch($saveAddress->getEntrance());

            //$this->addressId = 0;

        }

        $strAddress = sprintf('%s, %s, %s', $city, $orderStreet, $orderHouse);

        try {
            $okato = $locationService->getDadataLocationOkato($strAddress);
            $locations = $locationService->findLocationByExtService(LocationService::OKATO_SERVICE_CODE, $okato);

            if (count($locations)) {
                $location = current($locations);
                if ($location['TYPE_ID'] == 9) {
                    $this->setCityCode(end($location['PATH'])['CODE']);
                    $this->setCity('Москва');
                    $this->setMoscowDistrictCode($location['CODE']);
                }
            }
        } catch (\Exception $e) {
        }
    }
}
