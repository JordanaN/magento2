<?php

namespace Tegra\EduzzPayment\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Status;

use Tegra\EduzzPayment\Helper\GatewayInfo\MagentoEduzzStatus;
use Tegra\EduzzPayment\Model\Resource\RequestOrderUpdate;

class InstallSchema implements InstallSchemaInterface
{
    const ORDER_TABLE = 'sales_order';
    const ORDER_KEY = 'entity_id';

    private $status;

    public function __construct(
        Status $status
    ) {
        $this->status = $status;
    }

    private function createTableRequestOrderUpdate(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable(RequestOrderUpdate::TABLE);

        if ($setup->getConnection()->isTableExists($table)) {
            return;
        }

        $table = $setup->getConnection()
            ->newTable($table)
            ->addColumn(
                RequestOrderUpdate::ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' =>  true
                ],
                'ID Key'
            )
            ->addColumn(
                RequestOrderUpdate::ORDER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Order Updated'
            )
            ->addColumn(
                RequestOrderUpdate::REQUEST_DATA,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => false
                ],
                'Request Data'
            )
            ->addColumn(
                RequestOrderUpdate::FAILED,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'default' => false
                ],
                'Request Failed'
            )
            ->addColumn(
                RequestOrderUpdate::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => Table::TIMESTAMP_INIT
                ],
                'Order Updated At'
            )
            ->addColumn(
                RequestOrderUpdate::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => Table::TIMESTAMP_INIT_UPDATE
                ],
                'Request Sent At'
            )
            ->addForeignKey(
                $setup->getFkName(RequestOrderUpdate::TABLE, RequestOrderUpdate::ORDER_ID, self::ORDER_TABLE, self::ORDER_KEY),
                RequestOrderUpdate::ORDER_ID,
                self::ORDER_TABLE,
                self::ORDER_KEY,
                Table::ACTION_NO_ACTION
            )
            ->setOption('charset', 'utf8')
            ->setComment('Requests for Order Updates');

        $setup->getConnection()->createTable($table);
    }

    private function insertMagentoEduzzStatus(SchemaSetupInterface $setup)
    {
        foreach(MagentoEduzzStatus::LABEL_MAP as $statusEduzz => $label) {


            $this->status
                ->setData('status', $statusEduzz)
                ->setData('label', $label)->save();

            $this->status->assignState(MagentoEduzzStatus::STATE_MAP[$statusEduzz], true);
        }
    }

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->createTableRequestOrderUpdate($setup);
        $this->insertMagentoEduzzStatus($setup);

        $setup->endSetup();
    }
}
