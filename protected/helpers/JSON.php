<?php

/**
 * JSON helper class file.
 */
class JSON extends CJSON
{
    /**
     * Request data.
     * @var array
     */
    private static $_data;

    /**
     * Returns request data from raw body or query.
     * @param string $key
     * @param mixed $defaultValue
     * @return array
     */
    public static function getRequestData($key = null, $defaultValue = null)
    {
        if (self::$_data === null) {
            if (Yii::app()->getRequest()->getRequestType() === 'GET') {
                $input = $_GET;
            } else {
                $input = self::decode(Yii::app()->request->getRawBody(), true);
                if (!is_array($input)) {
                    $input = array();
                }
            }

            self::$_data = $input;
        }

        if ($key !== null) {
            return isset(self::$_data[$key]) ? self::$_data[$key] : $defaultValue;
        }

        return self::$_data;
    }

    /**
     * Throws response data to the end user and finish script.
     * @param mixed $data
     * @param integer $httpCode
     */
    public static function setResponceData($data = null, $httpCode = HttpCode::OK)
    {
        $data = static::serialize($data);

        header('Content-type: application/json');
        self::http_response_code(isset($data['code']) ? $data['code'] : $httpCode);

        if ($data) {
            echo static::encode($data) . PHP_EOL;
        }

        Yii::app()->end();
    }

    /**
     * Sets http response code for headers.
     * @param int $response_code
     * @throws Exception
     */
    public static function http_response_code($response_code = null)
    {
        // For 4.3.0 <= PHP <= 5.4.0
        if (function_exists('http_response_code')) {
            http_response_code($response_code);
        } elseif ($response_code !== null) {
            $message = HttpCode::getDescription($response_code);
            if ($message) {
                $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
                header($protocol . ' ' . $response_code . ' ' . $message);
            } else {
                throw new Exception(Yii::t(__CLASS__, 'Responce code is not valid.'));
            }
        }
    }

    /**
     * Returns serialized data depending on type.
     * @param mixed $data
     * @return mixed
     */
    public static function serialize($data)
    {
        if ($data instanceof CModel) {
            if (!$data->hasErrors()) {
                $data = static::serializeModel($data);
            } else {
                $data = static::serializeModelErrors($data);
            }
        } elseif ($data instanceof CActiveDataProvider) {
            $data = static::serializeDataProvider($data);
        } elseif ($data instanceof CExceptionEvent) {
            $data = static::serializeException($data);
        } elseif ($data instanceof CErrorEvent) {
            $data = static::serializeError($data);
        }

        return $data;
    }

    /**
     * Returns serialized data provider.
     * @param CActiveDataProvider $dataProvider
     * @return array
     */
    public static function serializeDataProvider($dataProvider)
    {
        $serializedData = array();
        foreach ($dataProvider->getData() as $model) {
            $serializedData[] = static::serializeModel($model);
        }

        return array(
            'items' => $serializedData,
        );
    }

    /**
     * Returns serialized model.
     * @param CModel $model
     * @return array
     */
    public static function serializeModel($model)
    {
        return $model->getAttributes();
    }

    /**
     * Returns serialized model errors.
     * @param CModel $model
     * @param int $httpCode
     * @return array
     */
    public static function serializeModelErrors($model, $httpCode = HttpCode::UNPROCESSABLE_ENTITY)
    {
        return array(
            'name'    => 'ModelError',
            'code'    => $httpCode,
            'message' => HttpCode::getDescription($httpCode),
            'fields'  => $model->getErrors(),
        );
    }

    /**
     * Returns serialized Exception.
     * @param CEvent $event
     * @return array
     */
    public static function serializeException($event)
    {
        $exception = $event->exception;

        $result = array(
            'name'    => get_class($event->exception),
            'code'    => property_exists($exception, 'statusCode') ? $exception->statusCode : $exception->getCode(),
            'message' => $exception->getMessage(),
        );

        if (!$result['code']) {
            $result['code'] = HttpCode::BAD_REQUEST;
        }

        return $result;
    }

    /**
     * Returns serialized Error.
     * @param CEvent $event
     * @return array
     */
    public static function serializeError($event)
    {
        $result = array(
            'name'    => 'Error',
            'code'    => $event->code,
            'message' => $event->message,
            'file'    => $event->file,
            'line'    => $event->line,
        );

        if (!$result['code']) {
            $result['code'] = HttpCode::INTERNAL_SERVER_ERROR;
        }

        return $result;
    }
}