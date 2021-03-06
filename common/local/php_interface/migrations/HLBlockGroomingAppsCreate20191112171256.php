<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;
use Sprint\Migration\Helpers\HlblockHelper;
use CUserFieldEnum;

class HLBlockGroomingAppsCreate20191112171256 extends SprintMigrationBase
{
    protected $description = 'Создание таблицы для хранения заявок на груминг';
    
    const HL_BLOCK_NAME = 'GroomingApps';
    
    protected $hlBlockData = [
        'NAME'       => self::HL_BLOCK_NAME,
        'TABLE_NAME' => 'b_hlbd_grooming_apps',
        'LANG'       => [
            'ru' => [
                'NAME' => 'Заявки на груминг',
            ],
        ],
    ];
    
    protected $fields = [
        [
            'FIELD_NAME'        => 'UF_USER_ID',
            'USER_TYPE_ID'      => 'integer',
            'XML_ID'            => 'UF_USER_ID',
            'SORT'              => 10,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'Y',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'ID пользователя',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'ID пользователя',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'ID пользователя',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_NAME',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_NAME',
            'SORT'              => 20,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'Y',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Имя',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Имя',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Имя',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_PHONE',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_PHONE',
            'SORT'              => 30,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'Y',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Имя',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Телефон',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Телефон',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_EVENT_ID',
            'USER_TYPE_ID'      => 'iblock_element',
            'XML_ID'            => '',
            'SORT'              => '100',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'DISPLAY'       => 'LIST',
                    'LIST_HEIGHT'   => 5,
                    'DEFAULT_VALUE' => '',
                    'ACTIVE_FILTER' => 'N',
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'ru' => 'Событие',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Событие',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Событие',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'ru' => '',
                ],
        ],
        [
            'FIELD_NAME'        => 'UF_EMAIL',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_EMAIL',
            'SORT'              => 20,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'Y',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Email',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Email',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Email',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_ANIMAL',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_ANIMAL',
            'SORT'              => 20,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'Y',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Вид животного',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Вид животного',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Вид животного',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_BREED',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_BREED',
            'SORT'              => 20,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'Y',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Порода',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Порода',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Порода',
            ],
        ],
        [
            'FIELD_NAME'        => 'UF_SERVICE',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_SERVICE',
            'SORT'              => 20,
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'Y',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Услуга',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Услуга',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Услуга',
            ],
        ],
    ];
    
    public function up()
    {
        $iblockId = \CIBlock::GetList([], ['CODE' => 'flagman_grooming'])->Fetch()['ID'];
        $this->fields[3]['SETTINGS']['IBLOCK_ID'] = $iblockId;
        
        /** @var HlblockHelper $hlBlockHelper */
        $hlBlockHelper = $this->getHelper()->Hlblock();
        
        /** @var \Sprint\Migration\Helpers\UserTypeEntityHelper $userTypeEntityHelper */
        $userTypeEntityHelper = $this->getHelper()->UserTypeEntity();
        
        if (!$hlBlockId = $hlBlockHelper->getHlblockId(static::HL_BLOCK_NAME)) {
            if ($hlBlockId = $hlBlockHelper->addHlblock($this->hlBlockData)) {
                //$this->log()->info('Добавлен HL-блок ' . static::HL_BLOCK_NAME);
            } else {
                //$this->log()->error('Ошибка при создании HL-блока ' . static::HL_BLOCK_NAME);
                
                return false;
            }
        } else {
            // $this->log()->info('HL-блок ' . static::HL_BLOCK_NAME . ' уже существует');
        }
        
        $entityId = 'HLBLOCK_' . $hlBlockId;
        foreach ($this->fields as $field) {
            if ($fieldId = $userTypeEntityHelper->addUserTypeEntityIfNotExists(
                $entityId,
                $field['FIELD_NAME'],
                $field
            )) {
                // $this->log()->info(
                //     'Добавлено поле ' . $field['FIELD_NAME'] . ' в HL-блок ' . self::HL_BLOCK_NAME
                // );
            } else {
                // $this->log()->error(
                //     'Ошибка при добавлении поля ' . $field['FIELD_NAME'] . ' в HL-блок ' . self::HL_BLOCK_NAME
                // );
                
                return false;
            }
            
            if ($field['ENUMS']) {
                $enum = new CUserFieldEnum();
                if ($enum->SetEnumValues($fieldId, $field['ENUMS'])) {
                    //$this->log()->info('Добавлены значения для поля ' . $field['FIELD_NAME']);
                } else {
                    //$this->log()->error('Не удалось добавить значения для поля ' . $field['FIELD_NAME']);
                }
            }
        }
        
        return true;
    }
    
    public function down()
    {
        /** @var HlblockHelper $hlBlockHelper */
        $hlBlockHelper = $this->getHelper()->Hlblock();
        
        if (!$hlBlockId = $hlBlockHelper->getHlblockId(static::HL_BLOCK_NAME)) {
            // $this->log()->error('HL-блок ' . static::HL_BLOCK_NAME . ' не найден');
            
            return true;
        }
        
        if ($hlBlockHelper->deleteHlblock($hlBlockId)) {
            // $this->log()->info('HL-блок ' . static::HL_BLOCK_NAME . ' удален');
        } else {
            // $this->log()->error('Ошибка при удалении HL-блока ' . static::HL_BLOCK_NAME);
            
            return false;
        }
        
        return true;
    }
}
