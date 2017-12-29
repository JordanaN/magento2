<?php

namespace Tegra\EduzzPayment\Controller\Update;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

use Magento\Sales\Api\Data\OrderInterface;

use Tegra\EduzzPayment\Helper\ConfigHelper;
use Tegra\EduzzPayment\Service\OrderService;
use Tegra\EduzzPayment\Service\ValidationService;

class Order extends Action
{
    const OK = 200;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 403;
    const NOT_FOUND = 404;

    private $configHelper;

    private $validationService;
    private $orderService;

    public function __construct(
        Context $context,
        ConfigHelper $configHelper,
        ValidationService $validationService,
        OrderService $orderService
    ) {
        parent::__construct($context);

        $this->configHelper = $configHelper;

        $this->orderService = $orderService;

        $this->validationService = $validationService;
        $this->orderService = $orderService;
    }

    private function setStatusCode(int $code = self::OK)
    {
        $this->getResponse()->setHttpResponseCode($code);
    }

    private function getParameters() : array
    {
        return (array) json_decode(file_get_contents('php://input'));
        // return $this->getRequest()->getPost()->toArray();
    }

    public function execute()
    {
        $parameters = $this->getParameters();

        if (!$this->validationService->validateEduzz($parameters)) {
            return $this->setStatusCode(self::UNAUTHORIZED);
        }
        if (!$this->validationService->validateRequestParameters($parameters)) {
            return $this->setStatusCode(self::BAD_REQUEST);
        }

        try {
            $this->orderService->updateOrder($parameters);
        } catch(\Exception $e) {
            return $this->setStatusCode(self::NOT_FOUND);
        }

        return $this->setStatusCode(self::OK);
    }
}
