<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\In\DeliverySchedule;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class ManualDayItem
 *
 * @package FourPaws\SapBundle\Dto\In
 * @Serializer\XmlRoot("manualdays")
 */
class ManualDayItem
{
    /**
     * Номер по порядку.
     * Содержит порядковый номер поставки.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("int")
     * @Serializer\SerializedName("num")
     *
     * @var int
     */
    protected $num = 0;

    /**
     * Дата поставки.
     * Содержит дату поставки, формат: ГГГГММДД.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("DateTime<'Ymd'>")
     * @Serializer\SerializedName("dlvdate")
     *
     * @var \DateTime
     */
    protected $date;

    /**
     * Дата заказа.
     * Содержит дату поставки, формат: ГГГГММДД.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\Type("DateTime<'Ymd'>")
     * @Serializer\SerializedName("orddate")
     *
     * @var \DateTime
     */
    protected $orderDate;

    /**
     * @return int
     */
    public function getNum(): int
    {
        return $this->num;
    }

    /**
     * @param int $num
     * @return ManualDayItem
     */
    public function setNum(int $num): ManualDayItem
    {
        $this->num = $num;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     * @return ManualDayItem
     */
    public function setDate(\DateTime $date): ManualDayItem
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getOrderDate(): \DateTime
    {
        return $this->orderDate;
    }

    /**
     * @param \DateTime $orderDate
     */
    public function setOrderDate(\DateTime $orderDate): void
    {
        $this->orderDate = $orderDate;
    }
}
