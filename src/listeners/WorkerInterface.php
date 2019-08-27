<?php


namespace ivoglent\yii2\apm\listeners;


interface WorkerInterface
{
    const EVENT_BEFORE_EXECUTE  = 'before_execute';
    const EVENT_AFTER_EXECUTE  = 'after_execute';

    public function getName(): string ;
    public function getResult(): string ;
}