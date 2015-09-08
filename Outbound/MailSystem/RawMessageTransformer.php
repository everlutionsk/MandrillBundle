<?php

namespace Everlution\MandrillBundle\Outbound\MailSystem;

interface RawMessageTransformer
{

    /**
     * @param array &$rawMessage
     */
    public function transform(array &$rawMessage);

}
