<?php

namespace AutomationHub\Triggers;

use AutomationHub\Lib\TriggerInterface;

/**
 * Class OrderPlacedTrigger
 *
 * Fires when a new order is placed in WHMCS via shopping cart checkout.
 * Hook: AfterShoppingCartCheckout
 *
 * @package AutomationHub\Triggers
 * @author Web Wave Digital <https://webwavedigital.co.in>
 */
class OrderPlacedTrigger implements TriggerInterface
{
    public function getKey(): string
    {
        return 'order_placed';
    }

    public function getName(): string
    {
        return 'New Order Placed';
    }

    public function getDescription(): string
    {
        return 'Fires immediately after a client completes checkout and places a new order.';
    }

    public function getHookName(): string
    {
        return 'AfterShoppingCartCheckout';
    }

    public function getPayload(array $hookParams): array
    {
        $orderId = (int)($hookParams['OrderID'] ?? $hookParams['orderid'] ?? 0);
        $orderNum = (string)($hookParams['OrderNumber'] ?? $hookParams['ordernumber'] ?? '');

        return [
            'event'          => 'order_placed',
            'order_id'       => $orderId,
            'order_number'   => $orderNum,
            'user_id'        => (int)($hookParams['UserID'] ?? $hookParams['userid'] ?? 0),
            'invoice_id'     => (int)($hookParams['InvoiceID'] ?? $hookParams['invoiceid'] ?? 0),
            'payment_method' => (string)($hookParams['PaymentMethod'] ?? ''),
            'amount'         => (float)($hookParams['Total'] ?? $hookParams['amount'] ?? 0.00),
            'products'       => $hookParams['ServiceIDs'] ?? $hookParams['products'] ?? [],
            'domains'        => $hookParams['DomainIDs'] ?? $hookParams['domains'] ?? [],
            'timestamp'      => date('c'),
        ];
    }

    public function getSamplePayload(): array
    {
        return [
            'event'          => 'order_placed',
            'order_id'       => 1024,
            'order_number'   => 'ORD-982341',
            'user_id'        => 42,
            'invoice_id'     => 5081,
            'payment_method' => 'stripe',
            'amount'         => 149.00,
            'products'       => ['Business Cloud Hosting', 'SSL Certificate'],
            'domains'        => ['example-domain.com'],
            'timestamp'      => date('c'),
        ];
    }
}
