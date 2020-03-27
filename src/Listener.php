<?php


namespace ivoglent\yii2\apm;


use yii\base\Component;

class Listener extends Component
{
    /**
     * @var Agent
     */
    protected $agent;

    public function start() {
        $this->agent = \Yii::$app->getModule('apm')->getAgent();
    }
}