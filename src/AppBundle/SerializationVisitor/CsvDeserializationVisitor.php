<?php

namespace FourPaws\AppBundle\SerializationVisitor;

use JMS\Serializer\AbstractVisitor;
use JMS\Serializer\Accessor\AccessorStrategyInterface;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\NullAwareVisitorInterface;
use phpDocumentor\Reflection\Types\Scalar;

/**
 * Class CsvSerializationVisitor
 * @package FourPaws\AppBundle\Serialization
 */
class CsvDeserializationVisitor extends AbstractVisitor implements NullAwareVisitorInterface
{

    private $result;
    private $navigator;

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @param string  $data
     * @param array   $type
     * @param Context $context
     *
     * @return array
     *
     * @throws NotSupportedException
     */
    public function visitArray($data, array $type, Context $context): array
    {
        $res = [];
        if (!empty($data)) {
            $res = explode('|', $data);
        }
        return $res;
    }

    /**
     * Determine if a value conveys a null value.
     * An example could be an xml element (Dom, SimpleXml, ...) that is tagged with a xsi:nil attribute
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isNull($value): bool
    {
        return $value === '';
    }

    /**
     * @param mixed   $data
     * @param array   $type
     *
     * @param Context $context
     *
     * @return mixed
     */
    public function visitNull($data, array $type, Context $context)
    {
        return null;
    }

    /**
     * @param mixed   $data
     * @param array   $type
     *
     * @param Context $context
     *
     * @return mixed
     */
    public function visitString($data, array $type, Context $context)
    {
        return $data;
    }

    /**
     * @param mixed   $data
     * @param array   $type
     *
     * @param Context $context
     *
     * @return mixed
     */
    public function visitBoolean($data, array $type, Context $context)
    {
        return (int)$data === 1;
    }

    /**
     * @param mixed   $data
     * @param array   $type
     *
     * @param Context $context
     *
     * @return mixed
     */
    public function visitDouble($data, array $type, Context $context)
    {
        return (double)$data;
    }

    /**
     * @param mixed   $data
     * @param array   $type
     *
     * @param Context $context
     *
     * @return mixed
     */
    public function visitInteger($data, array $type, Context $context)
    {
        return (int)$data;
    }

    /**
     * Called before the properties of the object are being visited.
     *
     * @param ClassMetadata $metadata
     * @param mixed         $data
     * @param array         $type
     *
     * @param Context       $context
     *
     * @return void
     */
    public function startVisitingObject(ClassMetadata $metadata, $data, array $type, Context $context): void
    {
        // TODO: Implement startVisitingObject() method.
        /** нихрена не делаем */
    }

    /**
     * @param PropertyMetadata $metadata
     * @param mixed            $data
     *
     * @param Context          $context
     *
     * @return void
     */
    public function visitProperty(PropertyMetadata $metadata, $data, Context $context): void
    {
        $v = $this->accessor->getValue($data, $metadata);

        $v = $this->navigator->accept($v, $metadata->type, $context);
        if ((null === $v && $context->shouldSerializeNull() !== true)
            || (true === $metadata->skipWhenEmpty && ($v instanceof \ArrayObject || is_array($v)) && 0 === count($v))
        ) {
            return;
        }

        $k = $this->namingStrategy->translateName($metadata);

        if ($metadata->inline) {
            if (is_array($v) || ($v instanceof \ArrayObject)) {
                $this->data = array_merge($this->data, (array)$v);
            }
        } else {
            $this->data[$k] = $v;
        }
    }

    /**
     * Called after all properties of the object have been visited.
     *
     * @param ClassMetadata $metadata
     * @param mixed         $data
     * @param array         $type
     *
     * @param Context       $context
     *
     * @return mixed
     */
    public function endVisitingObject(ClassMetadata $metadata, $data, array $type, Context $context)
    {
        // TODO: Implement endVisitingObject() method.
        /** нихрена не делаем */
    }

    /**
     * @deprecated use Context::getNavigator/Context::accept instead
     * @return GraphNavigator
     */
    public function getNavigator(): GraphNavigator
    {
        return $this->navigator;
    }

    /**
     * Called before serialization/deserialization starts.
     *
     * @param GraphNavigator $navigator
     *
     * @return void
     */
    public function setNavigator(GraphNavigator $navigator): void
    {
        $this->navigator = $navigator;
    }

    /**
     * @return object|array|scalar
     */
    public function getResult()
    {
        return $this->result;
    }
}
