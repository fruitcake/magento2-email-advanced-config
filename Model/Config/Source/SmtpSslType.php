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
class SmtpSslType implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * The the possible Auth types
     *
     * @codeCoverageIgnore
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'none', 'label' => 'None'],
            ['value' => 'ssl', 'label' => 'SSL'],
            ['value' => 'tls', 'label' => 'TLS'],
        ];
    }
}
