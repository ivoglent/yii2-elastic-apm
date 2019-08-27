<?php


namespace ivoglent\yii2\apm\events;


use yii\base\Event;

class TraceEvent extends Event
{
    private $traceName = 'trace';

    private $traceType = 'trace';

    private $context = [];

    /**
     * @return string
     */
    public function getTraceName(): string
    {
        return $this->traceName;
    }

    /**
     * @param string $traceName
     */
    public function setTraceName(string $traceName): void
    {
        $this->traceName = $traceName;
    }

    /**
     * @return string
     */
    public function getTraceType(): string
    {
        return $this->traceType;
    }

    /**
     * @param string $traceType
     */
    public function setTraceType(string $traceType): void
    {
        $this->traceType = $traceType;
    }


    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @param array $context
     */
    public function setContext(array $context): void
    {
        $this->context = $context;
    }


}