<?php


namespace ivoglent\yii2\apm;

use Elastic\Apm\PhpAgent\Agent as BaseAgent;
use Psr\Http\Message\ResponseInterface;

class Agent extends BaseAgent
{
    public function send(): bool
    {
        $request = $this->makeRequest();
        \Yii::info(sprintf('Sending data %s to endpoint %s', $request->getBody()->getContents(), $request->getUri()));
        return parent::send();
    }
}