<?php

namespace Tegra\EduzzPayment\Helper\GatewayInfo;

interface EduzzApi
{
    const BASE_URL = 'http://10.84.77.148:9000';
    const REGISTER_URL = self::BASE_URL . '/json';
    const UPDATE_URL = self::BASE_URL . '/receive';

    const ORDER_ID = 'trans_cod';
    const ORDER_STATUS = 'trans_status';
    const TRANS_ID = 'trans_id';
}
