<?php

namespace Tegra\EduzzPayment\Helper\GatewayInfo;

interface EduzzPaymentType
{
    const INVOICE = 1;
    const PAYPAL = 9;
    const VISA = 13;
    const AMEX = 14;
    const MASTERCARD = 15;
    const DINERS = 16;
    const BANCO_BRASIL = 17;
    const BRADESCO = 18;
    const ITAU = 19;
    const HIPER = 23;
    const ELO = 24;
    const PAYPAL_INTERNATIONAL = 25;
}
