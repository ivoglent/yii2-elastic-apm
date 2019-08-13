<?php


namespace ivoglent\yii2\apm;


use yii\base\Component;

class Listener extends Component
{
    /**
     * @var Agent
     */
    protected $agent;

    public function init()
    {
        parent::init();
        $this->agent = \Yii::$app->apmAgent;
    }

    public function start() {
        //Void
    }
}