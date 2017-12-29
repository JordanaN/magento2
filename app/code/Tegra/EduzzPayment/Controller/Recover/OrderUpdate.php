<?php

namespace Tegra\EduzzPayment\Controller\Recover;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

use Magento\Framework\Controller\ResultFactory;

use Tegra\EduzzPayment\Service\ValidationService;
use Tegra\EduzzPayment\Service\RequestOrderUpdateService;

class OrderUpdate extends Action
{
    const OK = 200;
    const UNAUTHORIZED = 403;

    private $validationService;
    private $requestOrderUpdateService;

    public function __construct(
        Context $context,
        ValidationService $validationService,
        RequestOrderUpdateService $requestOrderUpdateService
    ) {
        parent::__construct($context);

        $this->validationService = $validationService;
        $this->requestOrderUpdateService = $requestOrderUpdateService;
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

    private function response(array $response)
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $result->setData($response);
    }

    public function execute()
    {
        $parameters = $this->getParameters();

//        if (!$this->validationService->validateEduzz()) {
//            return $this->setStatusCode(self::UNAUTHORIZED);
//        }

        $requests = $this->requestOrderUpdateService->getFailedRequests();
        return $this->response($requests);
    }
}
