<?php

namespace Tegra\EduzzPayment\Service;

use Tegra\EduzzPayment\Model\RequestOrderUpdateFactory;
use Tegra\EduzzPayment\Model\Resource\RequestOrderUpdate;

class RequestOrderUpdateService
{
    private $requestOrderUpdateFactory;

    public function __construct(
        RequestOrderUpdateFactory $requestOrderUpdateFactory
    ) {
        $this->requestOrderUpdateFactory = $requestOrderUpdateFactory;
    }

    private function requestSuccessful(int $id)
    {
        $request = $this->requestOrderUpdateFactory
            ->create()
            ->load($id);

        $request->setData(RequestOrderUpdate::FAILED, false)->save();
    }

    public function save(int $orderId, array $data, bool $isSuccessful = true)
    {
        $register = [
            'order_id' => $orderId,
            'request_data' => json_encode($data),
            'failed' => !$isSuccessful
        ];

        $this->requestOrderUpdateFactory->create()->setData($register)->save();
    }

    public function getFailedRequests() : array
    {
        $requests = $this->requestOrderUpdateFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter(RequestOrderUpdate::FAILED, true)
            ->toArray();

        return array_map(function($request) {

            $this->requestSuccessful($request[RequestOrderUpdate::ID]);

            $data = $request[RequestOrderUpdate::REQUEST_DATA];
            return json_decode($data);
        }, $requests['items']);
    }
}
