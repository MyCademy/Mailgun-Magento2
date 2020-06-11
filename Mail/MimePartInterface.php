<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Bogardo\Mailgun\Mail;

/**
 * Interface representing a MIME part.
 */
interface MimePartInterface
{
    /**
     * Get type
     *
     * @return string
     */
    public function getType();

    /**
     * Get encoding
     *
     * @return string
     */
    public function getEncoding();

    /**
     * Get disposition
     *
     * @return string
     */
    public function getDisposition();

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Get filename
     *
     * @return string
     */
    public function getFileName();

    /**
     * Get charset
     *
     * @return string
     */
    public function getCharset();

    /**
     * Get boundary
     *
     * @return string
     */
    public function getBoundary();

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation();

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage();

    /**
     * Get Filters
     *
     * @return array
     */
    public function getFilters();

    /**
     * Check if this part can be read as a stream
     *
     * @return bool
     */
    public function isStream();

    /**
     * If this was created with a stream, return a filtered stream for reading the content. Useful for file attachment
     *
     * @param string $endOfLine
     *
     * @return resource
     */
    public function getEncodedStream($endOfLine = MimeInterface::LINE_END);

    /**
     * Get the Content of the current Mime Part in the given encoding.
     *
     * @param string $endOfLine
     *
     * @return string|resource
     */
    public function getContent($endOfLine = MimeInterface::LINE_END);

    /**
     * Get the RAW unencoded content from this part
     *
     * @return string
     */
    public function getRawContent();

    /**
     * Create and return the array of headers for this MIME part
     *
     * @param string $endOfLine
     *
     * @return array
     */
    public function getHeadersArray($endOfLine = MimeInterface::LINE_END);

    /**
     * Create and return the array of headers for this MIME part
     *
     * @param string $endOfLine
     *
     * @return string
     */
    public function getHeaders($endOfLine = MimeInterface::LINE_END);
}
