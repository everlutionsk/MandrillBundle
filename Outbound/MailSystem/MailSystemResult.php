<?php

namespace Everlution\MandrillBundle\Outbound\MailSystem;

use Everlution\EmailBundle\Outbound\Message\UniqueOutboundMessage;
use Everlution\EmailBundle\Outbound\MailSystem\MailSystemMessageStatus;
use Everlution\EmailBundle\Message\Recipient\Recipient;
use Everlution\EmailBundle\Outbound\MailSystem\MailSystemResult as MailSystemResultInterface;

class MailSystemResult implements MailSystemResultInterface
{

    /** @var MailSystemMessageStatus[] */
    protected $mailSystemMessagesStatus;

    /**
     * @param array $mandrillResult
     * @param UniqueOutboundMessage $uniqueMessage
     */
    public function __construct(array $mandrillResult, UniqueOutboundMessage $uniqueMessage)
    {
        $recipients = $uniqueMessage->getMessage()->getRecipients();
        $this->mailSystemMessagesStatus = $this->createMailSystemMessagesStatus($mandrillResult, $recipients);
    }

    /**
     * @return MailSystemMessageStatus[]
     */
    public function getMailSystemMessagesStatus()
    {
        return $this->mailSystemMessagesStatus;
    }

    /**
     * @param array $mandrillResult
     * @param Recipient[] $recipients
     * @return MailSystemMessageStatus[]
     */
    protected function createMailSystemMessagesStatus(array $mandrillResult, array $recipients)
    {
        $messagesStatus = [];

        foreach ($mandrillResult as $rawMessageStatus) {
            $recipient = $this->findRecipientByEmail($recipients, $rawMessageStatus['email']);

            if ($recipient !== null) {
                $messagesStatus[] = $this->createMailSystemMessageStatus($rawMessageStatus, $recipient);
            }
        }

        return $messagesStatus;
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
     * @param array $rawMessageStatus
     * @param Recipient $recipient
     * @return MailSystemMessageStatus
     */
    protected function createMailSystemMessageStatus($rawMessageStatus, Recipient $recipient)
    {
        $rejectReason = null;
        if (isset($rawMessageStatus['reject_reason'])) {
            $rejectReason = $rawMessageStatus['reject_reason'];
        }

        return new MailSystemMessageStatus($rawMessageStatus['_id'], $rawMessageStatus['status'], $rejectReason, $recipient);
    }

}
