<?php

namespace FourPaws\Catalog\Consumer\Message;

use JMS\Serializer\Annotation\Type;

class CatalogSyncMsg
{
    const ACTION_ADD = 'add';

    const ACTION_UPDATE = 'update';

    const ACTION_DELETE = 'delete';

    const ENTITY_TYPE_BRAND = 'brand';

    const ENTITY_TYPE_PRODUCT = 'product';

    const ENTITY_TYPE_OFFER = 'offer';

    /**
     * @var string
     * @Type("string")
     */
    protected $action = '';

    /**
     * @var string
     * @Type("string")
     */
    protected $entityType = '';

    /**
     * @var int
     * @Type("int")
     */
    protected $entityId = 0;

    public function __construct(string $action, string $entityType, int $entityId)
    {
        $this->action = $action;
        $this->entityType = $entityType;
        $this->entityId = $entityId;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     *
     * @return $this
     */
    public function withAction(string $action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return int
     */
    public function getEntityId(): int
    {
        return $this->entityId;
    }

    /**
     * @param int $entityId
     *
     * @return $this
     */
    public function withEntityId(int $entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntityType(): string
    {
        return $this->entityType;
    }

    /**
     * @param string $entityType
     *
     * @return $this
     */
    public function withEntityType(string $entityType)
    {
        $this->entityType = $entityType;

        return $this;
    }

}
