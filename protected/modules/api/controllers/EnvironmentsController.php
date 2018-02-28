<?php

/**
 * EnvironmentsController - controller for manage environments.
 */
class EnvironmentsController extends ApiController
{
    /**
     * @inheritdoc
     */
    public $modelClass = Environments::class;

    /**
     * @inheritdoc
     */
    public function actionList()
    {
        $dataProvider = new CActiveDataProvider($this->modelClass, array(
            'criteria'   => array(
                'order' => 'id ASC',
            ),
            'pagination' => false,
        ));

        JSON::setResponceData($dataProvider, StatusCode::OK);
    }

    /**
     * @inheritdoc
     */
    public function actionView($id)
    {
        $model = $this->getModel($id);

        JSON::setResponceData($model, StatusCode::OK);
    }

    /**
     * @inheritdoc
     */
    public function actionCreate()
    {
        $model = new $this->modelClass;
        $model->setAttributes(JSON::getRequestData());

        if (!$model->save() && !$model->hasErrors()) {
            throw new CException('Failed to create the object for unknown reason.', StatusCode::INTERNAL_SERVER_ERROR);
        }

        JSON::setResponceData($model, StatusCode::CREATED);
    }

    /**
     * @inheritdoc
     */
    public function actionUpdate($id)
    {
        $model = $this->getModel($id);
        $model->setAttributes(JSON::getRequestData());

        if (!$model->save() && !$model->hasErrors()) {
            throw new CException('Failed to update the object for unknown reason.', StatusCode::INTERNAL_SERVER_ERROR);
        }

        JSON::setResponceData($model, StatusCode::OK);
    }

    /**
     * @inheritdoc
     */
    public function actionDelete($id)
    {
        if (!$this->getModel($id)->delete()) {
            throw new CException('Failed to delete the object for unknown reason.', StatusCode::INTERNAL_SERVER_ERROR);
        }

        JSON::setResponceData(null, StatusCode::NO_CONTENT);
    }
}