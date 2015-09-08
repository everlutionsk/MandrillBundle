<?php

namespace Everlution\MandrillBundle\Outbound\MessageEvent;

use Everlution\MandrillBundle\Support\AbstractRequestProcessor;
use Symfony\Component\HttpFoundation\Request;
use Everlution\EmailBundle\Outbound\MessageEvent\MessageEvent;
use Everlution\EmailBundle\Outbound\MessageEvent\RequestProcessor as RequestProcessorInterface;

class RequestProcessor extends AbstractRequestProcessor implements RequestProcessorInterface
{

    /**
     * @param Request $request
     * @return MessageEvent[]
     */
    public function createMessageEvents(Request $request)
    {
        $mandrillEvents = $this->getMandrillEvents($request);

        return array_map(function($mandrillEvent) {
            $msg = $mandrillEvent['msg'];
            return new MessageEvent($msg['_id'], $msg['state'], $msg['reject'], 'mandrill');
        }, $mandrillEvents);
    }

}
