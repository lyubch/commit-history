<?php

class ApiModule extends CWebModule
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->setImport(array(
            'api.components.*',
            'api.models.*',
        ));

        Yii::app()->attachEventHandler('onException', function($event) {
            JSON::setResponceData($event);
        });

        Yii::app()->attachEventHandler('onError', function($event) {
            JSON::setResponceData($event);
        });
    }
}
