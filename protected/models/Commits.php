<?php

/**
 * This is the model class for table "commits".
 *
 * The followings are the available columns in table 'commits':
 * @property string $id
 * @property string $description
 * @property string $url
 * @property string $date
 * @property integer $type
 * @property integer $task_id
 * @property integer $env_id
 */
class Commits extends CActiveRecord
{
    /**
     * Types.
     */
    const TYPE_FEATURE = 0;
    const TYPE_DEFECT  = 1;
    const TYPE_CHANGE  = 2;

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'commits';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('id, description, url, date, type, task_id, env_id', 'required'),
            array('id', 'length', 'max' => 60),
            array('type, task_id, env_id', 'numerical', 'integerOnly' => true),
            array('description', 'length', 'max' => 255),
            array('url', 'length', 'max' => 255),
            array('type', 'in', 'range' => array_keys(static::types())),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, description, url, date, type, task_id, env_id', 'safe'),
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
            'id'          => 'ID',
            'description' => 'Description',
            'url'         => 'Url',
            'date'        => 'Date',
            'type'        => 'Type',
            'task_id'     => 'Task',
            'env_id'      => 'Environment',
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

        $criteria->compare('id', $this->id, true);
        $criteria->compare('description', $this->description, true);
        $criteria->compare('url', $this->url, true);
        $criteria->compare('date', $this->date, true);
        $criteria->compare('type', $this->type);
        $criteria->compare('task_id', $this->task_id);
        $criteria->compare('env_id', $this->env_id);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Commits the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave()
    {
        if (!parent::beforeSave()) {
            return false;
        }

        if ($this->date instanceof DateTime) {
            $this->date = $this->date->format('Y/m/d H:i:s');
        }

        return true;
    }

    /**
     * Returns list of types.
     * @return array
     */
    public static function types()
    {
        return array(
            static::TYPE_FEATURE => 'F',
            static::TYPE_DEFECT  => 'D',
            static::TYPE_CHANGE  => 'C',
        );
    }

    /**
     * Returns type id by type.
     * @param string $type
     * @return int
     */
    public static function typeId($type)
    {
        $types = array_flip(static::types());
        return isset($types[$type]) ? $types[$type] : null;
    }
}
