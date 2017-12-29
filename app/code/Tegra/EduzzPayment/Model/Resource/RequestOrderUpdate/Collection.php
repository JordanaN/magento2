<?php

namespace Tegra\EduzzPayment\Model\Resource\RequestOrderUpdate;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

use Tegra\EduzzPayment\Model\RequestOrderUpdate;
use Tegra\EduzzPayment\Model\Resource\RequestOrderUpdate as Resource;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(RequestOrderUpdate::class, Resource::class);
    }
}
