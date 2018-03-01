<?php

/**
 * This is the model class for table "branches".
 *
 * The followings are the available columns in table 'branches':
 * @property integer $id
 * @property string $name
 * @property string $last_loading_date
 * @property string $env_id
 */
class Branches extends CActiveRecord
{
    const LAST_LOADING_DATE_NOT_EXISTS = '-2 weeks';

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'branches';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name, last_loading_date, env_id', 'required'),
            array('name', 'length', 'max' => 45),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, name, last_loading_date, env_id', 'safe'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'environment' => array(self::BELONGS_TO, 'Environments', 'env_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id'                => 'ID',
            'name'              => 'Name',
            'last_loading_date' => 'Last Loading Date',
            'env_id'            => 'Environment',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('last_loading_date', $this->last_loading_date, true);
        $criteria->compare('env_id', $this->env_id);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Branches the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Returns date limit for commits.
     * @return \DateTime
     */
    public function getDateLimit()
    {
        if ($this->last_loading_date === null) {
            return new DateTime(static::LAST_LOADING_DATE_NOT_EXISTS);
        }

        return new DateTime($this->last_loading_date);
    }
}
