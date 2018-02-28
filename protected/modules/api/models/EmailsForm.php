<?php

/**
 * EmailsForm - form for preview, send emails actions.
 */
class EmailsForm extends CFormModel
{
    /**
     * Environment name.
     * @var string
     */
    public $env;
    /**
     * Branch name.
     * @var string
     */
    public $branch;
    /**
     * Environment related model.
     * @var Environments
     */
    private $_environment;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array(
            array('env', 'required'),
            array('env', 'exist', 'attributeName' => 'name', 'className' => 'Environments'),
            array('env, branch', 'safe'),
        );
    }

    /**
     * Returns environment related model.
     * @return Environments
     */
    public function getEnvironment()
    {
        if ($this->_environment === null) {
            $this->_environment = Environments::model()->find('name=:name', array(
                ':name' => $this->env,
            ));
        }

        return $this->_environment;
    }
}
