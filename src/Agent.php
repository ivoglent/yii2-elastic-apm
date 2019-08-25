<?php


namespace ivoglent\yii2\apm;

use Elastic\Apm\PhpAgent\Agent as BaseAgent;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Agent extends BaseAgent
{
    public function send(?RequestInterface $request = null): bool
    {
        $request = $this->makeRequest();
        $logs = (sprintf('Sending data %s to endpoint %s', $request->getBody()->getContents(), $request->getUri()));
        file_put_contents(\Yii::getAlias('@digico/frontend/runtime/logs/apm.log'), $logs);
        return parent::send($request);
    }
}