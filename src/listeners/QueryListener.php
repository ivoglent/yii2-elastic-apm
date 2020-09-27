<?php


namespace ivoglent\yii2\apm\listeners;


use Elastic\Apm\PhpAgent\Model\Context\DbContext;
use Elastic\Apm\PhpAgent\Model\Span;
use ivoglent\yii2\apm\components\db\mysql\Command;
use ivoglent\yii2\apm\Listener;
use yii\base\Event;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class QueryListener extends Listener
{
    /** @var Span */
    private $span;

    public function init()
    {
        parent::init();
        Event::on(Command::class, Command::EVENT_BEFORE_QUERY, [$this, 'beforeQuery']);
        Event::on(Command::class, Command::EVENT_AFTER_QUERY, [$this, 'afterQuery']);
    }

    public function beforeQuery(Event $event) {
        /** @var Command $command */
        $command = $event->sender;
        if ($this->agent->isReady()) {
            $this->span = $this->agent->startTrace($command->getName(), 'query');
        }
    }

    public function afterQuery(Event $event) {
        if ($this->agent->isReady()) {
            /** @var Command $command */
            $command = $event->sender;
            $context = new DbContext([
                'spanType' => 'db',
                'type' => 'query',
                'statement' => $command->getQuery()
            ]);
            $this->agent->stopTrace($this->span->getId(), $context);
        }
    }
}