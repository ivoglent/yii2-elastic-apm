<?php


namespace ivoglent\yii2\apm\listeners;


use Elastic\Apm\PhpAgent\Model\Context\DbContext;
use Elastic\Apm\PhpAgent\Model\Span;
use ivoglent\yii2\apm\Listener;
use yii\base\ActionEvent;
use yii\base\Event;
use yii\web\Application;
use yii\web\Controller;

class RequestListener extends Listener
{
    public function init()
    {
        parent::init();
        \Yii::$app->on(Application::EVENT_BEFORE_REQUEST, [$this, 'beforeRequest']);
        \Yii::$app->on(Application::EVENT_AFTER_REQUEST, [$this, 'afterRequest']);
        Event::on(Controller::class, Controller::EVENT_BEFORE_ACTION, [$this, 'beforeAction']);
    }

    public function beforeRequest(Event $event) {
        /** @var Application $sender */
        $sender = $event->sender;
        \Yii::info('Request start', 'apm');
        if (!\Yii::$app->request->isOptions) {
            $txtName = \Yii::$app->request->url;
            $this->agent->startTransaction($txtName, 'http');
        }
    }

    public function afterRequest(Event $event) {
        \Yii::info('Request stop', 'apm');
        /** @var Application $sender */
        $sender = $event->sender;
        if (!\Yii::$app->request->isOptions) {
            $result = (string) $sender->response->getStatusCode();
            $this->agent->getTransaction()->setResult($result);
        }
    }

    public function beforeAction(ActionEvent $event) {
        \Yii::info('Action start', 'apm');
        if (!\Yii::$app->request->isOptions) {
            $txtName = sprintf('%s.%s', \Yii::$app->controller->id, $event->action->id);
            if (!empty(\Yii::$app->controller->module)) {
                $txtName = \Yii::$app->controller->module->id . '.' . $txtName;
            }
            $this->agent->getTransaction()->setName($txtName);
        }
    }
}