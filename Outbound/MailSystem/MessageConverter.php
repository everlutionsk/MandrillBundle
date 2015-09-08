<?php

namespace Everlution\MandrillBundle\Outbound\MailSystem;

use Everlution\EmailBundle\Attachment\Attachment;
use Everlution\EmailBundle\Outbound\Message\UniqueOutboundMessage;
use Everlution\EmailBundle\Outbound\Message\OutboundMessage;
use Everlution\EmailBundle\Message\Recipient\Recipient;
use Everlution\EmailBundle\Message\Template\Parameter;

class MessageConverter
{

    /**
     * @param UniqueOutboundMessage $uniqueMessage
     * @return array
     */
    public function convertToRawMessage(UniqueOutboundMessage $uniqueMessage)
    {
        $message = $uniqueMessage->getMessage();

        return [
            'headers' => $this->getRawHeaders($uniqueMessage),
            'subject' => $message->getSubject(),
            'html' => $message->getHtml(),
            'from_email' => $message->getFromEmail(),
            'from_name' => $message->getFromName(),
            'text' => $message->getText(),
            'to' => $this->getRawRecipients($message),
            'images' => $this->getRawImages($message->getImages()),
            'attachments' => $this->getRawAttachments($message->getAttachments()),
        ];
    }

    /**
     * @param Parameter[] $parameters
     * @return array
     */
    public function convertToRawTemplateParameters(array $parameters)
    {
        return array_map(function(Parameter $parameter) {
            return [
                'name' => $parameter->getName(),
                'content' => $parameter->getValue()
            ];
        }, $parameters);
    }

    /**
     * @param UniqueOutboundMessage $uniqueMessage
     * @return array
     */
    protected function getRawHeaders(UniqueOutboundMessage $uniqueMessage)
    {
        $message = $uniqueMessage->getMessage();
        $headers = [];

        $headers['Message-ID'] = $uniqueMessage->getMessageId();

        if ($message->getReplyTo() !== null) $headers['Reply-To'] = $message->getReplyTo();
        if ($message->getReferences() !== null) $headers['References'] = $message->getReferences();
        if ($message->getInReplyTo() !== null) $headers['In-Reply-To'] = $message->getInReplyTo();

        $headers = array_merge($headers, $message->getCustomHeaders());

        return $headers;
    }

    /**
     * @param OutboundMessage $message
     * @return array
     */
    protected function getRawRecipients(OutboundMessage $message)
    {
        return array_map(function(Recipient $recipient) {
            return [
                'email' => $recipient->getEmail(),
                'name' => $recipient->getName(),
                'type' => $recipient->getType()
            ];
        }, $message->getRecipients());
    }

    /**
     * @param Attachment[] $images
     * @return array
     */
    protected function getRawImages(array $images)
    {
        return $this->getRawAttachments($images);
    }

    /**
     * @param Attachment[] $attachments
     * @return array
     */
    protected function getRawAttachments(array $attachments)
    {
        return array_map(function(Attachment $attachment) {
            return [
                'type' => $attachment->getMimeType(),
                'name' => $attachment->getName(),
                'content' => base64_encode($attachment->getContent()),
            ];
        }, $attachments);
    }

}
