<?php

namespace Tegra\EduzzPayment\Service;

use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Zend_Http_Response;

use Magento\Sales\Model\Order;

use Tegra\EduzzPayment\Helper\ConfigHelper;

class EduzzService
{
    private $configHelper;
    private $requestUpdateOrderService;

    private $clientFactory;

    public function __construct(
        ConfigHelper $configHelper,
        RequestOrderUpdateService $requestUpdateOrderService,
        ZendClientFactory $clientFactory
    ) {
        $this->configHelper = $configHelper;
        $this->requestUpdateOrderService = $requestUpdateOrderService;

        $this->clientFactory = $clientFactory;
    }

    private function setUpdataData(Order $order) : array
    {
        return [
            'id' => $order->getId(),
            'order' => $order->getData()
        ];
    }

    private function setRegisterData(Order $order) : array
    {
        return [
            'id' => $order->getId(),
            'order' => $order->getData(),
            'urlRedirect' => $this->configHelper->getOwnBaseUrl(),
            'urlPost' => $this->configHelper->getOwnPostUrl(),
            'urlAdvertising' => $this->configHelper->getAdvertisingUrl()
        ];
    }

    private function postOrder(array $data, string $url) : Zend_Http_Response
    {
        return $this->clientFactory
            ->create()
            ->setHeaders('PublicKey', $this->configHelper->getPublicKey())
            ->setHeaders('PrivateKey', $this->configHelper->getPrivateKey())
            ->setRawData(json_encode($data), 'application/json')
            ->setUri($url)
            ->setMethod(ZendClient::POST)
            ->request();
    }

    private function getRedirectUrl(Zend_Http_Response $response)
    {
        $body = json_decode($response->getBody());

        return empty($body->redirectUrl)
            ? ''
            : $body->redirectUrl;
    }

    public function registerOrder(Order $order) : string
    {
        $data = $this->setRegisterData($order);
        $url  = $this->configHelper->getEduzzRegisterUrl();

        $response = $this->postOrder($data, $url);

        return $this->getRedirectUrl($response);
    }

    public function updateOrder(Order $order)
    {
        $data = $this->setUpdataData($order);
        $url  = $this->configHelper->getEduzzUpdateUrl();

        $response = $this->postOrder($data, $url);

        $this->requestUpdateOrderService->save(
            $order->getId(),
            $data,
            $response->isSuccessful()
        );
    }
}
