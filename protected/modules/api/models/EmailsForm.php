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
     * Branch related model.
     * @var Branches
     */
    private $_branch;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array(
            array('env, branch', 'required'),
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

    /**
     * Returns branch related model.
     * @return Branches
     */
    public function getBranch()
    {
        if ($this->_branch === null) {
            $environment   = $this->getEnvironment();
            $this->_branch = Branches::model()->find('name=:name AND env_id=:env_id', array(
                ':name'   => $this->branch,
                ':env_id' => $environment->id,
            ));
            if ($this->_branch === null) {
                $this->_branch       = new Branches();
                $this->_branch->name   = $this->branch;
                $this->_branch->env_id = $environment->id;
            }
        }

        return $this->_branch;
    }
}
