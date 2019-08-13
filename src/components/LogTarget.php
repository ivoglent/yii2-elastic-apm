<?php


namespace ivoglent\yii2\apm\components;


use Elastic\Apm\PhpAgent\Model\Context\DbContext;
use Elastic\Apm\PhpAgent\Model\Span;
use ivoglent\yii2\apm\Agent;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Connection;
use yii\db\Exception;
use yii\di\Instance;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;
use yii\log\Logger;
use yii\log\LogRuntimeException;
use yii\log\Target;

class LogTarget extends Target
{
    public $logFile = '';

    /**
     * @var Agent
     */
    public $agent;

    public function export()
    {
        \Yii::info('Collecting DB queries...', 'apm');
        $queries = $this->calculateTimings();
        foreach ($queries as $query) {
            $queryName = $this->getQueryName($query['info']);
            $span = new Span([
                'name' => $queryName,
                'type' => 'query'
            ]);
            $context = new DbContext([
                'type' => 'db',
                'statement' => $query['info']
            ]);
            //\Yii::info($query, 'apm');
            $span->setContext($context);
            $span->setDuration((float) number_format($query['duration'] * 1000, 3));
            $span->setTimestamp((int) $query['timestamp'] * 1000000);
            $span->setStacktrace($query['trace']);
            $this->agent->register($span);
        }
        $this->agent->stopTransaction();
    }

    /**
     * @param $sql
     * @return string
     */
    private function getQueryName($sql) {
        $action = '';
        $table = '';
        return sprintf('Query %s on table %s', $action, $table);
    }

    /**
     * Calculates given request profile timings.
     *
     * @return array timings [`info`, `category`, `timestamp`, `trace`, `level`, `duration`, `memory`, `memoryDiff`]
     */
    public function calculateTimings()
    {
        foreach ($this->messages as $k => $message) {
            if (!in_array($message[2], $this->categories)) {
                unset($this->messages[$k]);
            }
        }
        return Yii::getLogger()->calculateTimings(isset($this->messages) ? $this->messages : []);
    }

    public function collect($messages, $final)
    {
        $this->messages = array_merge($this->messages, $messages);
        $this->export();
    }
}
