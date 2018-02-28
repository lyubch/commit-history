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

        $ms = Yii::app()->mailSender;
        $html = $ms->getTemplate('commit-history', array(
            'environment' => $form->getEnvironment(),
            'commits'     => $this->getCommits($form->branch),
        ));

        JSON::setResponceData(array(
            'html' => $html,
        ), StatusCode::OK);
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

        $ms      = Yii::app()->mailSender;
        $commits = $this->getCommits($form->branch);
        $ms->setTemplate('commit-history', array(
            'environment' => $form->getEnvironment(),
            'commits'     => $commits,
        ));

        if ($ms->send($form->getEnvironment()->getEmailsList())) {
            // save commits
            foreach ($commits as $types) {
                foreach ($types as $commit) {
                    $commit->save(false);
                }
            }
            JSON::setResponceData(null, StatusCode::NO_CONTENT);
        } else {
            throw new CException('Failed to send emails for unknown reason.', StatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Returns list of all new commits.
     * @param string $branch
     * @return array
     * @throws CException
     */
    protected function getCommits($branch = null)
    {
        $commits = Yii::app()->bitbucket->getCommits($branch);
        // set all dates as DateTime object.
        foreach ($commits['values'] as &$value) {
            $value['date'] = new DateTime($value['date']);
        }
        // If it’s first time loading and the last loading date isn’t
        // specified we need to get commits from two weeks ago.
        if (!Commits::model()->count()) {
            $limit = new DateTime('-2 weeks');
            foreach ($commits['values'] as $key => $value) {
                if ($value['date'] < $limit) {
                    unset($commits['values'][$key]);
                }
            }
        }

        $normalizedCommits = array();
        foreach (array_reverse($commits['values']) as $value) {
            if (!preg_match('/\s*(F|C|D)#(\d+)\s*-\s*(.+)/', $value['message'], $matches)) {
                continue;
            }

            if (Commits::model()->exists('id=:id', array(
                    ':id' => $value['hash'],
                ))) {
                continue;
            }

            $commit = new Commits();
            $commit->setAttributes(array(
                'id'          => $value['hash'],
                'description' => $matches[3],
                'url'         => $value['links']['html']['href'],
                'date'        => $value['date'],
                'type'        => Commits::typeId($matches[1]),
                'task_id'     => $matches[2],
            ));
            if (!$commit->validate()) {
                throw new CException('Failed to build commits - validation error.', StatusCode::INTERNAL_SERVER_ERROR);
            }

            $normalizedCommits[$commit->type][$commit->task_id] = $commit;
        }

        return $normalizedCommits;
    }
}