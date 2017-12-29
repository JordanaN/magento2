<?php

namespace Tegra\EduzzPayment\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Tegra\EduzzPayment\Controller\Redirect\Eduzz;
use Tegra\EduzzPayment\Helper\GatewayInfo\EduzzApi;
use Tegra\EduzzPayment\Helper\GatewayInfo\EduzzStatus;
use Tegra\EduzzPayment\Helper\GatewayInfo\EduzzPaymentType;

class ConfigHelper extends AbstractHelper
{

    const OWN_POST = '/eduzz/update/order';

    private $scope;
    private $storeManager;

    public function __construct(
        ScopeConfigInterface $scope,
        StoreManagerInterface $storeManager
    ) {
        $this->scope = $scope;
        $this->storeManager = $storeManager;
    }

    private function get(string $parameter, $default = null)
    {
        $value = $this->scope->getValue("payment/tegra_eduzzpayment/{$parameter}", 'stores');
        if (empty($value)) {
            return $default;
        }
        return $value;
    }

    public function isActive(bool $default = false) : bool
    {
        return $this->get('active', $default);
    }

    public function getPublicKey(string $default = '') : string
    {
        return $this->get('public_eduzz_key', $default);
    }

    public function getPrivateKey(string $default = '') : string
    {
        return $this->get('private_eduzz_key', $default);
    }

    public function getTitle(string $default = '') : string
    {
        return $this->get('title', $default);
    }

    public function getAdvertisingUrl(string $default = '') : string
    {
        return $this->get('advertising_url', $default);
    }

    public function getEduzzBaseUrl() : string
    {
        return EduzzApi::BASE_URL;
    }

    public function getEduzzRegisterUrl() : string
    {
        return EduzzApi::REGISTER_URL;
    }

    public function getEduzzUpdateUrl() : string
    {
        return EduzzApi::UPDATE_URL;
    }

    public function getOwnBaseUrl(string $default = '') : string
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        return empty($baseUrl)
            ? $default
            : trim($baseUrl, '/');
    }

    public function getOwnPostUrl(string $default = '') : string
    {
        $baseUrl = $this->getOwnBaseUrl();
        return empty($baseUrl)
            ? $default
            : $baseUrl . self::OWN_POST;
    }

    public function getValidOrderStatus() : array
    {
        $invoiceStatusReflection = new \ReflectionClass(EduzzStatus::class);
        return $invoiceStatusReflection->getConstants();
    }

    public function getValidPaymentTypes() : array
    {
        $paymentTypeReflection = new \ReflectionClass(EduzzPaymentType::class);
        return $paymentTypeReflection->getConstants();
    }
}
