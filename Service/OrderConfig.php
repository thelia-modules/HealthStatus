<?php

namespace HealthStatus\Service;

use Propel\Runtime\Exception\PropelException;
use Thelia\Model\ModuleQuery;
use Thelia\Model\OrderQuery;
use Thelia\Model\ProductQuery;

class OrderConfig
{
    /**
     * @throws PropelException
     */
    public function getLastOrder(): array
    {
        $lastOrder = OrderQuery::create()
            ->orderByCreatedAt('desc')
            ->findOne();

        if ($lastOrder) {
            $orderDate = $lastOrder->getCreatedAt();
            $orderDate = $orderDate->format('d/m/Y - H:i');
        } else {
            $orderDate = 'No orders found';
        }

        return [
            'lastOrderDate' => [
                'value' => $orderDate,
            ],
        ];

    }

    /**
     * @throws PropelException
     */
    public function getLastPaidOrder(): array
    {
        $lastOrderPaid = OrderQuery::create()
            ->filterByStatusId(2)
            ->orderByCreatedAt('desc')
            ->findOne();

        $orderDatePaid = 'No paid orders found';
        $paymentModule = 'No payment module found';

        if ($lastOrderPaid) {
            $orderDatePaid = $lastOrderPaid->getInvoiceDate()->format('d/m/Y - H:i');
            $paymentModuleId = $lastOrderPaid->getPaymentModuleId();

            if ($paymentModuleId !== null) {
                $paymentModule = ModuleQuery::create()
                    ->filterById($paymentModuleId)
                    ->findOne();

                if ($paymentModule) {
                    $paymentModule = $paymentModule->getTitle();
                }
            }
        }

        return [
            'lastPaidOrderDate' => [
                'value' => $orderDatePaid,
            ],
            'lastPaidPaymentModule' => [
                'value' => $paymentModule,
            ],
        ];
    }

    /**
     * @throws PropelException
     */
    public function getLastProductAdded(): array
    {
        $lastProductAdded = ProductQuery::create()
            ->orderByCreatedAt('desc')
            ->findOne();

        $productDate = 'No products found';

        if ($lastProductAdded) {
            $productDate = $lastProductAdded->getCreatedAt()->format('d/m/Y - H:i');
        }

        return [
            'lastProductAddedDate' => [
                'value' => $productDate,
            ],
        ];

    }

}