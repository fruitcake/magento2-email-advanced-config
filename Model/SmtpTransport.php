<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Fruitcake\EmailAdvancedConfig\Model;

use Fruitcake\EmailAdvancedConfig\Helper\Data;
use Laminas\Mail\Transport\Smtp;
use Laminas\Mail\Transport\SmtpOptions;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Phrase;
use Magento\Store\Model\ScopeInterface;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Sendmail;

/**
 * Class that responsible for filling some message data before transporting it.
 * @see \Laminas\Mail\Transport\Smtp is used for transport
 * @see \Magento\Email\Model\Transport for the original Magento implementation. This overrides the construct
 */
class SmtpTransport implements TransportInterface
{
    /**
     * Configuration path to source of Return-Path and whether it should be set at all
     * @see \Magento\Config\Model\Config\Source\Yesnocustom to possible values
     */
    const XML_PATH_SENDING_SET_RETURN_PATH = 'system/smtp/set_return_path';

    /**
     * Configuration path for custom Return-Path email
     */
    const XML_PATH_SENDING_RETURN_PATH_EMAIL = 'system/smtp/return_path_email';

    /**
     * Whether return path should be set or no.
     *
     * Possible values are:
     * 0 - no
     * 1 - yes (set value as FROM address)
     * 2 - use custom value
     *
     * @var int
     */
    private $isSetReturnPath;

    /**
     * @var string|null
     */
    private $returnPathValue;

    /**
     * @var Sendmail
     */
    private $laminasTransport;

    /**
     * @var MessageInterface
     */
    private $message;

    /** @var Data */
    private $helper;

    /**
     * @param MessageInterface $message Email message object
     * @param ScopeConfigInterface $scopeConfig Core store config
     * @param null|string|array|\Traversable $parameters Config options for sendmail parameters
     */
    public function __construct(
        MessageInterface $message,
        ScopeConfigInterface $scopeConfig,
        Data $helper,
        $parameters = null
    ) {
        $this->isSetReturnPath = (int) $scopeConfig->getValue(
            self::XML_PATH_SENDING_SET_RETURN_PATH,
            ScopeInterface::SCOPE_STORE
        );
        $this->returnPathValue = $scopeConfig->getValue(
            self::XML_PATH_SENDING_RETURN_PATH_EMAIL,
            ScopeInterface::SCOPE_STORE
        );
        $this->helper = $helper;

        $this->message = $message;

        // When not enabled, fall back to Sendmail transport
        if (! $this->helper->isEnabled()) {
            $this->laminasTransport = new Sendmail($parameters);
            return;
        }

        $options  = [
            'name' => 'localhost',
            'host' => $this->helper->getConfig('smtp/host'),
            'port' => $this->helper->getConfig('smtp/port'),
            'connection_config' => [
                'username' => $this->helper->getConfig('smtp/username'),
                'password' => $this->helper->getConfig('smtp/password'),
            ]
        ];

        $auth = $this->helper->getConfig('smtp/auth');
        if ($auth && $auth !== 'none') {
            $options['connection_class'] = $auth;
        }
        
        $ssl = $this->helper->getConfig('smtp/ssl');        
        if ($ssl && $ssl !== 'none') {
            $options['connection_config']['ssl'] = $ssl;
        }

        $this->laminasTransport = new Smtp(new SmtpOptions($options));
    }

    /**
     * @inheritdoc
     */
    public function sendMessage()
    {
        try {
            $laminasMessage = Message::fromString($this->message->getRawMessage())->setEncoding('utf-8');
            $this->removeDuplicateHeaders($laminasMessage);

            if (2 === $this->isSetReturnPath && $this->returnPathValue) {
                $laminasMessage->setSender($this->returnPathValue);
            } elseif (1 === $this->isSetReturnPath && $laminasMessage->getFrom()->count()) {
                $fromAddressList = $laminasMessage->getFrom();
                $fromAddressList->rewind();
                $laminasMessage->setSender($fromAddressList->current()->getEmail());
            }

            $this->laminasTransport->send($laminasMessage);
        } catch (\Exception $e) {
            throw new MailException(new Phrase($e->getMessage()), $e);
        }
    }

    /**
     * Fix an issue with duplicate headers (eg Content-Transfer-Encoding cannot occur twice for AWS SES)
     * 
     * @param Message $laminasMessage
     */
    protected function removeDuplicateHeaders(Message $laminasMessage)
    {
        $headers = clone $laminasMessage->getHeaders();
        $laminasMessage->getHeaders()->clearHeaders();
        $alreadySetHeaders = [];

        foreach ($headers as $header) {
            if (!in_array($header->getFieldName(), $alreadySetHeaders, true)) {
                $laminasMessage->getHeaders()->addHeader($header);
            }
            $alreadySetHeaders[] = $header->getFieldName();
        }
    }

    /**
     * @inheritdoc
     */
    public function getMessage()
    {
        return $this->message;
    }
}
