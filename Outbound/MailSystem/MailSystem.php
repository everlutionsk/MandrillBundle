<?php

namespace Everlution\MandrillBundle\Outbound\MailSystem;

use DateTime, DateTimeZone, Mandrill;
use Everlution\EmailBundle\Outbound\Message\UniqueOutboundMessage;
use Everlution\EmailBundle\Outbound\MailSystem\MailSystem as MailSystemInterface;
use Everlution\EmailBundle\Outbound\MailSystem\MailSystemException;
use Everlution\EmailBundle\Message\Template\Template;

class MailSystem implements MailSystemInterface
{

    /** @var RawMessageTransformer[] */
    protected $rawMessageTransformers = [];

    /** @var bool */
    protected $asyncMandrillSending;

    /** @var Mandrill */
    protected $mandrill;

    /** @var MessageConverter */
    protected $messageConverter;

    /** @var TransparentRecipientEnforcer */
    protected $recipientEnforcer;

    /**
     * @param string $apiKey
     * @param bool $asyncMandrillSending
     * @param MessageConverter $messageConverter
     * @param TransparentRecipientEnforcer $recipientEnforcer
     */
    public function __construct($apiKey, $asyncMandrillSending, MessageConverter $messageConverter, TransparentRecipientEnforcer $recipientEnforcer)
    {
        $this->mandrill = new Mandrill($apiKey);
        $this->asyncMandrillSending = $asyncMandrillSending;
        $this->messageConverter = $messageConverter;
        $this->recipientEnforcer = $recipientEnforcer;
    }

    /**
     * @param RawMessageTransformer $transformer
     */
    public function addRawMessageTransformer(RawMessageTransformer $transformer)
    {
        $this->rawMessageTransformers[] = $transformer;
    }

    /**
     * @param UniqueOutboundMessage $uniqueMessage
     * @return MailSystemResult
     * @throws MailSystemException
     */
    public function sendMessage(UniqueOutboundMessage $uniqueMessage)
    {
        return $this->sendMessageToMandrill($uniqueMessage);
    }

    /**
     * @param UniqueOutboundMessage $uniqueMessage
     * @param DateTime $sendAt
     * @return MailSystemResult
     * @throws MailSystemException
     */
    public function scheduleMessage(UniqueOutboundMessage $uniqueMessage, DateTime $sendAt)
    {
        return $this->sendMessageToMandrill($uniqueMessage, $sendAt);
    }




    /**
     * @param UniqueOutboundMessage $uniqueMessage
     * @param DateTime|null $sendAt
     * @return MailSystemResult
     * @throws MailSystemException
     */
    protected function sendMessageToMandrill(UniqueOutboundMessage $uniqueMessage, DateTime $sendAt = null)
    {
        $template = $uniqueMessage->getMessage()->getTemplate();

        $rawMessage = $this->messageConverter->convertToRawMessage($uniqueMessage);
        $this->transformRawMessage($rawMessage);

        $this->recipientEnforcer->tryTransformRawMessage($rawMessage);

        if ($template === null) {
            $mandrillResult = $this->sendRawMessage($rawMessage, $sendAt);
        } else {
            $mandrillResult = $this->sendRawTemplateMessage($rawMessage, $template, $sendAt);
        }

        $this->recipientEnforcer->tryTransformMandrillResult($mandrillResult, $uniqueMessage->getMessage()->getRecipients());

        return new MailSystemResult($mandrillResult, $uniqueMessage);
    }

    /**
     * @param array &$rawMessage
     */
    protected function transformRawMessage(array &$rawMessage)
    {
        foreach ($this->rawMessageTransformers as $transformer) {
            $transformer->transform($rawMessage);
        }
    }

    /**
     * @param array $rawMessage
     * @param DateTime|null $sendAt
     * @return array
     * @throws MailSystemException
     */
    protected function sendRawMessage($rawMessage, DateTime $sendAt = null)
    {
        try {
            $sendAt_string = $this->convertDateTimeToString($sendAt);
            return $this->mandrill->messages->send($rawMessage, $this->asyncMandrillSending, null, $sendAt_string);
        } catch (\Mandrill_Error $e) {
            throw new MailSystemException($e->getMessage());
        }
    }

    /**
     * @param DateTime|null $dateTime
     * @return string|null
     */
    protected function convertDateTimeToString(DateTime $dateTime = null)
    {
        if ($dateTime === null) {
            return null;
        }

        $dateTime->setTimezone(new DateTimeZone('UTC'));

        return $dateTime->format('Y-m-d H:i:s');
    }

    /**
     * @param array $rawMessage
     * @param Template $template
     * @param DateTime|null $sendAt
     * @return array
     * @throws MailSystemException
     */
    protected function sendRawTemplateMessage($rawMessage, Template $template, DateTime $sendAt = null)
    {
        $rawTemplateParameters = $this->messageConverter->convertToRawTemplateParameters($template->getParameters());

        try {
            $sendAt_string = $this->convertDateTimeToString($sendAt);
            return $this->mandrill->messages->sendTemplate($template->getName(), $rawTemplateParameters, $rawMessage, $this->asyncMandrillSending, null, $sendAt_string);
        } catch (\Mandrill_Error $e) {
            throw new MailSystemException($e->getMessage());
        }
    }

    /**
     * @return string
     */
    public function getMailSystemName()
    {
        return 'mandrill';
    }

}
