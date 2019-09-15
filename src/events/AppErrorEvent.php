<?php


namespace ivoglent\yii2\apm\events;


use yii\base\Event;

class AppErrorEvent extends Event
{
    public $exception;
}