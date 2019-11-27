<?php

namespace Bogardo\Mailgun\Mail;

use Bogardo\Mailgun\Helper\Config as Config;
use InvalidArgumentException;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Framework\Phrase;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Email\Model\Transport as MagentoTransport;
use Mailgun\Mailgun;
use Mailgun\Messages\MessageBuilder;
use Zend_Mail;

class Transport extends MagentoTransport
{

    /**
     * @var \Bogardo\Mailgun\Helper\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\Mail\EmailMessageInterface
     */
    protected $message;

    /**
     * Transport constructor.
     *
     * @param \Magento\Framework\Mail\EmailMessageInterface $message
     * @param null                                     $parameters
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        EmailMessageInterface $message,
        ScopeConfigInterface $scopeConfig,
        $parameters = null
    ){

        parent::__construct($message, $scopeConfig, $parameters);

        $this->config = ObjectManager::getInstance()->create(Config::class);
        $this->message = $message;
    }

    /**
     * Send a mail using this transport
     *
     * @return void
     */
    public function sendMessage()
    {
        // If Mailgun Service is disabled, use the default mail transport
        if (!$this->config->enabled()) {
            parent::sendMessage();

            return;
        }

        $messageBuilder = $this->createMailgunMessage($this->parseMessage());

        $mailgun = new Mailgun($this->config->privateKey(), $this->getHttpClient(), $this->config->endpoint());
        $mailgun->setApiVersion($this->config->version());
        $mailgun->setSslEnabled($this->config->ssl());

        $mailgun->sendMessage($this->config->domain(), $messageBuilder->getMessage(), $messageBuilder->getFiles());
    }

    /**
     * @return array
     */
    protected function parseMessage()
    {
        $parser = new MessageParser($this->message);

        return $parser->parse();
    }

    /**
     * @return \Http\Client\HttpClient
     */
    protected function getHttpClient()
    {
        return new \Http\Adapter\Guzzle6\Client();
    }

    /**
     * @param array $message
     *
     * @return \Mailgun\Messages\MessageBuilder
     * @throws \Mailgun\Messages\Exceptions\TooManyParameters
     */
    protected function createMailgunMessage(array $message)
    {
        $builder = new MessageBuilder();

        $builder->setFromAddress($message['from']);
        $builder->setSubject($message['subject']);
        foreach ($message['to'] as $to) {
            $builder->addToRecipient($to);
        }

        foreach ($message['cc'] as $cc) {
            $builder->addCcRecipient($cc);
        }

        foreach ($message['bcc'] as $bcc) {
            $builder->addBccRecipient($bcc);
        }

        if ($message['html']) {
            $builder->setHtmlBody($message['html']);
        }

        if ($message['text']) {
            $builder->setTextBody($message['text']);
        }

        foreach ($message['attachments'] as $attachment) { /** @var \Zend_Mime_Part $attachment */
            $tempPath = tempnam(sys_get_temp_dir(), 'attachment');
            file_put_contents($tempPath, $attachment->getRawContent());
            $builder->addAttachment($tempPath, $attachment->filename);
        }

        return $builder;
    }

}
