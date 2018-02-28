<?php

class Bitbucket extends CApplicationComponent
{
    /**
     * Bitbucket API base url.
     * @var string
     */
    public $baseUrl;
    /**
     * Bitbucket user login.
     * @var string
     */
    public $username;
    /**
     * Bitbucket user password.
     * @var string
     */
    public $password;
    /**
     * Bitbucket username (from query).
     * @var string
     */
    private $_username;
    /**
     * Bitbucket repo slug (from query).
     * @var string
     */
    private $_repository;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->baseUrl === null) {
            throw new CException('Property `baseUrl` can not be blank.');
        }
        if ($this->username === null) {
            throw new CException('Property `username` can not be blank.');
        }
        if ($this->password === null) {
            throw new CException('Property `password` can not be blank.');
        }
        if ($this->_username === null || $this->_repository === null) {
            throw new CException('Property `projectUrl` can not be blank.');
        }
    }

    /**
     * Returns `commits` response from Bitbucket API.
     * @param string $branch
     * @return array
     * @throws CException
     */
    public function getCommits($branch = null)
    {
        $url = $this->baseUrl . '/repositories/' . $this->_username . '/' . $this->_repository . '/commits';
        if ($branch) {
            $url .= '/' . $branch;
        }

        $ch     = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL            => $url,
            CURLOPT_USERPWD        => $this->username . ':' . $this->password,
            CURLOPT_RETURNTRANSFER => true,
        ));
        $result = curl_exec($ch);
        if ($result !== false) {
            $result = JSON::decode($result);
        } else {
            trigger_error(curl_error($ch));
        }
        curl_close($ch);

        if (isset($result['type']) && $result['type'] === 'error') {
            throw new CException($result['error']['message']);
        }

        return $result;
    }

    /**
     * Sets project url. Like https://bitbucket.org/<username>/<repo_slug>
     * @param string $projectUrl
     * @throws CException
     */
    public function setProjectUrl($projectUrl)
    {
        if (!preg_match('/.*bitbucket\.org\/([^\/]+)\/([^\/]+).*/i', $projectUrl, $matches)) {
            throw new CException('Property `projectUrl` should match the pattern `https://bitbucket.org/{username}/{repository}`.');
        }

        $this->_username   = $matches[1];
        $this->_repository = $matches[2];
    }
}
