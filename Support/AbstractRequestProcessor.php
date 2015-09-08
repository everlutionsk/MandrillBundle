<?php

namespace Everlution\MandrillBundle\Support;

use Everlution\EmailBundle\Support\RequestSignatureVerifier;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractRequestProcessor implements RequestSignatureVerifier
{
    /** @var RequestSignatureGenerator */
    protected $requestSignatureGenerator;

    /** @var string */
    protected $webhookKey;

    /**
     * @param RequestSignatureGenerator $requestSignatureGenerator
     * @param string $webhookKey
     */
    public function __construct(RequestSignatureGenerator $requestSignatureGenerator, $webhookKey)
    {
        $this->requestSignatureGenerator = $requestSignatureGenerator;
        $this->webhookKey = $webhookKey;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function isRequestSignatureCorrect(Request $request)
    {
        $signature = $request->headers->get('X-Mandrill-Signature');
        $generatedSignature = $this->requestSignatureGenerator->generateSignature($request, $this->webhookKey);

        //TODO: Check Inbound/MessageEvent signature calculation
        //return $signature === $generatedSignature;
        return true;
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function getMandrillEvents(Request $request)
    {
        return json_decode($request->request->get('mandrill_events'), true);
    }

}
