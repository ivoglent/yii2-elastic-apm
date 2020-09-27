<?php


namespace ivoglent\yii2\apm\listeners;


use Elastic\Apm\PhpAgent\Model\Context\DbContext;
use Elastic\Apm\PhpAgent\Model\Context\SpanContext;
use Elastic\Apm\PhpAgent\Model\Span;
use ivoglent\yii2\apm\components\db\mysql\Command;
use ivoglent\yii2\apm\events\TraceEvent;
use ivoglent\yii2\apm\Listener;
use yii\base\Application;
use yii\base\Event;

class TraceListener extends Listener
{
    /** @var Span */
    private $span;

    public function init()
    {
        parent::init();
        Event::on(TracerInterface::class, TracerInterface::EVENT_START_TRACE, [$this, 'startTrace']);
        Event::on(TracerInterface::class, TracerInterface::EVENT_END_TRACE, [$this, 'endTrace']);
    }

    /**
     * @param TraceEvent $event
     * @throws \Elastic\Apm\PhpAgent\Exception\RuntimeException
     */
    public function startTrace(TraceEvent $event) {
        if ($this->agent->isReady()) {
            \Yii::info('Start trace: ' . $event->getTraceName());
            $this->span = $this->agent->startTrace($event->getTraceName(), $event->getTraceType());
        }
    }

    /**
     * @throws \Elastic\Apm\PhpAgent\Exception\RuntimeException
     */
    public function endTrace(Event $event) {
        if ($this->agent->isReady()) {
            \Yii::info('End trace');
            $context = null;
            if ($event instanceof TraceEvent) {
                $context = new SpanContext($event->getContext());
            }
            $this->agent->stopTrace($this->span->getId(), $context);
        }
    }
}