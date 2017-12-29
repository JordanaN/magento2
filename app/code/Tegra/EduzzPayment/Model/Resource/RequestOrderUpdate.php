<?php

namespace Tegra\EduzzPayment\Model\Resource;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class RequestOrderUpdate extends AbstractDb
{
    const TABLE = 'eduzz_request_order_update';

    const ID = 'id';

    const ORDER_ID = 'order_id';

    const REQUEST_DATA = 'request_data';
    const FAILED = 'failed';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    const PRIMARY_KEY = self::ID;
    const FOREIGN_KEY = self::ORDER_ID;

    protected function _construct()
    {
        $this->_init(self::TABLE, self::PRIMARY_KEY);
    }
}
