<?php

namespace Bogardo\Mailgun\Mail;

use Magento\Framework\Mail\MessageInterface;
use Magento\Store\Model\ScopeInterface;
use Zend\Mail\Address;
use Zend\Mail\AddressList;

class MessageParser
{

    /**
     * @var \Magento\Framework\Message\MessageInterface|\Bogardo\Mailgun\Mail\Message
     */
    protected $message;

    /**
     * @param \Magento\Framework\Mail\MessageInterface $message
     */
    public function __construct(
        MessageInterface $message
    ){
        $this->message = $message;
    }

    /**
     * @return array
     */
    public function parse()
    {
        $eol = "\n";


        $html = "";
        $text = "";

        $messageBody = $this->message->getZendMessage()->getBody();
        if ($messageBody && $messageBody instanceof \Zend\Mime\Message) {
            foreach($messageBody->getParts() as $messageSubPart) {
                if($messageSubPart->getType() == 'text/html') {
                    $html .= $messageSubPart->getContent($eol);
                }
            }
        }

        $text = $this->message->getZendMessage()->getBodyText();

        $text = quoted_printable_decode($text);
        $html = quoted_printable_decode($html);

        if(empty($this->getFlatAddressList($this->message->getZendMessage()->getFrom()))) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $fromAddress = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('trans_email/ident_support/email',ScopeInterface::SCOPE_STORE);
        }else{
            $fromAddress = $this->getFlatAddressList($this->message->getZendMessage()->getFrom())[0];
        }

        $toAddressList = $this->getFlatAddressList($this->message->getZendMessage()->getTo());
        $ccAddressList = $this->getFlatAddressList($this->message->getZendMessage()->getCc());
        $bccAddressList = $this->getFlatAddressList($this->message->getZendMessage()->getBcc());
        $replyToAddressList = $this->getFlatAddressList($this->message->getZendMessage()->getReplyTo());

        $attachments = [];
        if($this->message->getZendMessage()->getBody() instanceof \Zend\Mime\Message) {
            foreach ($this->message->getZendMessage()->getBody()->getParts() as $part) { /** @var \Zend_Mime_Part $part */
                if ($part->disposition == 'attachment') {
                    $attachments[] = $part;
                }
            }
        }

        return [
            'from' => $fromAddress,
            'reply-to' => $replyToAddressList,
            'subject' => $this->message->getZendMessage()->getSubject(),
            'to' => $toAddressList,
            'cc' => $ccAddressList,
            'bcc' => $bccAddressList,
            'html' => $html ?: null,
            'text' => $text ?: null,
            'attachments' => $attachments,
        ];
    }

    protected function getFlatAddressList(AddressList $zendAddressList)
    {
        $addressList = [];

        foreach($zendAddressList as $address) {
            /** @var $address Address */
            $addressList[] = $address->getEmail();
        }

        return $addressList;
    }
}
