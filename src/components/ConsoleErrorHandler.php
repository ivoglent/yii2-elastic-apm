<?php


namespace ivoglent\yii2\apm\components;


use yii\base\Event;
use yii\base\ExitException;
use yii\console\ErrorHandler;

class ConsoleErrorHandler extends ErrorHandler
{
    use ErrorHandlerTrait;
    const EVENT_ON_ERROR = 'onError';
}