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
use yii\web\Response;

class LogTarget extends Target
{
    public $logFile = '';
    const QUERY_MAX_LEN = 50;

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
                'type' => 'sql'
            ]);
            $context = new DbContext([
                'type' => 'query',
                'statement' => $query['info']
            ]);
            $span->setContext('db', $context);
            $span->setDuration((float) number_format($query['duration'] * 1000, 3));
            $span->setTimestamp($query['timestamp'] * 1000000);
            $transaction = $this->agent->getTransaction();
            $start = $span->getTimestamp() - $transaction->getTimestamp();
            $start = $start < 0 ? 0 : $start;
            $span->setStart($start);
            foreach ($query['trace'] as $trace) {
                $span->setStacktrace([
                    'filename' => basename($trace['file']),
                    'lineno' => $trace['line'],
                    'abs_path' => $trace['file'],
                    'function' => $trace['function'],
                    'module' => $trace['class']
                ]);
            }
            $this->agent->register($span);
        }
        $this->agent->stopTransaction();
    }

    /**
     * @param $sql
     * @return string
     */
    private function getQueryName($sql) {
        $sql = str_replace('`', "", $sql);
        if (strlen($sql) <= self::QUERY_MAX_LEN) {
            return $sql;
        }
        $command = strtoupper(trim(substr($sql,0, strpos($sql, ' '))));
        $name = $command;
        switch ($command) {
            case 'SELECT':
                if(preg_match('/FROM\s(.*?)\s/i', $sql, $matches)) {
                    $name = "$name FROM " . $matches[1];
                }
                break;
            case  'UPDATE':
                if(preg_match('/UPDATE \s(.*?)\s/i', $sql, $matches)) {
                    $name = "$name " . $matches[1];
                }
                break;
            case  'INSERT':
                if(preg_match('/INSERT INTO\s(.*?)\s/i', $sql, $matches)) {
                    $name = "$name INTO " . $matches[1];
                }
                break;
            case  'DELETE':
                if(preg_match('/FROM \s(.*?)\s/i', $sql, $matches)) {
                    $name = "$name FROM " . $matches[1];
                }
                break;
            case  'SHOW':
                $name = "$name TABLES";
                break;
            default:
                $name = substr($sql, 0, self::QUERY_MAX_LEN) . '...';
        }

        return $name;

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
        $this->messages = array_merge($this->messages, static::filterMessages($messages, $this->getLevels(), $this->categories, $this->except));
        $count = count($this->messages);
        if ($final) {
            $this->export();
            $this->messages = [];
        }
    }
}
