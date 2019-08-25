<?php

namespace ivoglent\yii2\apm;


use Elastic\Apm\PhpAgent\Config;
use Elastic\Apm\PhpAgent\Model\Framework;
use Elastic\Apm\PhpAgent\Model\User;
use ivoglent\yii2\apm\components\LogTarget;
use ivoglent\yii2\apm\listeners\ConsoleListener;
use ivoglent\yii2\apm\listeners\QueryListener;
use ivoglent\yii2\apm\listeners\RequestListener;
use Monolog\Logger;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\db\ActiveRecordInterface;

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

    public $enabled = false;

    /** @var LogTarget */
    private $logTarget;


    public function init()
    {
        parent::init();
        if ($this->enabled && !$this->isAssetRequest()) {
            if (empty($this->configs['agent'])) {
                throw new InvalidConfigException('Missing config for APM agent');
            }
            $agentConfig = $this->configs['agent'];
            $config = new Config($agentConfig['name'], \Yii::$app->version, $agentConfig['serverUrl'], $agentConfig['token']);
            $fromework = new Framework([
                'name' => 'Yii2',
                'version' => \Yii::getVersion()
            ]);
            $config->setFramework($fromework);
            $config->setEnvironment(YII_ENV);

            if (!\Yii::$app->user->isGuest) {
                $user = new User([
                    'id' => \Yii::$app->user->getId()
                ]);
                $config->setUser($user);
            }
            \Yii::info('APM module init', 'apm');

            $this->agent = new Agent($config);
        }

    }

    /**
     * @return false|int
     */
    private function isAssetRequest() {
        $url = $_SERVER['REQUEST_URI'];
        return preg_match('/\.(js|css|png|jpeg|jpg|map|mp4|avi|mp3|mov)$/i', $url);
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
        if ($this->enabled && !$this->isAssetRequest()) {
            \Yii::info('APM module booting', 'apm');
            $app->setComponents([
                'apmAgent' => $this->agent,
                'consoleListener' => [
                    'class' => ConsoleListener::class
                ],
                'requestListener' => [
                    'class' => RequestListener::class
                ],
                'queryListener' => [
                    'class' => QueryListener::class
                ],
            ]);
            $app->requestListener->start();
            $app->consoleListener->start();
            $app->queryListener->start();

        }
    }
}