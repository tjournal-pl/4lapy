<?php

namespace FourPaws\Migrator\Entity;

use Bitrix\Main\GroupTable;
use Exception;
use FourPaws\Migrator\Entity\Exceptions\AddException;
use FourPaws\Migrator\Entity\Exceptions\UpdateException;
use FourPaws\Migrator\Exception\MigratorException;

/**
 * Class UserGroup
 *
 * @package FourPaws\Migrator\Entity
 */
class UserGroup extends AbstractEntity
{
    const EXCLUDED_GROUPS = [
        1,
        2,
        6,
    ];
    
    /**
     * Установим маппинг существующих групп по умолчанию
     *
     * EXTERNAL -> INTERNAL
     *
     * @return array
     * @throws MigratorException
     * @throws Exception
     */
    public function setDefaults() : array
    {
        if ($this->checkEntity()) {
            return [];
        }
        
        $map = [
            1 => 8,
            2 => 2,
            6 => 6,
            7 => 6,
        ];
        
        foreach ($map as $key => $item) {
            $result = MapTable::addEntity($this->entity, $key, $item);
            
            if (!$result->isSuccess()) {
                throw new MigratorException(sprintf('Error: %s', implode(', ', $result->getErrorMessages())));
            }
        }
        
        return $map;
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return UpdateResult
     *
     * @throws Exception
     */
    public function updateItem(string $primary, array $data) : UpdateResult
    {
        if (in_array($primary, self::EXCLUDED_GROUPS, false)) {
            return new UpdateResult(true, $primary);
        }
        
        $result = GroupTable::update($primary, $data);
        
        return new UpdateResult($result->isSuccess(), $result->getId());
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return AddResult
     *
     * @throws AddException
     * @throws Exception
     */
    public function addItem(string $primary, array $data) : AddResult
    {
        $result = GroupTable::add($data);
        
        if ($result->isSuccess() && !MapTable::addEntity($this->entity, $primary, $result->getId())->isSuccess()) {
            throw new AddException('Error: add entity was broken');
        }
        
        return new AddResult($result->isSuccess(), $result->getId());
    }
    
    /**
     * @param string $field
     * @param string $primary
     * @param        $value
     *
     * @return UpdateResult
     *
     * @throws UpdateException
     * @throws Exception
     */
    public function setFieldValue(string $field, string $primary, $value) : UpdateResult
    {
        $result = GroupTable::update($primary, [$field => $value]);
        
        if ($result->isSuccess()) {
            return new UpdateResult(true, $result->getId());
        }
        
        $errors = $result->getErrorMessages();
        
        throw new UpdateException(sprintf('Update field with primary %s error: %s', $primary, $errors));
    }
}
