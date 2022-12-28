<?php

namespace Fruitcake\EmailAdvancedConfig\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Cache\Manager;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var Manager
     */
    protected $cacheManager;

    /**
     * @param ResourceConnection $resourceConnection
     * @param EncryptorInterface $encryptor
     * @param WriterInterface $configWriter
     * @param Manager $cacheManager
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        EncryptorInterface $encryptor,
        WriterInterface $configWriter,
        Manager $cacheManager
    ) {
        $this->connection = $resourceConnection->getConnection();
        $this->encryptor = $encryptor;
        $this->configWriter = $configWriter;
        $this->cacheManager = $cacheManager;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $passwordPath = 'fruitcake_email_advanced/smtp/password';

            $passwordQuery = $this->connection
                ->select()
                ->from('core_config_data', 'value')
                ->where('path = ?', $passwordPath);

            if ($password = $this->connection->fetchOne($passwordQuery)) {
                $this->configWriter->save(
                    $passwordPath,
                    $this->encryptor->encrypt($password)
                );

                $this->cacheManager->flush(['config']);
                $this->cacheManager->clean(['config']);
            }
        }
    }
}
