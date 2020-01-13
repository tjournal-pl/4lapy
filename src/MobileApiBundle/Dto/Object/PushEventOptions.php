<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Dto\Object;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class PushEventOptions
{
    /**
     * @var int
     * @Serializer\SerializedName("id")
     * @Serializer\Type("int")
     * @Assert\NotBlank()
     */
    protected $id;
    
    /**
     * @var string
     * @Serializer\SerializedName("title")
     * @Serializer\Type("string")
     * @Serializer\SkipWhenEmpty()
     */
    protected $title = '';

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("type")
     */
    protected $type;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return PushEventOptions
     */
    public function setId(int $id): PushEventOptions
    {
        $this->id = $id;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }
    
    /**
     * @param string $title
     * @return PushEventOptions
     */
    public function setTitle(string $title): PushEventOptions
    {
        $this->title = $title;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return PushEventOptions
     */
    public function setType(string $type): PushEventOptions
    {
        $this->type = $type;
        return $this;
    }
}
