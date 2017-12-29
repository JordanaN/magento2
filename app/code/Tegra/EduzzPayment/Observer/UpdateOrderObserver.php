<?php

namespace Tegra\EduzzPayment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

use Tegra\EduzzPayment\Service\EduzzService;
use Tegra\EduzzPayment\Service\ValidationService;

class UpdateOrderObserver implements ObserverInterface
{
    private $eduzzService;
    private $validationService;

    public function __construct(
        EduzzService $eduzzService,
        ValidationService $validationService
    ) {
        $this->eduzzService = $eduzzService;
        $this->validationService = $validationService;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (!$this->validationService->validatePaymentMethod($order) ||
            $this->validationService->validateProgramaticaly($order)) {

            return;
        }

        $this->eduzzService->updateOrder($order);
    }
}
