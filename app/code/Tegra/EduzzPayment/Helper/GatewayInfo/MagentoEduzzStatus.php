<?php

namespace Tegra\EduzzPayment\Helper\GatewayInfo;

use Magento\Sales\Model\Order;

interface MagentoEduzzStatus
{
    const OPEN = 'open';
    const PAID = 'paid';
    const CANCELED = 'canceled';
    const WAITING_REFUND = 'waiting_refund';
    const REFUND = 'refund';
    const ANALYZING = 'analyzing';
    const RECOVERING = 'recovering';
    const COMMISSION = 'commission';

    const LABEL_MAP = [
        self::OPEN => 'Open',
        self::PAID => 'Paid',
        self::CANCELED => 'Canceled',
        self::WAITING_REFUND => 'Waiting Refund',
        self::REFUND => 'Refund',
        self::ANALYZING => 'Analyzing',
        self::RECOVERING => 'Recovering',
        self::COMMISSION => 'Commission'
    ];

    const STATE_MAP = [
        self::OPEN => Order::STATE_PROCESSING,
        self::PAID => Order::STATE_PROCESSING,
        self::CANCELED => Order::STATE_CANCELED,
        self::WAITING_REFUND => Order::STATE_PROCESSING,
        self::REFUND => Order::STATE_CLOSED,
        self::ANALYZING => Order::STATE_PAYMENT_REVIEW,
        self::RECOVERING => Order::STATE_PAYMENT_REVIEW,
        self::COMMISSION => Order::STATE_COMPLETE
    ];
}
