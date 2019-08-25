<?php


namespace ivoglent\yii2\apm\events;


use yii\base\Event;

class WorkerEvent extends Event
{
    const EVENT_WORKER_START    =   'worker_start';
    const EVENT_WORKER_END    =   'worker_end';
}