<?php

namespace Tegra\EduzzPayment\Controller\Redirect;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect\Interceptor;

use Magento\Framework\Controller\ResultFactory;


use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;


use Tegra\EduzzPayment\Helper\ConfigHelper;
use Tegra\EduzzPayment\Service\ValidationService;
use Tegra\EduzzPayment\Service\EduzzService;


class Eduzz extends Action
{
    private $orderInterface;

    private $configHelper;
    private $eduzzService;
    private $validationService;

    public function __construct(
        Context               $context,
        OrderInterface        $orderInterface,

        ConfigHelper $configHelper,
        EduzzService $eduzzService,
        ValidationService $validationService
    ) {
        parent::__construct($context);

        $this->orderInterface = $orderInterface;

        $this->configHelper = $configHelper;
        $this->eduzzService = $eduzzService;
        $this->validationService = $validationService;
    }

    private function getOrderByParam(string $param = 'id') : Order
    {
        $orderId = $this->getRequest()->getParam($param);
        return $this->orderInterface->load($orderId);
    }

    private function redirect(string $url) : Interceptor
    {
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirect->setUrl($url);
        return $redirect;
    }

    public function execute()
    {
        $order = $this->getOrderByParam();
        $baseUrl = $this->configHelper->getOwnBaseUrl();

        if (!$this->validationService->validateOrderExist($order)    ||
            !$this->validationService->validatePaymentMethod($order) ||
            !$this->validationService->validateCustomer($order)      ||
            !$this->validationService->validateStateNewOrder($order)) {

            return $this->redirect($baseUrl);
        }

        $redirectUrl = $this->eduzzService->registerOrder($order);

        return empty($redirectUrl)
            ? $this->redirect($baseUrl)
            : $this->redirect($redirectUrl);
    }
}
