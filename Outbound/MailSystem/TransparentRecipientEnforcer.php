<?php

namespace Everlution\MandrillBundle\Outbound\MailSystem;

use Everlution\EmailBundle\Message\Recipient\Recipient;

class TransparentRecipientEnforcer
{

    /** @var string|null */
    protected $enforcedDeliveryAddress;

    /**
     * @param string|null $enforcedDeliveryAddress
     */
    public function __construct($enforcedDeliveryAddress = null)
    {
        $this->enforcedDeliveryAddress = $enforcedDeliveryAddress;
    }

    /**
     * @param array $rawMessage
     */
    public function tryTransformRawMessage(array &$rawMessage)
    {
        if (!$this->isRecipientEnforced()) return;

        foreach ($rawMessage['to'] as &$recipient) {
            $recipient['email'] = $this->enforcedDeliveryAddress;
        }
    }

    /**
     * @param array $mandrillResult
     * @param Recipient[] $originalRecipients
     */
    public function tryTransformMandrillResult(array &$mandrillResult, array $originalRecipients)
    {
        if (!$this->isRecipientEnforced()) return;

        foreach ($mandrillResult as $key => &$messageStatus) {
            if (isset($originalRecipients[$key])) {
                $messageStatus['email'] = $originalRecipients[$key]->getEmail();
            }
        }
    }

    /**
     * @return bool
     */
    protected function isRecipientEnforced()
    {
        return $this->enforcedDeliveryAddress !== null;
    }

}
