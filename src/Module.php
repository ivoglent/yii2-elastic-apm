<?php

namespace ivoglent\yii2\apm;


use Elastic\Apm\PhpAgent\Config;
use ivoglent\yii2\apm\listeners\ConsoleListener;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;

class Module extends \yii\base\Module implements BootstrapInterface
{
    /**
     * @var array
     */
    public $configs;
    /**
     * @var Agent
     */
    private $agent;


    public function init()
    {
        parent::init();
        if (empty($this->configs['agent'])) {
            throw new InvalidConfigException('Missing config for APM agent');
        }
        $config = new Config(...$this->configs['agent']);
        $this->agent = new Agent($config);
    }

    /**
     * @return Agent
     */
    public function getAgent(): Agent
    {
        return $this->agent;
    }



    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        $app->setComponents([
            'consoleListener' => [
                'class' => ConsoleListener::class
            ],
            'queryListener' => [
                'class' => ConsoleListener::class
            ],
            'RequestListener' => [
                'class' => ConsoleListener::class
            ],
        ]);
    }
}