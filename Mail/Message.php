<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bogardo\Mailgun\Mail;

use Zend\Mime\Mime;
use Zend\Mime\Part;
use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Framework\Mail\EmailMessage;
use Magento\Framework\Mail\MimeMessageInterface;
use Magento\Framework\Mail\Address;

class Message extends EmailMessage {
    /**
     * @var \Zend\Mail\Message
     */
    protected $zendMessage;

    /**
     * Message type
     *
     * @var string
     */
    private $messageType = self::TYPE_TEXT;

    /**
     * Types of message
     * @deprecated
     */
    const TYPE_TEXT = 'text/plain';

    /**
     * @deprecated
     */
    const TYPE_HTML = 'text/html';

    /**
     * Initialize dependencies.
     *
     * @param string $charset
     */
    public function __construct( $charset = 'utf-8' ) {
        $this->zendMessage = new \Zend\Mail\Message();
        $this->zendMessage->setEncoding( $charset );
    }

    /**
     * @return \Zend\Mail\Message
     */
    public function getZendMessage()
    {
        return $this->zendMessage;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated
     * @see \Magento\Framework\Mail\Message::setBodyText
     * @see \Magento\Framework\Mail\Message::setBodyHtml
     */
    public function setMessageType( $type ) {
        $this->messageType = $type;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated
     * @see \Magento\Framework\Mail\Message::setBodyText
     * @see \Magento\Framework\Mail\Message::setBodyHtml
     */
    public function setBody( $body ) {
        if ( is_string( $body ) && $this->messageType === MailMessageInterface::TYPE_HTML ) {
            $body = $this->createHtmlMimeFromString( $body );
        }
        $this->zendMessage->setBody( $body );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setSubject( $subject ) {
        $this->zendMessage->setSubject( $subject );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSubject() : ?string {
        return $this->zendMessage->getSubject();
    }

    /**
     * @inheritdoc
     */
    public function getBody() : MimeMessageInterface {
        return $this->zendMessage->getBody();
    }

    /**
     * @inheritdoc
     */
    public function setFromAddress($fromAddress, $fromName = null)
    {
        $this->zendMessage->setFrom($fromAddress, $fromName);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setFrom( $fromAddress )
    {
        $this->zendMessage->setFrom( $fromAddress );
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addTo( $toAddress ) {
        $this->zendMessage->addTo( $toAddress );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addCc( $ccAddress ) {
        $this->zendMessage->addCc( $ccAddress );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addBcc( $bccAddress ) {
        $this->zendMessage->addBcc( $bccAddress );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setReplyTo( $replyToAddress ) {
        $this->zendMessage->setReplyTo( $replyToAddress );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRawMessage() {
        return $this->zendMessage->toString();
    }

    /**
     * @inheritdoc
     */
    public function setBodyHtml( $html ) {
        $this->setMessageType( self::TYPE_HTML );

        return $this->setBody( $html );
    }

    /**
     * @inheritdoc
     */
    public function setBodyText( $text ) {
        $this->setMessageType( self::TYPE_TEXT );

        return $this->setBody( $text );
    }

    /**
     * @inheritDoc
     */
    public function getEncoding(): string
    {
        return $this->message->getEncoding();
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return $this->message->getHeaders()->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getFrom(): ?array
    {
        return $this->convertAddressListToAddressArray($this->message->getFrom());
    }


    /**
     * @inheritDoc
     */
    public function getTo(): array
    {
        return $this->convertAddressListToAddressArray($this->message->getTo());
    }

    /**
     * @inheritDoc
     */
    public function getCc(): ?array
    {
        return $this->convertAddressListToAddressArray($this->message->getCc());
    }

    /**
     * @inheritDoc
     */
    public function getBcc(): ?array
    {
        return $this->convertAddressListToAddressArray($this->message->getBcc());
    }

    /**
     * @inheritDoc
     */
    public function getReplyTo(): ?array
    {
        return $this->convertAddressListToAddressArray($this->message->getReplyTo());
    }

    /**
     * @inheritDoc
     */
    public function getBodyText(): string
    {
        return $this->message->getBodyText();
    }

    /**
     * @inheritDoc
     */
    public function getSender(): ?Address
    {
        /** @var ZendAddress $zendSender */
        if (!$zendSender = $this->message->getSender()) {
            return null;
        }

        return $this->addressFactory->create(
            [
                'email' => $zendSender->getEmail(),
                'name' => $zendSender->getName()
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return $this->message->toString();
    }

    /**
     * Create HTML mime message from the string.
     *
     * @param string $htmlBody
     *
     * @return \Zend\Mime\Message
     */
    private function createHtmlMimeFromString( $htmlBody ) {
        $htmlPart = new Part( $htmlBody );
        $htmlPart->setCharset( $this->zendMessage->getEncoding() );
        $htmlPart->setType( Mime::TYPE_HTML );
        $mimeMessage = new \Zend\Mime\Message();
        $mimeMessage->addPart( $htmlPart );

        return $mimeMessage;
    }
}