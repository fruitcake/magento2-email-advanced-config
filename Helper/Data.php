<?php

namespace Fruitcake\EmailAdvancedConfig\Helper;

use Fruitcake\EmailAdvancedConfig\Model\Config\Source\SmtpTransportType;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class Data extends AbstractHelper
{
    const CONFIG_PATH = 'fruitcake_email_advanced/';

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->encryptor = $encryptor;
    }

    public function isEnabled(): bool
    {
        return $this->getConfig('smtp/transport') === SmtpTransportType::TRANSPORT_SMTP;
    }

    /**
     * @param string $code
     *
     * @return mixed
     */
    public function getConfig($code = '')
    {
        $value = $this->scopeConfig->getValue(self::CONFIG_PATH . $code, ScopeInterface::SCOPE_STORE, null);

        return ($code === 'smtp/password')
            ? $this->encryptor->decrypt($value)
            : $value;
    }
}
