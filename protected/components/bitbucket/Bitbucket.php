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
     * @param DateTime $dateLimit
     * @return array
     * @throws CException
     */
    public function getCommits($branch, $dateLimit)
    {
        $url = $this->baseUrl . '/repositories/' . $this->_username . '/' . $this->_repository . '/commits/' . $branch;

        return $this->sendRequest($url, $dateLimit);
    }

    /**
     * Sends recursive request for all pages to get all commits relevant
     * to date limit.
     * @param string $url
     * @param DateTime $dateLimit
     * @return array
     * @throws CException
     */
    private function sendRequest($url, $dateLimit)
    {
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

        // check date is greater than date limit
        $allowContinue = true;
        foreach ($result['values'] as $key => &$value) {
            if ($allowContinue) {
                $value['date'] = new DateTime($value['date']);
                if ($value['date'] < $dateLimit) {
                    $allowContinue = false;
                    unset($result['values'][$key]);
                }
            } else {
                unset($result['values'][$key]);
            }
        }
        // run recursive
        if (isset($result['next']) && $allowContinue) {
            $result['values'] = array_merge($result['values'], $this->sendRequest($result['next'], $dateLimit)['values']);
            unset($result['next']);
        }
        // update total count
        $result['pagelen'] = count($result['values']);

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
