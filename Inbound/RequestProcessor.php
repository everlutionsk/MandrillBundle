<?php

namespace Everlution\MandrillBundle\Inbound;

use Everlution\EmailBundle\Attachment\Attachment;
use Everlution\EmailBundle\Attachment\BasicAttachment;
use Everlution\EmailBundle\Message\Header;
use Everlution\EmailBundle\Message\Recipient\Recipient;
use Everlution\MandrillBundle\Support\AbstractRequestProcessor;
use Symfony\Component\HttpFoundation\Request;
use Everlution\EmailBundle\Inbound\Message\InboundMessage;
use Everlution\EmailBundle\Inbound\RequestProcessor as RequestProcessorInterface;

class RequestProcessor extends AbstractRequestProcessor implements RequestProcessorInterface
{

    /**
     * @param Request $request
     * @return InboundMessage[]
     */
    public function createInboundMessages(Request $request)
    {
        $mandrillEvents = $this->getMandrillEvents($request);

        return array_map(function($mandrillEvent) {
            return $this->createInboundMessage($mandrillEvent['msg']);
        }, $mandrillEvents);
    }

    /**
     * @param array $rawMessage
     * @return InboundMessage
     */
    protected function createInboundMessage(array $rawMessage)
    {
        $rawHeaders = array_change_key_case($rawMessage['headers'], CASE_LOWER);

        $message = new InboundMessage();

        $message->setFromName($rawMessage['from_name']);
        $message->setFromEmail($rawMessage['from_email']);
        $message->setSubject($rawMessage['subject']);
        $message->setText($rawMessage['text']);
        $message->setHtml($rawMessage['html']);

        $message->setMessageId($this->getHeaderValue($rawHeaders, 'message-id'));
        $message->setReplyTo($this->getHeaderValue($rawHeaders, 'reply-to'));
        $message->setInReplyTo($this->getHeaderValue($rawHeaders, 'in-reply-to'));
        $message->setReferences($this->getHeaderValue($rawHeaders, 'references'));
        $message->setHeaders($this->getHeadersFromRawHeaders($rawHeaders));

        $message->setRecipients($this->getRecipientsFromRawMessage($rawMessage));
        $message->setAttachments($this->getAttachmentsFromRawMessage($rawMessage));
        $message->setImages($this->getImagesFromRawMessage($rawMessage));

        return $message;
    }

    /**
     * @param array $rawHeaders
     * @param string $key
     * @return string|null
     */
    protected function getHeaderValue(array $rawHeaders, $key)
    {
        return isset($rawHeaders[$key]) ? $rawHeaders[$key] : null;
    }

    /**
     * @param array $rawMessage
     * @return Recipient[]
     */
    protected function getRecipientsFromRawMessage(array $rawMessage)
    {
        $recipients = [];

        if (isset($rawMessage['to'])) {
            foreach ($rawMessage['to'] as $rawRecipient) {
                $recipients[] = new Recipient($rawRecipient[0], $rawRecipient[1], 'to');
            }
        }

        if (isset($rawMessage['cc'])) {
            foreach ($rawMessage['cc'] as $rawRecipient) {
                $recipients[] = new Recipient($rawRecipient[0], $rawRecipient[1], 'cc');
            }
        }

        return $recipients;
    }

    /**
     * @param array $rawHeaders
     * @return Header[]
     */
    protected function getHeadersFromRawHeaders(array $rawHeaders)
    {
        $headers = [];

        foreach ($rawHeaders as $key => $value) {
            $headers[] = new Header($key, $value);
        }

        return $headers;
    }

    /**
     * @param array $rawMessage
     * @return Attachment[]
     */
    protected function getAttachmentsFromRawMessage(array $rawMessage)
    {
        if (!isset($rawMessage['attachments'])) {
            return [];
        }

        return array_map(function($rawAttachment) {
            $rawContent = $rawAttachment['content'];
            return new BasicAttachment(
                $rawAttachment['type'],
                $rawAttachment['name'],
                $rawAttachment['base64'] ? base64_decode($rawContent) : $rawContent
            );
        }, $rawMessage['attachments']);
    }

    /**
     * @param array $rawMessage
     * @return Attachment[]
     */
    protected function getImagesFromRawMessage(array $rawMessage)
    {
        if (!isset($rawMessage['images'])) {
            return [];
        }

        return array_map(function($rawAttachment) {
            return new BasicAttachment(
                $rawAttachment['type'],
                $rawAttachment['name'],
                base64_decode($rawAttachment['content'])
            );
        }, $rawMessage['images']);
    }

}
