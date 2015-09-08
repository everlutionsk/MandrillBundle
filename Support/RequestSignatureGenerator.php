<?php

namespace Everlution\MandrillBundle\Support;

use Symfony\Component\HttpFoundation\Request;

class RequestSignatureGenerator
{

    /**
     * @param Request $request
     * @param string $webhookKey
     * @return string
     */
    public function generateSignature(Request $request, $webhookKey)
    {
        $data = $request->request->all();

        $signedData = $request->getRequestUri();

        ksort($data);

        foreach ($data as $key => $value) {
            $signedData .= $key;
            $signedData .= $value;
        }

        return base64_encode(hash_hmac('sha1', $signedData, $webhookKey, true));
    }

}
