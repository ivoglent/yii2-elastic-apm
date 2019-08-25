<?php


namespace ivoglent\yii2\apm\components;


use yii\base\Event;
use yii\base\ExitException;
use yii\web\ErrorHandler;

class WebErrorHandler extends ErrorHandler
{
    use ErrorHandlerTrait;
    const EVENT_ON_ERROR    =   'onError';
}