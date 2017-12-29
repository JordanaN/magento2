<?php

namespace Tegra\EduzzPayment\Model;

use Magento\Framework\Model\AbstractModel;

use Tegra\EduzzPayment\Model\Resource\RequestOrderUpdate as Resource;

class RequestOrderUpdate extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(Resource::class);
    }
}
