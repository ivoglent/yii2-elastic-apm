<?php


namespace ivoglent\yii2\apm\components;


use yii\base\Event;
use yii\base\ExitException;

trait ErrorHandlerTrait
{
    public $errorException;
    /**
     * @param $exception
     */
    public function handleException($exception)
    {
        if ($exception instanceof ExitException) {
            return;
        }
        $this->errorException = $exception;
        Event::trigger($this, self::EVENT_ON_ERROR);
        return parent::handleException($exception);
    }
}