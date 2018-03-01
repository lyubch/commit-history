<?php

/**
 * This is the model class for table "environments".
 *
 * The followings are the available columns in table 'environments':
 * @property integer $id
 * @property string $name
 * @property string $server_url
 *
 * The followings are the available model relations:
 * @property Emails[] $emailsList
 * @property Commits[] $commitsList
 */
class Environments extends CActiveRecord
{
    /**
     * Emails for save.
     * @var array
     */
    public $emails;

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'environments';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name, server_url', 'required'),
            array('name', 'unique'),
            array('name', 'length', 'max' => 45),
            array('server_url', 'length', 'max' => 100),
            array('server_url', 'url'),
            array('emails', 'type', 'type' => 'array'),
            array('emails', 'validateEmails'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, name, server_url, emails', 'safe'),
        );
    }

    /**
     * Validates list of emails to be a valid email.
     * @param string $attribute
     * @param array $params
     */
    public function validateEmails($attribute, $params)
    {
        if (!is_array($this->$attribute)) {
            return;
        }
        
        foreach ($this->$attribute as $key => $value) {
            if (!preg_match('/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/', $value)) {
                $this->addError($attribute, Yii::t('yii', "{$attribute}[{$key}] is not a valid email address."));
            }
        }
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'emailsList'   => array(self::HAS_MANY, 'Emails', 'env_id'),
            'commitsList'  => array(self::HAS_MANY, 'Commits', 'env_id'),
            'branchesList' => array(self::HAS_MANY, 'Branches', 'env_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id'         => 'ID',
            'name'       => 'Name',
            'server_url' => 'Server Url',
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
        $criteria->compare('server_url', $this->server_url, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Environments the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @inheritdoc
     */
    public function getAttributes($names = true)
    {
        return array_merge(parent::getAttributes($names), array(
            'emails' => $this->getEmailsList(true),
        ));
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();
        
        $this->emails = $this->getEmailsList();
    }
    
    /**
     * @inheritdoc
     */
    public function afterSave()
    {
        parent::afterSave();

        if (!$this->getIsNewRecord()) {
            Emails::model()->deleteAll('env_id=:env_id', array(
                ':env_id' => $this->id,
            ));
        }

        if (is_array($this->emails)) {
            foreach ($this->emails as $mail) {
                $email         = new Emails();
                $email->email  = $mail;
                $email->env_id = $this->id;
                $email->save(false);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();

        Emails::model()->deleteAll('env_id=:env_id', array(
            ':env_id' => $this->id,
        ));
        Commits::model()->deleteAll('env_id=:env_id', array(
            ':env_id' => $this->id,
        ));
        Branches::model()->deleteAll('env_id=:env_id', array(
            ':env_id' => $this->id,
        ));
    }

    /**
     * Returns related list of emails.
     * @param bool $refresh
     * @return array
     */
    public function getEmailsList($refresh = false)
    {
        return array_map(function($email) {
            return $email->email;
        }, $this->getRelated('emailsList', $refresh));
    }
}
