<?php

namespace Tegra\EduzzPayment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

use Tegra\EduzzPayment\Service\OrderService;
use Tegra\EduzzPayment\Service\ValidationService;

class PlaceOrderObserver implements ObserverInterface
{
    private $orderService;
    private $validationService;

    public function __construct(
        OrderService $orderService,
        ValidationService $validationService
    ) {
        $this->orderService = $orderService;
        $this->validationService = $validationService;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if (!$this->validationService->validatePaymentMethod($order)) {
            return;
        }

        $this->orderService->setStatus($order, 'new', 'pending');
    }
}
