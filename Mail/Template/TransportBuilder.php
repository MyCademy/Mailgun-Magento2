<?php
/**
 * Mail Template Transport Builder
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Bogardo\Mailgun\Mail\Template;

use Magento\Framework\Mail\Template\TransportBuilder as BaseTransportBuilder;

/**
 * TransportBuilder
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class TransportBuilder extends BaseTransportBuilder
{
    /**
     * Prepare message.
     *
     * @return $this
     * @throws LocalizedException if template type is unknown
     */
    protected function prepareMessage()
    {
        $template = $this->getTemplate();
        $content = $template->processTemplate();

        switch ($template->getType()) {
            case TemplateTypesInterface::TYPE_TEXT:
                $part['type'] = MimeInterface::TYPE_TEXT;
                break;

            case TemplateTypesInterface::TYPE_HTML:
                $part['type'] = MimeInterface::TYPE_HTML;
                break;

            default:
                throw new LocalizedException(
                    new Phrase('Unknown template type')
                );
        }

        /** @var \Magento\Framework\Mail\MimePartInterface $mimePart */
        $mimePart = $this->mimePartInterfaceFactory->create([
            'content' => $content,
            'disposition' => 'inline'
        ]);
        $this->messageData['encoding'] = $mimePart->getCharset();
        $this->messageData['body'] = $this->mimeMessageInterfaceFactory->create(
            ['parts' => [$mimePart]]
        );

        $this->messageData['subject'] = html_entity_decode(
            (string)$template->getSubject(),
            ENT_QUOTES
        );

        $this->message = $this->emailMessageInterfaceFactory->create($this->messageData);

        return $this;
    }
}
