<?php

class m180228_195655_init extends CDbMigration
{
    public function up()
    {
        $sql = file_get_contents(Yii::getPathOfAlias('application.data') . '/schema.mysql.sql');
        Yii::app()->getDb()->createCommand($sql)->execute();
    }

    public function down()
    {
        echo "m180228_195655_init does not support migration down.\n";
        return false;
    }
}