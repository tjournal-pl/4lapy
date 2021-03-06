<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\Entity;

use DateTime;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\StoreBundle\Collection\DeliveryScheduleCollection;
use FourPaws\StoreBundle\Collection\OrderDayCollection;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Service\DeliveryScheduleService;
use FourPaws\StoreBundle\Service\StoreService;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DeliverySchedule
 *
 * @package FourPaws\StoreBundle\Entity
 */
class DeliverySchedule extends Base implements \Serializable
{
    /**
     * Еженедельный
     */
    public const TYPE_WEEKLY = '1';

    /**
     * По определенным неделям
     */
    public const TYPE_BY_WEEK = '2';

    /**
     * Ручной
     */
    public const TYPE_MANUAL = '8';

    /**
     * @var int
     *
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("ID")
     * @Serializer\Groups(groups={"read","update","delete"})
     *
     * @Assert\NotBlank(groups={"read","update","delete"})
     * @Assert\GreaterThanOrEqual(value="1",groups={"read","update","delete"})
     * @Assert\Blank(groups={"create"})
     */
    protected $id;

    /**
     * @var string
     * @Serializer\SerializedName("UF_TPZ_NAME")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $name = '';

    /**
     * @var string
     * @Serializer\SerializedName("UF_TPZ_XML_ID")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $xmlId = '';

    /**
     * @var string
     * @Serializer\SerializedName("UF_TPZ_SENDER")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $senderCode = '';

    /**
     * @var string
     * @Serializer\SerializedName("UF_TPZ_RECEIVER")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $receiverCode = '';

    /**
     * @var DateTime
     * @Serializer\SerializedName("UF_TPZ_ACTIVE_FROM")
     * @Serializer\Type("DateTime<'d.m.Y H:i:s'>")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $activeFrom;

    /**
     * @var DateTime
     * @Serializer\SerializedName("UF_TPZ_ACTIVE_TO")
     * @Serializer\Type("DateTime<'d.m.Y H:i:s'>")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $activeTo;

    /**
     * Номер недели
     *
     * @var int[]
     * @Serializer\SerializedName("UF_TPZ_WEEK_NUMBER")
     * @Serializer\Type("array_or_false<int>")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $weekNumbers;

    /**
     * Дни формирования заказа
     *
     * @var int[]
     * @Serializer\SerializedName("UF_TPZ_DAY_OF_WEEK")
     * @Serializer\Type("array_or_false<int>")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $orderDays;

    /**
     * Дни поставки
     *
     * @var int[]
     * @Serializer\SerializedName("UF_TPZ_SUPPLY_DAYS")
     * @Serializer\Type("array_or_false<int>")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $supplyDays;

    /**
     * Номер поставки
     *
     * @var string
     * @Serializer\SerializedName("UF_TPZ_DELIVERY_NBR")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $deliveryNumber;

    /**
     * @var DateTime[]
     * @Serializer\SerializedName("UF_TPZ_ORDER_DATE")
     * @Serializer\Type("array_or_false<DateTime<'d.m.Y'>>")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $orderDates;

    /**
     * @var DateTime[]
     * @Serializer\SerializedName("UF_TPZ_DELIVERY_DATE")
     * @Serializer\Type("array_or_false<DateTime<'d.m.Y'>>")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $deliveryDates;

    /**
     * @var string
     * @Serializer\SerializedName("UF_TPZ_TYPE")
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $type = '';

    /**
     * @var string
     * @Serializer\SerializedName("UF_DATE_UPDATE")
     * @Serializer\Type("DateTime<'d.m.Y H:i:s'>")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $dateUpdate;

    /**
     * @var int
     * @Serializer\SerializedName("UF_REGULARITY")
     * @Serializer\Type("int")
     * @Serializer\Groups(groups={"create","read","update","delete"})
     */
    protected $regular;

    /**
     * @var Store
     */
    protected $sender;

    /**
     * @var Store
     */
    protected $receiver;

    /**
     * @var DeliveryScheduleCollection
     */
    protected $receiverSchedules;

    /**
     * @var DeliveryScheduleCollection
     */
    protected $senderSchedules;


    /**
     * @var DeliveryScheduleService
     */
    protected $scheduleService;

    /**
     * @var StoreService
     */
    protected $storeService;

    /**
     * DeliverySchedule constructor.
     * @throws ApplicationCreateException
     */
    public function __construct()
    {
        $this->storeService = Application::getInstance()->getContainer()->get('store.service');
        $this->scheduleService = Application::getInstance()->getContainer()->get(DeliveryScheduleService::class);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return DeliverySchedule
     */
    public function setId(int $id): DeliverySchedule
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return DeliverySchedule
     */
    public function setName(string $name): DeliverySchedule
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getSenderCode(): string
    {
        return $this->senderCode;
    }

    /**
     * @param string $senderCode
     *
     * @return DeliverySchedule
     */
    public function setSenderCode(string $senderCode): DeliverySchedule
    {
        $this->senderCode = $senderCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getReceiverCode(): string
    {
        return $this->receiverCode;
    }

    /**
     * @param string $receiverCode
     *
     * @return DeliverySchedule
     */
    public function setReceiverCode(string $receiverCode): DeliverySchedule
    {
        $this->receiverCode = $receiverCode;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getActiveFrom(): DateTime
    {
        return $this->activeFrom;
    }

    /**
     * @param DateTime $activeFrom
     *
     * @return DeliverySchedule
     */
    public function setActiveFrom(DateTime $activeFrom): DeliverySchedule
    {
        $this->activeFrom = $activeFrom;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getActiveTo(): DateTime
    {
        return $this->activeTo;
    }

    /**
     * @param DateTime $activeTo
     *
     * @return DeliverySchedule
     */
    public function setActiveTo(DateTime $activeTo): DeliverySchedule
    {
        $this->activeTo = $activeTo;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return DeliverySchedule
     */
    public function setType(string $type): DeliverySchedule
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return array
     */
    public function getWeekNumbers(): array
    {
        return $this->weekNumbers;
    }

    /**
     * @param array $weekNumbers
     * @return DeliverySchedule
     */
    public function setWeekNumbers(array $weekNumbers): DeliverySchedule
    {
        $this->weekNumbers = $weekNumbers;
        return $this;
    }

    /**
     * @return array
     */
    public function getOrderDays(): array
    {
        return $this->orderDays ?? [];
    }

    /**
     * @param array $orderDays
     *
     * @return DeliverySchedule
     */
    public function setOrderDays(array $orderDays): DeliverySchedule
    {
        $this->orderDays = $orderDays;

        return $this;
    }

    /**
     * @return int[]
     */
    public function getSupplyDays(): array
    {
        return $this->supplyDays ?? [];
    }

    /**
     * @param int[] $supplyDays
     *
     * @return DeliverySchedule
     */
    public function setSupplyDays(array $supplyDays): DeliverySchedule
    {
        $this->supplyDays = $supplyDays;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryNumber(): string
    {
        return $this->deliveryNumber;
    }

    /**
     * @param string $deliveryNumber
     *
     * @return DeliverySchedule
     */
    public function setDeliveryNumber(array $deliveryNumber): DeliverySchedule
    {
        $this->deliveryNumber = $deliveryNumber;

        return $this;
    }

    /**
     * @return DateTime[]
     */
    public function getDeliveryDates(): array
    {
        return $this->deliveryDates ?? [];
    }

    /**
     * @param DateTime[] $deliveryDates
     *
     * @return DeliverySchedule
     */
    public function setDeliveryDates(array $deliveryDates): DeliverySchedule
    {
        $this->deliveryDates = $deliveryDates;

        return $this;
    }

    /**
     * @noinspection MultipleReturnStatementsInspection
     *
     * @param DateTime $date
     * @return bool
     */
    public function isActiveForDate(DateTime $date): bool
    {
        if ($this->activeFrom && $this->activeFrom > $date) {
            return false;
        }

        if ($this->activeTo && $this->activeTo < $date) {
            return false;
        }

        return true;
    }

    /**
     * @throws NotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @return Store
     */
    public function getReceiver(): Store
    {
        if (null === $this->receiver) {
            $this->setReceiver($this->storeService->getStoreByXmlId($this->getReceiverCode()));
        }
        return $this->receiver;
    }

    /**
     * @param Store $receiver
     * @return DeliverySchedule
     */
    public function setReceiver(Store $receiver): DeliverySchedule
    {
        $this->receiver = $receiver;
        $this->receiverCode = $receiver->getXmlId();
        return $this;
    }

    /**
     * @throws NotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @return Store
     */
    public function getSender(): Store
    {
        if (null === $this->sender) {
            $this->setSender($this->storeService->getStoreByXmlId($this->getSenderCode()));
        }
        return $this->sender;
    }

    /**
     * @param Store $sender
     * @return DeliverySchedule
     */
    public function setSender(Store $sender): DeliverySchedule
    {
        $this->sender = $sender;
        $this->senderCode = $sender->getXmlId();

        return $this;
    }

    /**
     * @return string
     */
    public function getXmlId(): string
    {
        return $this->xmlId;
    }

    /**
     * @param string $xmlId
     *
     * @return DeliverySchedule
     */
    public function setXmlId(string $xmlId): DeliverySchedule
    {
        $this->xmlId = $xmlId;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTypeCode(): ?string
    {
        return $this->scheduleService->getTypeCodeById((int)$this->getType());
    }

    /**
     * @throws \Bitrix\Main\ArgumentException
     * @throws NotFoundException
     * @return DeliveryScheduleCollection
     */
    public function getReceiverSchedules(): DeliveryScheduleCollection
    {
        if (null === $this->receiverSchedules) {
            $this->setReceiverSchedules($this->scheduleService->findBySender($this->getReceiver()));
        }
        return $this->receiverSchedules;
    }

    /**
     * @param DeliveryScheduleCollection $receiverSchedules
     * @return DeliverySchedule
     */
    public function setReceiverSchedules(DeliveryScheduleCollection $receiverSchedules): DeliverySchedule
    {
        $this->receiverSchedules = $receiverSchedules;
        return $this;
    }

    /**
     * @throws NotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @return DeliveryScheduleCollection
     */
    public function getSenderSchedules(): DeliveryScheduleCollection
    {
        if (null === $this->senderSchedules) {
            $this->setSenderSchedules($this->scheduleService->findByReceiver($this->getSender()));
        }

        return $this->senderSchedules;
    }

    /**
     * @param DeliveryScheduleCollection $senderSchedules
     * @return DeliverySchedule
     */
    public function setSenderSchedules(DeliveryScheduleCollection $senderSchedules): DeliverySchedule
    {
        $this->senderSchedules = $senderSchedules;
        return $this;
    }

    /**
     * @return DateTime[]
     */
    public function getOrderDates(): ?array
    {
        return $this->orderDates;
    }

    /**
     * @param DateTime[] $orderDates
     */
    public function setOrderDates(array $orderDates): void
    {
        $this->orderDates = $orderDates;
    }

    /**
     * @param DateTime $from
     * @return null|DateTime
     */
    public function getNextDelivery(DateTime $from = null): ?DateTime
    {
        if (!$from instanceof DateTime) {
            $from = new DateTime();
        }

        if (!$this->isActiveForDate($from)) {
            return null;
        }

        /**
         * Ищем ближайший день для формирования заказа
         * и соответствующий день отгрузки
         *
         * @param DateTime $from дата, от которой считаем
         * @param bool $realDate признак того что дата реальная и надо проверить время для заказа
         * @return null|DateTime
         */
        $getByDay = function (DateTime $from, bool $realDate = true): ?DateTime {
            $fromDay = (int)$from->format('N');
            $fromWeek = (int)$from->format('W');

            $orderDays = $this->scheduleService->getOrderAndSupplyDays($this, $from);

            if(null == $orderDays){
                return null;
            }

            $orderDates = [];

            /** @var OrderDay $day */
            foreach ($orderDays as $key => $day) {
                $date = clone $from;

                $weekDiff = $day->getWeekNum() - $fromWeek;
                $daysDiff = $day->getOrderDay() - $fromDay;
                $weeks = ($weekDiff >= 0) ? "+".$weekDiff : $weekDiff;
                $days = ($daysDiff >= 0) ? "+".$daysDiff : $daysDiff;

                $date->modify(sprintf('%s weeks %s days', $weeks, $days));

                /** Не укладываемся по времени для формирования заказа */
                if($realDate && $from > $day->setOrderTimeForDate($date)){
                    if($this->getTypeCode() == self::TYPE_WEEKLY){
                        $date->modify('+1 week');
                    } else{
                        continue;
                    }
                }

                $orderDates[$key] = $date;
            }

            if (!empty($orderDates)) {
                $orderDate = min($orderDates);
                $orderDayIndex = array_search($orderDate, $orderDates);

                /** @var OrderDayCollection $orderDays */
                $supplyDay = $orderDays[$orderDayIndex]->getSupplyDay();

                $fromDay = (int)$orderDate->format('N');

                $diff = $supplyDay - $fromDay;
                $days = ($diff > 0) ? $diff : $diff + 7;
                $orderDate->modify(sprintf('+%s days', $days));

                return $orderDate;
            }

            return null;
        };

        $result = null;
        $date = clone $from;
        switch ($this->getTypeCode()) {
            case self::TYPE_MANUAL:
                $results = [];
                $date->setTime(0, 0, 0, 0);

                /** @var \DateTime $deliveryDate */
                foreach ($this->deliveryDates as $deliveryDate) {
                    if (!$deliveryDate instanceof DateTime) {
                        continue;
                    }

                    if ($deliveryDate >= $date) {
                        $results[] = $deliveryDate;
                    }
                }

                if (!empty($results)) {
                    $result = min($results);
                }
                break;
            case self::TYPE_BY_WEEK:
                $weekNumbers = $this->getWeekNumbers();
                $weekDates = [];

                foreach ($weekNumbers as $weekNumber) {
                    $weekDate = clone $date;
                    $weekDate->setISODate($date->format('Y'), $weekNumber);

                    if ($weekDate->format('W') < $date->format('W')) {
                        $weekDate->modify('+1 year');
                    }

                    $weekDates[] = ($weekDate > $date) ? $weekDate : $date;
                }

                uasort($weekDates, function($a, $b) {
                   return  $a > $b;
                });

                if(!empty($weekDates)){
                    $realDate = true;
                    $weekDate = current($weekDates);

                    if($weekDate != $date){
                        $realDate = false;
                    }

                    $result = $getByDay($weekDate, $realDate);
                    if(!$result){
                        $realDate = false;
                        $weekDate = next($weekDates);
                        $result = $getByDay($weekDate, $realDate);
                    }
                }
                else{
                    $result = null;
                }

                break;
            case self::TYPE_WEEKLY:
                $result = $getByDay($from);
                break;
        }

        /** Результат за пределами активности графика */
        if ($result && !$this->isActiveForDate($result)) {
            return null;
        }

        return $result;
    }

    /**
     * @throws Exception
     */
    public function setDateUpdate(): void
    {
        $this->dateUpdate = new \DateTime();
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @return string
     */
    public function serialize(): string
    {
        return \serialize([
            $this->id,
            $this->name,
            $this->xmlId,
            $this->senderCode,
            $this->receiverCode,
            $this->activeFrom,
            $this->activeTo,
            $this->weekNumbers,
            $this->orderDays,
            $this->supplyDays,
            $this->deliveryNumber,
            $this->deliveryDates,
            $this->type,
            $this->regular
        ]);
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @param string $serialized
     * @throws ApplicationCreateException
     */
    public function unserialize($serialized): void
    {
        [
            $this->id,
            $this->name,
            $this->xmlId,
            $this->senderCode,
            $this->receiverCode,
            $this->activeFrom,
            $this->activeTo,
            $this->weekNumbers,
            $this->orderDays,
            $this->supplyDays,
            $this->deliveryNumber,
            $this->deliveryDates,
            $this->type,
            $this->regular
        ] = \unserialize($serialized, ['allowed_classes' => true]);

        $this->__construct();
    }

    /**
     * @param int $regular
     * @return DeliverySchedule
     */
    public function setRegular(int $regular): DeliverySchedule
    {
        $this->regular = $regular;
        return $this;
    }

    /**
     * @return int
     */
    public function getRegular(): int
    {
        return $this->regular;
    }
}
