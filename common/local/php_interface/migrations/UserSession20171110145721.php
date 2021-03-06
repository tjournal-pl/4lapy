<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Application;
use FourPaws\MobileApiBundle\Tables\ApiUserSessionTable;

class UserSession20171110145721 extends SprintMigrationBase
{

    protected $description = 'Create User Session Table for mobile api';

    public function up()
    {
        $tableName = ApiUserSessionTable::getTableName();
        /**
         * Compile from d7 DataManager will return only not null table fields structure
         */
        $tableStructure = <<<SQL
CREATE TABLE `$tableName`(
  `ID`                   INT          NOT NULL AUTO_INCREMENT,
  `DATE_INSERT`          DATETIME     NOT NULL,
  `DATE_UPDATE`          DATETIME     NOT NULL,
  `USER_AGENT`           VARCHAR(255),
  `REMOTE_ADDR`          VARCHAR(255),
  `HTTP_CLIENT_IP`       VARCHAR(255),
  `HTTP_X_FORWARDED_FOR` VARCHAR(255),
  `USER_ID`              INT,
  `FUSER_ID`             INT          NOT NULL,
  `TOKEN`                VARCHAR(255) NOT NULL,
  PRIMARY KEY (`ID`)
)
SQL;

        Application::getConnection()->startTransaction();
        if (Application::getConnection()->isTableExists($tableName)) {
            throw new \RuntimeException(sprintf(
                'Table %s already exists',
                $tableName
            ));
        }
        try {
            Application::getConnection()->query($tableStructure);
            Application::getConnection()->commitTransaction();
        } catch (\Exception $exception) {
            Application::getConnection()->rollbackTransaction();
            throw  new \RuntimeException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function down()
    {
        $tableName = ApiUserSessionTable::getTableName();
        Application::getConnection()->dropTable($tableName);
    }
}
