<?php
/**
 * MailSender class file.
 */
class MailSender extends CApplicationComponent
{
    public $templates = array();
    private $_viewPath;
    private $_template = array(
        'subject' => null,
        'message' => null,
    );

    /**
     * Sender main function who sends each time where MailSender send.
     * Override this method if you are use other mail sender.
     * @param string $to - email
     * @param string $subject - theme
     * @param string $message - body
     */
    private function sender($to, $subject, $body)
    {
        $mailer  = Yii::app()->mailer;
        $message = $mailer->createMessage($subject, $body, 'text/html');
        $message->setTo($to);

        try {
            $mailer->send($message);
        } catch (Throwable $t) {
            return false;
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function setViewPath($path)
    {
        if (($pathOfAlias = Yii::getPathOfAlias($path)) !== false && file_exists($pathOfAlias)
        ) {
            $this->_viewPath = $pathOfAlias;
        } else {
            die(strtr('View path `{path}` are wrong.', array(
                '{path}' => $path)));
        }
    }

    public function getViewPath()
    {
        if ($this->_viewPath == null) {
            $this->_viewPath = dirname(__FILE__) . '/templates';
        }

        return $this->_viewPath;
    }

    /**
     * Send template emails emails.
     * @param mixed $emails email
     */
    public function send($email = array())
    {
        $subject      = $this->_template['subject'];
        $message      = $this->_template['message'];
        $isSuccessful = $this->sender($email, $subject, $message);

        return $isSuccessful;
    }

    public function getTemplate($view, $params = array())
    {
        $templateFile = $this->getViewPath() . '/' . $view . '.php';

        if (!file_exists($templateFile)) {
            die(strtr('Template view `{view}` das not exists.', array(
                '{view}' => $view)));
        }

        if (is_array($params)) {
            extract($params, EXTR_PREFIX_SAME, 'data');
        }

        ob_start();
        require $templateFile;
        return ob_get_clean();
    }

    public function setTemplate($view, $params = array())
    {
        if (isset($this->templates[$view])) {
            $this->_template['subject'] = $this->templates[$view]['subject'];
            $this->_template['message'] = $this->getTemplate($view, $params);
        } else {
            die(strtr('Template view `{view}` can not be found.', array(
                '{view}' => $view)));
        }
    }
}