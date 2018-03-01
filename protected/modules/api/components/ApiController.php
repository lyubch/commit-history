<?php

/**
 * ApiController - base controller for api.
 */
class ApiController extends CController
{
    /**
     * @inheritdoc
     */
    public $defaultAction = 'list';
    /**
     * Model class for CRUD.
     * @var string
     */
    public $modelClass;

    /**
     * @inheritdoc
     */
    public function filters()
    {
        return array(
            'getOnly + list, view',
            'postOnly + create',
            'putOnly + update',
            'deleteOnly + delete',
        );
    }

    /**
     * List all models action.
     */
    public function actionList()
    {
        throw new CException(Yii::t('yii', 'Your request is invalid.'));
    }

    /**
     * View model action.
     * @param int $id
     */
    public function actionView($id)
    {
        throw new CException(Yii::t('yii', 'Your request is invalid.'));
    }

    /**
     * Create model action.
     * @throws CException
     */
    public function actionCreate()
    {
        throw new CException(Yii::t('yii', 'Your request is invalid.'));
    }

    /**
     * Update model action.
     * @param int $id
     * @throws CException
     */
    public function actionUpdate($id)
    {
        throw new CException(Yii::t('yii', 'Your request is invalid.'));
    }

    /**
     * Delete model action.
     * @param int $id
     * @throws CException
     */
    public function actionDelete($id)
    {
        throw new CException(Yii::t('yii', 'Your request is invalid.'));
    }

    /**
     * The filter method for 'getOnly' filter.
     * @param CFilterChain $filterChain
     * @throws CException
     */
    public function filterGetOnly($filterChain)
    {
        if ($this->getIsGetRequest()) {
            $filterChain->run();
        } else {
            throw new CException(Yii::t('yii', 'Your request is invalid.'));
        }
    }

    /**
     * The filter method for 'putOnly' filter.
     * @param CFilterChain $filterChain
     * @throws CException
     */
    public function filterPutOnly($filterChain)
    {
        if (Yii::app()->getRequest()->getIsPutRequest()) {
            $filterChain->run();
        } else {
            throw new CException(Yii::t('yii', 'Your request is invalid.'));
        }
    }

    /**
     * The filter method for 'deleteOnly' filter.
     * @param CFilterChain $filterChain
     * @throws CException
     */
    public function filterDeleteOnly($filterChain)
    {
        if (Yii::app()->getRequest()->getIsDeleteRequest()) {
            $filterChain->run();
        } else {
            throw new CException(Yii::t('yii', 'Your request is invalid.'));
        }
    }

    /**
     * Returns whether this is a GET request.
     * @return bool
     */
    public function getIsGetRequest()
    {
        return isset($_SERVER['REQUEST_METHOD']) && !strcasecmp($_SERVER['REQUEST_METHOD'], 'GET');
    }

    /**
     * Returns model by id.
     * @param mixed $id
     * @return CActiveRecord
     * @throws CException
     */
    protected function getModel($id)
    {
        $modelClass = $this->modelClass;
        if ($modelClass === null) {
            throw new CException('Property `modelClass` can not be empty.', HttpCode::INTERNAL_SERVER_ERROR);
        }

        $model = $modelClass::model()->findByPk($id);
        if ($model === null) {
            throw new CException('Page not found.', HttpCode::NOT_FOUND);
        }

        return $model;
    }
}