<?php

namespace Tegra\EduzzPayment\Service;

use Magento\Customer\Model\Session;

use Magento\Sales\Model\Order;

use Tegra\EduzzPayment\Controller\Redirect\Eduzz;
use Tegra\EduzzPayment\Helper\ConfigHelper;
use Tegra\EduzzPayment\Helper\GatewayInfo\EduzzApi;

class ValidationService
{
    const STATE_NEW = 'new';
    const STATUS_PENDING = 'pending';

    private $configHelper;
    private $session;

    public function __construct(
        Session $session,
        ConfigHelper $configHelper

    ) {
        $this->session = $session;
        $this->configHelper = $configHelper;
    }

    public function validateOrderExist(Order $order) : bool
    {
        return !!$order->getId();
    }

    public function validateOrderRegistered(Order $order) : bool
    {
        return !!$order->getPayment()->getLastTransId();
    }

    public function validateCustomer(Order $order) : bool
    {
        if (!$order->getCustomerId() ||
            !$this->session->getCustomerId()) {
            return false;
        }

        $loggedCustomerId = $this->session->getCustomerId();
        $orderCustomerId  = $order->getCustomerId();

        return $loggedCustomerId === $orderCustomerId;

    }

    public function validatePaymentMethod(Order $order) : bool
    {
        $method = $order
            ->getPayment()
            ->getMethodInstance()
            ->getTitle();

        return $method === $this->configHelper->getTitle();
    }

    public function validateEduzz(array $parameters = []) : bool
    {
        if (empty($parameters['api_key'])) {
            return false;
        }

        return $this->configHelper->getPrivateKey() === $parameters['api_key'];
    }

    public function validateStateNewOrder(Order $order) : bool
    {
        $state = $order->getState();
        $status = $order->getStatus();

        return $state === self::STATE_NEW &&
               $status === self::STATUS_PENDING;
    }

    private function validateStatusOrder(array $parameters = []) : bool
    {
        if (empty($parameters[EduzzApi::ORDER_STATUS])) {
            return false;
        }
        $status = $parameters[EduzzApi::ORDER_STATUS];
        $validStatus = $this->configHelper->getValidOrderStatus();
        return !!array_search($status, $validStatus);
    }

    public function validateRequestParameters(array $parameters = []) : bool
    {
        return !empty($parameters[EduzzApi::ORDER_ID]) &&
               !empty($parameters[EduzzApi::TRANS_ID]) &&
               $this->validateStatusOrder($parameters);
    }

    public function validateProgramaticaly(Order $order) : bool
    {
        return isset($order->isProgramaticaly);
    }
}
