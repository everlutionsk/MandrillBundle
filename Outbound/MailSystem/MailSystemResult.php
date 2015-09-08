<?php

namespace Everlution\MandrillBundle\Outbound\MailSystem;

use Everlution\EmailBundle\Outbound\Message\UniqueOutboundMessage;
use Everlution\EmailBundle\Outbound\MailSystem\MailSystemMessageInfo;
use Everlution\EmailBundle\Message\Recipient\Recipient;
use Everlution\EmailBundle\Outbound\MailSystem\MailSystemResult as MailSystemResultInterface;

class MailSystemResult implements MailSystemResultInterface
{

    /** @var MailSystemMessageInfo[] */
    protected $mailSystemMessagesInfo;

    /**
     * @param array $mandrillResult
     * @param UniqueOutboundMessage $uniqueMessage
     */
    public function __construct(array $mandrillResult, UniqueOutboundMessage $uniqueMessage)
    {
        $recipients = $uniqueMessage->getMessage()->getRecipients();
        $this->mailSystemMessagesInfo = $this->createMailSystemMessagesInfo($mandrillResult, $recipients);
    }

    /**
     * @return MailSystemMessageInfo[]
     */
    public function getMailSystemMessagesInfo()
    {
        return $this->mailSystemMessagesInfo;
    }

    /**
     * @param array $mandrillResult
     * @param Recipient[] $recipients
     * @return MailSystemMessageInfo[]
     */
    protected function createMailSystemMessagesInfo(array $mandrillResult, array $recipients)
    {
        $messagesInfo = [];

        foreach ($mandrillResult as $rawMessageInfo) {
            $recipient = $this->findRecipientByEmail($recipients, $rawMessageInfo['email']);

            if ($recipient !== null) {
                $messagesInfo[] = $this->createMailSystemMessageInfo($rawMessageInfo, $recipient);
            }
        }

        return $messagesInfo;
    }

    /**
     * @param Recipient[] $recipients
     * @param string $email
     * @return Recipient|null
     */
    protected function findRecipientByEmail(array $recipients, $email)
    {
        foreach ($recipients as $recipient) {
            if ($recipient->getEmail() === $email) {
                return $recipient;
            }
        }

        return null;
    }

    /**
     * @param array $rawMessageInfo
     * @param Recipient $recipient
     * @return MailSystemMessageInfo
     */
    protected function createMailSystemMessageInfo($rawMessageInfo, Recipient $recipient)
    {
        $rejectReason = null;
        if (isset($rawMessageInfo['reject_reason'])) {
            $rejectReason = $rawMessageInfo['reject_reason'];
        }

        return new MailSystemMessageInfo($rawMessageInfo['_id'], $rawMessageInfo['status'], $rejectReason, $recipient);
    }

}
