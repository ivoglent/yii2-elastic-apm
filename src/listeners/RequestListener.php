<?php


namespace ivoglent\yii2\apm\listeners;


use ivoglent\yii2\apm\Listener;
use yii\base\ActionEvent;
use yii\web\Application;

class RequestListener extends Listener
{
    public function init()
    {
        parent::init();
        \Yii::$app->on(Application::EVENT_BEFORE_ACTION, [$this, 'beforeRequest']);
        \Yii::$app->on(Application::EVENT_AFTER_ACTION, [$this, 'afterRequest']);
    }

    public function beforeRequest(ActionEvent $event) {
        if (!\Yii::$app->request->isOptions) {
            
        }
    }
}