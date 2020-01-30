<?php

namespace Bogardo\Mailgun\Mail;

use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Mail\MimeMessage;
use Zend\Mail\Address;
use Zend\Mail\AddressList;

class MessageParser
{

    /**
     * @var \Magento\Framework\Message\EmailMessage
     */
    protected $message;

    /**
     * @param \Magento\Framework\Mail\EmailMessageInterface $message
     */
    public function __construct(
        EmailMessageInterface $message
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

        $messageBody = $this->message->getMessageBody();
        if ($messageBody && $messageBody instanceof MimeMessage) {
            foreach($messageBody->getParts() as $messageSubPart) { /** @var \Magento\Framework\Mail\MimePart $messageSubPart */
                if($messageSubPart->getType() == 'text/html') {
                    $html .= $messageSubPart->getContent($eol);
                }
            }
        }

        $text = $this->message->getBodyText();

        $text = quoted_printable_decode($text);
        $html = quoted_printable_decode($html);

        if(empty($this->getFlatAddressList($this->message->getFrom()))) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $fromAddress = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('trans_email/ident_general/email',ScopeInterface::SCOPE_STORE);
        }else{
            $fromAddress = $this->getFlatAddressList($this->message->getFrom())[0];
        }

        $toAddressList = $this->getFlatAddressList($this->message->getTo());
        $ccAddressList = $this->getFlatAddressList($this->message->getCc());
        $bccAddressList = $this->getFlatAddressList($this->message->getBcc());
        $replyToAddressList = $this->getFlatAddressList($this->message->getReplyTo());

        $attachments = [];
        if($messageBody && $messageBody instanceof MimeMessage) {
            foreach ($messageBody->getParts() as $part) { /** @var \Magento\Framework\Mail\MimePart $part */
                if ($part->getDisposition() == 'attachment') {
                    $attachments[] = $part;
                }
            }
        }

        return [
            'from' => $fromAddress,
            'reply-to' => $replyToAddressList,
            'subject' => $this->message->getSubject(),
            'to' => $toAddressList,
            'cc' => $ccAddressList,
            'bcc' => $bccAddressList,
            'html' => $html ?: null,
            'text' => $text ?: null,
            'attachments' => $attachments,
        ];
    }

    protected function getFlatAddressList(array $zendAddressList)
    {
        $addressList = [];

        foreach($zendAddressList as $address) {
            /** @var $address Address */
            $addressList[] = $address->getEmail();
        }

        return $addressList;
    }
}
