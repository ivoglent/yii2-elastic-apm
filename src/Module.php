<?php

namespace ivoglent\yii2\apm;


use Elastic\Apm\PhpAgent\Config;
use Elastic\Apm\PhpAgent\Model\Framework;
use Exception;
use ivoglent\yii2\apm\components\ConsoleErrorHandler;
use ivoglent\yii2\apm\components\WebErrorHandler;
use ivoglent\yii2\apm\listeners\ConsoleListener;
use ivoglent\yii2\apm\listeners\QueryListener;
use ivoglent\yii2\apm\listeners\ExceptionListener;
use ivoglent\yii2\apm\listeners\RequestListener;
use ivoglent\yii2\apm\listeners\TraceListener;
use ivoglent\yii2\apm\listeners\WorkerListener;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;
use Yii;

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

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        Yii::setAlias('ivoglent/yii2/apm', __DIR__);
        if ($this->enabled && !$this->isAssetRequest()) {
            if (empty($this->configs['agent'])) {
                throw new InvalidConfigException('Missing config for APM agent');
            }
            $agentConfig = $this->configs['agent'];
            $config = new Config($agentConfig['name'], Yii::$app->version, $agentConfig['serverUrl'], $agentConfig['token']);
            $fromework = new Framework([
                'name' => 'Yii2',
                'version' => Yii::getVersion()
            ]);
            $config->setFramework($fromework);
            $config->setEnvironment(YII_ENV);

            Yii::info('APM module init', 'apm');

            try {
                $this->agent = new Agent($config);
                if (PHP_SAPI === 'cli') {
                    Yii::$app->setComponents([
                        'errorHandler' => [
                            'class' => ConsoleErrorHandler::class,
                        ]
                    ]);
                } else {
                    Yii::$app->setComponents([
                        'errorHandler' => [
                            'class' => WebErrorHandler::class,
                            'errorAction' => '/' . Yii::$app->errorHandler->errorAction
                        ]
                    ]);
                }

                Yii::$app->errorHandler->register();
            } catch (Exception $e) {
                Yii::error($e->getMessage());
            }

        }

    }

    /**
     * @return false|int
     */
    private function isAssetRequest() {
        if (isset($_SERVER['REQUEST_URI'])) {
            $url = $_SERVER['REQUEST_URI'];
            return preg_match('/\.(js|css|png|jpeg|jpg|map|mp4|avi|mp3|mov)$/i', $url);
        }
        return false;
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
        $components = [
            'apmAgent' => $this->agent,
        ];
        if ($this->enabled) {
            Yii::info('APM module booting', 'apm');
            if (PHP_SAPI === 'cli') {
                $components = array_merge($components, [
                    'consoleListener' => [
                        'class' => ConsoleListener::class,
                        'skipCommands' => isset($this->configs['skipCommands']) ? $this->configs['skipCommands'] : []
                    ]
                ]);
            } else {
                if (!$this->isAssetRequest()) {
                    $components = array_merge($components, [
                        'requestListener' => [
                            'class' => RequestListener::class
                        ]
                    ]);
                }
            }
            $components = array_merge($components, [
                'queryListener' => [
                    'class' => QueryListener::class
                ],
                'exceptionListener' => [
                    'class' => ExceptionListener::class,
                    'skipExceptions' => isset($this->configs['skipExceptions']) ? $this->configs['skipExceptions'] : []
                ],
                'workerListener' => [
                    'class' => WorkerListener::class
                ],
                'traceListener' => [
                    'class' => TraceListener::class
                ],
            ]);
            $app->setComponents($components);
            foreach ($components as $key => $config) {
                $component = $app->{$key};
                if ($component instanceof Listener) {
                    $component->start();
                }
            }
        }
    }
}