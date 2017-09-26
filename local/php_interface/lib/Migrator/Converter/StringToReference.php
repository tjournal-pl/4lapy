<?php

namespace FourPaws\Migrator\Converter;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Highloadblock\DataManager;

/**
 * Class StringToReference
 *
 * Конвертация строковых значений в значения из справочника. Сравнение по $fieldToSearch (UF_NAME по умолчанию).
 * В случае, если значений не найдено, значения добавляются в справочник.
 *
 * @package FourPaws\Migrator\Converter
 */
final class StringToReference extends AbstractConverter
{
    const FIELD_EXTERNAL_KEY = 'UF_XML_ID';
    
    private $referenceCode;
    
    private $fieldToSearch;
    
    /**
     * @return string
     */
    public function getFieldToSearch() : string
    {
        return $this->fieldToSearch;
    }
    
    /**
     * @param string $fieldToSearch
     */
    public function setFieldToSearch(string $fieldToSearch = 'UF_NAME')
    {
        $this->fieldToSearch = $fieldToSearch;
    }
    
    /**
     * @var DataManager
     */
    private $dataClass;
    
    /**
     * @return DataManager
     */
    private function getDataClass() : DataManager
    {
        return $this->dataClass;
    }
    
    /**
     * @throws \Exception
     */
    private function setDataClass()
    {
        $table = HighloadBlockTable::getList(['filter' => ['=NAME' => $this->getReferenceCode()]])->fetch();
        
        if (!$table) {
            /**
             * @todo придумать сюда нормальный Exception
             */
            throw new \Exception('Highloadblock with name ' . $this->getReferenceCode() . ' is not found.');
        }
        
        $dataClass = HighloadBlockTable::compileEntity($table)->getDataClass();
        
        if (is_string($dataClass)) {
            $dataClass = new $dataClass();
        }
        
        $this->dataClass = $dataClass;
    }
    
    private static $referenceValues = [];
    
    /**
     * @return string
     */
    public function getReferenceCode() : string
    {
        return $this->referenceCode;
    }
    
    /**
     * @param string $referenceCode
     */
    public function setReferenceCode(string $referenceCode)
    {
        $this->referenceCode = $referenceCode;
    }
    
    /**
     * @param array $data
     *
     * @return array
     */
    public function convert(array $data) : array
    {
        $this->setDataClass();
        
        $isArray   = true;
        $fieldName = $this->getFieldName();

        if (!$data[$fieldName]) {
            return $data;
        }
        
        $fieldToSearch = $this->getFieldToSearch();
        
        if (!is_array($data[$fieldName])) {
            $isArray          = false;
            $data[$fieldName] = [$data[$fieldName]];
        }
    
        $result = [];

        foreach ($data[$fieldName] as $value) {
            $r = $this->searchValue($value, $fieldToSearch);

            if (!$r) {
                $r = $this->addValue($value, $fieldToSearch);
            }

            $result[] = $r;
        }

        $data[$fieldName] = $isArray ? $result : array_shift($result);
        
        return $data;
    }
    
    /**
     * @param $value
     * @param $fieldName
     *
     * @return string
     * @throws \Exception
     */
    public function addValue($value, $fieldName) : string
    {
        $externalKey = md5($value);
        
        $result = $this->getDataClass()::add([
                                                 $fieldName               => $value,
                                                 self::FIELD_EXTERNAL_KEY => $externalKey,
                                             ]);
        
        if (!$result->isSuccess()) {
            /**
             * @todo придумать сюда нормальный Exception
             */
            throw new \Exception('Reference value add error: ' . implode(', ', $result->getErrorMessages()));
        }
        
        return $externalKey;
    }
    
    /**
     * @param $value
     * @param $fieldToSearch
     *
     * @return mixed
     */
    protected function searchValue($value, $fieldToSearch) : string
    {
        $referenceValues = $this->getReferenceValues();
        $position = array_search($value,
                                 array_column($referenceValues, $fieldToSearch),
                                 true);

        return $position === false ? '' : $referenceValues[$position][self::FIELD_EXTERNAL_KEY];
    }
    
    /**
     * @return array
     *
     * @throws \Exception
     */
    private function getReferenceValues() : array
    {
        if (!self::$referenceValues) {
            
            
            self::$referenceValues = $this->getDataClass()::getList()->fetchAll();
        }
        
        return self::$referenceValues;
    }
    
    /**
     * StringToReference constructor.
     *
     * @param string $fieldName
     *
     * @throws \Bitrix\Main\LoaderException
     */
    public function __construct(string $fieldName)
    {
        if (!Loader::includeModule('highloadblock')) {
            throw new LoaderException('Module highloadblock must be installed');
        }
        
        $this->setFieldToSearch();
        
        parent::__construct($fieldName);
    }
}