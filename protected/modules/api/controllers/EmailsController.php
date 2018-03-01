<?php

/**
 * EmailsController - controller for send emails.
 */
class EmailsController extends ApiController
{
    /**
     * @inheritdoc
     */
    public $defaultAction = 'preview';

    /**
     * @inheritdoc
     */
    public function filters()
    {
        return array(
            'getOnly + preview',
            'postOnly + send',
        );
    }

    /**
     * Preview email without saving.
     */
    public function actionPreview()
    {
        $form = new EmailsForm();
        $form->setAttributes(JSON::getRequestData());
        if (!$form->validate()) {
            JSON::setResponceData($form);
        }

        $ms          = Yii::app()->mailSender;
        $branch      = $form->getBranch();
        $environment = $form->getEnvironment();
        $commits     = $this->getCommits($environment, $branch);
        $html = $ms->getTemplate('commit-history', array(
            'environment' => $environment,
            'commits'     => $commits,
        ));

        JSON::setResponceData(array(
            'html' => $html,
        ), HttpCode::OK);
    }

    /**
     * Sends emails and save history.
     * @throws CException
     */
    public function actionSend()
    {
        $form = new EmailsForm();
        $form->setAttributes(JSON::getRequestData());
        if (!$form->validate()) {
            JSON::setResponceData($form);
        }

        $ms          = Yii::app()->mailSender;
        $branch      = $form->getBranch();
        $environment = $form->getEnvironment();
        $commits     = $this->getCommits($environment, $branch);
        $ms->setTemplate('commit-history', array(
            'environment' => $environment,
            'commits'     => $commits,
        ));

        if ($ms->send($environment->getEmailsList())) {
            // save commits
            foreach ($commits as $types) {
                foreach ($types as $commit) {
                    $commit->save(false);
                }
            }
            // save branch
            $branch->last_loading_date = date('Y/m/d H:i:s');
            $branch->save(false);

            JSON::setResponceData(null, HttpCode::NO_CONTENT);
        } else {
            throw new CException('Failed to send emails for unknown reason.', HttpCode::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Returns list of all new commits.
     * @param Environments $environment
     * @param Branches $branch
     * @return array
     * @throws CException
     */
    protected function getCommits($environment, $branch)
    {
        $commits = Yii::app()->bitbucket->getCommits($branch->name, $branch->getDateLimit());
        $params = Yii::app()->params;
        if (isset($params['parse-commit-message'])) {
            $parsePattern = $params['parse-commit-message']['pattern'];
            $parseMatches = $params['parse-commit-message']['matches'];
        }

        $normalizedCommits = array();
        foreach (array_reverse($commits['values']) as $value) {
            if (isset($parsePattern) && !preg_match($parsePattern, $value['message'], $matches)) {
                continue;
            }
            // replace matches keys
            foreach ($matches as $k => $v) {
                if (!isset($parseMatches[$k])) {
                    continue;
                }
                $matches[$parseMatches[$k]] = $v;
            }

            if (Commits::model()->exists('id=:id AND env_id=:env_id', array(
                ':id'     => $value['hash'],
                ':env_id' => $environment->id,
            ))) {
                continue;
            }

            $commit = new Commits();
            $commit->setAttributes(array(
                'id'          => $value['hash'],
                'description' => $matches['description'],
                'url'         => $value['links']['html']['href'],
                'date'        => $value['date'],
                'type'        => isset($matches['type']) ? Commits::typeId($matches['type']) : Commits::TYPE_CHANGE,
                'task_id'     => $matches['task_id'],
                'env_id'      => $environment->id,
            ));
            if (!$commit->validate()) {
                throw new CException('Failed to build commits - validation error.', HttpCode::INTERNAL_SERVER_ERROR);
            }

            $normalizedCommits[$commit->type][$commit->task_id] = $commit;
        }

        return $normalizedCommits;
    }
}