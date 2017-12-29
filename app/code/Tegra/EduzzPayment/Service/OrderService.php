<?php

namespace Tegra\EduzzPayment\Service;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory;

use Magento\Sales\Model\Order;

use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Order\Payment\Transaction;

use Tegra\EduzzPayment\Helper\ConfigHelper;
use Tegra\EduzzPayment\Helper\GatewayInfo\EduzzApi;
use Tegra\EduzzPayment\Helper\GatewayInfo\EduzzStatus;
use Tegra\EduzzPayment\Helper\GatewayInfo\MagentoEduzzStatus;

class OrderService
{
    /** Order Status */
    const PENDING = 'pending';

    /** Order State */
    const NEW        = Order::STATE_NEW;
    const PROCESSING = Order::STATE_PROCESSING;
    const CANCELED   = Order::STATE_CANCELED;
    const CLOSED     = Order::STATE_CLOSED;
    const COMPLETE   = Order::STATE_COMPLETE;

    /** Transaction Type */
    const CAPTURE = Transaction::TYPE_CAPTURE;
    const REFUND  = Transaction::TYPE_REFUND;

    /** Transaction Column */
    const TRANSACTION_ID = TransactionInterface::TRANSACTION_ID;

    const TO_STATUS_METHODS = [
        EduzzStatus::OPEN => 'toOpen',
        EduzzStatus::PAID => 'toPaid',
        EduzzStatus::CANCELED => 'toCanceled',
        EduzzStatus::WAITING_REFUND => 'toWaitingRefund',
        EduzzStatus::ANALYZING => 'toAnalyzing',
        EduzzStatus::RECOVERING => 'toRecovering',
        EduzzStatus::REFUND => 'toRefund',
        EduzzStatus::COMMISSION => 'toCommission'
    ];

    private $transactionBuilder;
    private $orderInterface;
    private $transactionSearchFactory;


    private $configHelper;
    private $validationService;

    public function __construct(
        BuilderInterface $transactionBuilder,
        OrderInterface $orderInterface,
        TransactionSearchResultInterfaceFactory $transactionSearchFactory,
        ValidationService $validationService,
        ConfigHelper $configHelper
    ) {
        $this->transactionBuilder = $transactionBuilder;
        $this->orderInterface = $orderInterface;
        $this->transactionSearchFactory = $transactionSearchFactory;

        $this->configHelper = $configHelper;
        $this->validationService = $validationService;
    }

    public function setStatus(Order $order, string $state = self::NEW, string $status = self::PENDING) : Order
    {
        $order->isProgrammatically = true;

        $order
            ->setStatus($status)
            ->setState($state)
            ->save();

        return $order;
    }

    public function updateOrder(array $parameters)
    {
        $orderId = $parameters[EduzzApi::ORDER_ID];
        $status = $parameters[EduzzApi::ORDER_STATUS];
        $transactionId = $parameters[EduzzApi::TRANS_ID];

        $order = $this->orderInterface->load($orderId);

        if (!$this->validationService->validateOrderExist($order)) {
            throw new \Exception('Order');
        }

        $order->isProgramaticaly = true;
        $this->{self::TO_STATUS_METHODS[$status]}($order, $transactionId, $parameters);
    }

    private function formatAdditionalInfo(array $information) : array
    {
        return $information;
    }

    private function findByTxnId(Order $order, int $txnId)
    {
        $transactions = $this->transactionSearchFactory
            ->create()
            ->addOrderIdFilter($order->getId())
            ->addTxnTypeFilter(self::REFUND)
            ->getItems();

        $transactions = array_filter($transactions, function($transaction) use ($txnId) {
            return $transaction->getTxnId() == $txnId;
        });

        $transactions = array_values($transactions);
        return empty($transactions)
            ? null
            : $transactions[0];
    }

    private function createTransaction(Order $order,
                                       string $type,
                                       int $transactionId,
                                       array $information = [],
                                       bool $isClosed = true) : Transaction
    {
        $payment = $order->getPayment();
        $parentId = $payment->getLastTransId();

        $payment->setLastTransId($transactionId);
        $payment->setTransactionId($transactionId);
        $payment->setAdditionalInformation([Transaction::RAW_DETAILS => $this->formatAdditionalInfo($information) ]);

        $transaction = $this->transactionBuilder
            ->setPayment($payment)
            ->setOrder($order)
            ->setTransactionId($transactionId)
            ->setFailSafe(true)
            ->build($type);

        $payment->setParentTransactionId($parentId);

        $payment->save();
        $order->save();

        return $transaction->setIsClosed($isClosed)->save();
    }

    private function closeTransaction(Order $order, int $transactionId) : Transaction
    {
        $transaction = $this->findByTxnId($order, $transactionId);

        if (is_null($transaction)) {
            throw new \Exception('Transaction');
        }

        return $transaction->setIsClosed(true)->save();
    }

    private function toOpen(Order $order)
    {
        $this->setStatus($order, self::PROCESSING, MagentoEduzzStatus::OPEN);
    }

    private function toPaid(Order $order, $transactionId, array $parameters = [])
    {
        $this->setStatus($order, self::PROCESSING, MagentoEduzzStatus::PAID);
        $this->createTransaction($order, self::CAPTURE, $transactionId, $parameters);
    }

    private function toCanceled(Order $order)
    {
        $this->setStatus($order, self::CANCELED, MagentoEduzzStatus::CANCELED);
    }

    private function toWaitingRefund(Order $order, int $transactionId, array $parameters = [])
    {
        $this->createTransaction($order, self::REFUND, $transactionId, $parameters, false);
        $this->setStatus($order, self::PROCESSING, MagentoEduzzStatus::WAITING_REFUND);
    }

    private function toRefund(Order $order, int $transactionId)
    {
        $this->closeTransaction($order, $transactionId);
        $this->setStatus($order, self::CLOSED, MagentoEduzzStatus::REFUND);
    }

    private function toAnalyzing(Order $order)
    {
        $this->setStatus($order, self::PROCESSING, MagentoEduzzStatus::ANALYZING);
    }

    private function toRecovering(Order $order)
    {
        $this->setStatus($order, self::PROCESSING, MagentoEduzzStatus::RECOVERING);
    }

    private function toCommission(Order $order)
    {
        $this->setStatus($order, self::COMPLETE, MagentoEduzzStatus::COMMISSION);
    }
}
