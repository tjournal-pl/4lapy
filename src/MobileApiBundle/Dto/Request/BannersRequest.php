<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Request\Types\GetRequest;
use FourPaws\MobileApiBundle\Dto\Request\Types\SimpleUnserializeRequest;
use JMS\Serializer\Annotation as Serializer;

class BannersRequest implements SimpleUnserializeRequest, GetRequest
{
    /**
     * Код раздела в инфоблоке баннеров
     * @Serializer\Type("string")
     * @Serializer\SerializedName("tag")
     *
     * @var string
     */
    protected $sectionCode;

    /**
     * Код города для проброса параметра в ссылку с баннера
     * @Serializer\Type("string")
     * @Serializer\SerializedName("city_id")
     *
     * @var string
     */
    protected $cityId;

    /**
     * @return string|null
     */
    public function getSectionCode()
    {
        return $this->sectionCode;
    }

    /**
     * @param string $sectionCode
     * @return BannersRequest
     */
    public function setSectionCode($sectionCode): BannersRequest
    {
        $this->sectionCode = $sectionCode;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     * @param string $cityId
     * @return BannersRequest
     */
    public function setCityId($cityId): BannersRequest
    {
        $this->cityId = $cityId;
        return $this;
    }

}
