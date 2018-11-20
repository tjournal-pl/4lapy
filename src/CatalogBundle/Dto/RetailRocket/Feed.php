<?php

namespace FourPaws\CatalogBundle\Dto\RetailRocket;

use DateTime;
use Doctrine\Common\Annotations\Annotation\Required;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Feed
 *
 * @package FourPaws\CatalogBundle\Dto\RetailRocket
 *
 * @Serializer\XmlRoot("yml_catalog")
 */
class Feed
{
    /**
     * @Required()
     * @Serializer\XmlAttribute()
     * @Serializer\Type("DateTime<'Y-m-d H:i'>")
     *
     * @var DateTime
     */
    protected $date;

    /**
     * @Required()
     * @Serializer\Type("FourPaws\CatalogBundle\Dto\RetailRocket\Shop")
     *
     * @var Shop
     */
    protected $shop;

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     *
     * @return Feed
     */
    public function setDate(DateTime $date): Feed
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Shop
     */
    public function getShop(): Shop
    {
        return $this->shop;
    }

    /**
     * @param Shop $shop
     *
     * @return Feed
     */
    public function setShop(Shop $shop): Feed
    {
        $this->shop = $shop;

        return $this;
    }
}