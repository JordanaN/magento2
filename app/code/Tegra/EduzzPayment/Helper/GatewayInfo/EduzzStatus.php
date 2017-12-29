<?php

namespace Tegra\EduzzPayment\Helper\GatewayInfo;

interface EduzzStatus
{
    const OPEN = 1;
    const PAID = 3;
    const CANCELED = 4;
    const WAITING_REFUND = 6;
    const REFUND = 7;
    const ANALYZING = 8;
    const RECOVERING = 11;
    const COMMISSION = 13;
}
