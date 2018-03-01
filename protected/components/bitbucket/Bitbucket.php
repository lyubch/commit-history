<?php

class Bitbucket extends CApplicationComponent
{
    /**
     * Bitbucket API base url.
     * @var string
     */
    public $baseUrl;
    /**
     * Bitbucket OAuth url.
     * @var string
     */
    public $authUrl;
    /**
     * Bitbucket client key.
     * @var string
     */
    public $key;
    /**
     * Bitbucket client secret.
     * @var string
     */
    public $secret;
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
     * Access token details.
     * @var array
     */
    private $_accessToken;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->baseUrl === null) {
            throw new CException('Property `baseUrl` can not be blank.');
        }
        if ($this->authUrl === null) {
            throw new CException('Property `authUrl` can not be blank.');
        }
        if ($this->key === null) {
            throw new CException('Property `key` can not be blank.');
        }
        if ($this->secret === null) {
            throw new CException('Property `secret` can not be blank.');
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

        return $this->getCommitsRecursive($url, $dateLimit);
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

    /**
     * Sends recursive request for all pages to get all commits relevant
     * to date limit.
     * @param string $url
     * @param DateTime $dateLimit
     * @return array
     * @throws CException
     */
    private function getCommitsRecursive($url, $dateLimit)
    {
        $ch     = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => array(
                'Authorization: Bearer ' . $this->getAccessToken(),
            ),
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
            $result['values'] = array_merge($result['values'], $this->getCommitsRecursive($result['next'], $dateLimit)['values']);
            unset($result['next']);
        }
        // update total count
        $result['pagelen'] = count($result['values']);

        return $result;
    }

    /**
     * Returns OAuth access token for Bearer authentification.
     * @return string
     */
    private function getAccessToken()
    {
        if ($this->_accessToken === null) {
            $accessToken        = $this->getAuthRequest();
            $this->_accessToken = array(
                'access_token'  => $accessToken['access_token'],
                'refresh_token' => $accessToken['refresh_token'],
                'expires_in'    => time() + intval($accessToken['expires_in']),
            );
        } elseif (time() >= $this->_accessToken['expires_in']) {
            $accessToken        = $this->getAuthRequest($this->_accessToken['refresh_token']);
            $this->_accessToken = array(
                'access_token'  => $accessToken['access_token'],
                'refresh_token' => $accessToken['refresh_token'],
                'expires_in'    => time() + intval($accessToken['expires_in']),
            );
        }

        return $this->_accessToken['access_token'];
    }

    /**
     * Performs OAuth request to get access token details.
     * @param string $refreshToken
     * @return array
     * @throws CException
     */
    private function getAuthRequest($refreshToken = null)
    {
        $data = array(
            CURLOPT_URL            => $this->authUrl,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => array(
                'grant_type' => 'client_credentials',
            ),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => $this->key . ':' . $this->secret,
        );
        if ($refreshToken !== null) {
            $data[CURLOPT_POSTFIELDS] = array(
                'grant_type'    => 'refresh_token',
                'refresh_token' => $refreshToken,
            );
        }

        $ch     = curl_init();
        curl_setopt_array($ch, $data);
        $result = curl_exec($ch);
        if ($result !== false) {
            $result = JSON::decode($result);
        } else {
            trigger_error(curl_error($ch));
        }
        curl_close($ch);

        if (isset($result['error_description'])) {
            throw new CException($result['error_description']);
        }

        return $result;
    }
}
