<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;

class ReferenceCodes20181701152918 extends SprintMigrationBase
{
    
    protected $description = 'Add codes to references (colour, country)';
    
    public function getValues() {
        return [
            'Colour' => '',
            'Country' => '',
        ];
    }
    
    /**
     * @return bool|void
     * @throws \Exception
     */
    public function up() {
        $result = $this->getValues();
        
        foreach ($result as $hlBlock => $values) {
            $this->addUf($hlBlock);
        }
    }
    
    /**
     * @param string $hlBlockCode
     */
    public function addUf(string $hlBlockCode) {
        $helper = new HelperManager();
        
        $hlblockId = $helper->Hlblock()->getHlblockId($hlBlockCode);
        $entityId  = 'HLBLOCK_' . $hlblockId;
        
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_CODE', [
            'FIELD_NAME'        => 'UF_CODE',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => '',
            'SORT'              => '950',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          => [
                'SIZE'          => 20,
                'ROWS'          => 1,
                'REGEXP'        => null,
                'MIN_LENGTH'    => 0,
                'MAX_LENGTH'    => 0,
                'DEFAULT_VALUE' => null,
            ],
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Символьный код',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Символьный код',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Символьный код',
            ],
            'ERROR_MESSAGE'     => [
                'ru' => null,
            ],
            'HELP_MESSAGE'      => [
                'ru' => null,
            ],
        ]);
    }
    
    public function down() {
        $helper = new HelperManager();
        
        //your code ...
        
    }
    
}
