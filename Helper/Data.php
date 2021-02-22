<?php

namespace Fruitcake\EmailAdvancedConfig\Helper;


use Fruitcake\EmailAdvancedConfig\Model\Config\Source\SmtpTransportType;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;


class Data extends AbstractHelper
{
    const CONFIG_PATH = 'fruitcake_email_advanced/';

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
        return $this->scopeConfig->getValue(self::CONFIG_PATH . $code, ScopeInterface::SCOPE_STORE, null);
    }
}
