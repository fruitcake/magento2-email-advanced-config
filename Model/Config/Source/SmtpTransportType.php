<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Fruitcake\EmailAdvancedConfig\Model\Config\Source;

use  Fruitcake\EmailAdvancedConfig\Model\Config\AdvancedConfig;

/**
 * Option provider for custom media URL type
 */
class SmtpTransportType implements \Magento\Framework\Data\OptionSourceInterface
{
    public const TRANPORT_SENDMAIL = 'sendmail';
    public const TRANSPORT_SMTP = 'smtp';

    /**
     * The the possible Auth types
     *
     * @codeCoverageIgnore
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::TRANPORT_SENDMAIL, 'label' => 'Sendmail (Magento default)'],
            ['value' => self::TRANSPORT_SMTP, 'label' => 'SMTP Configuration'],
        ];
    }
}
