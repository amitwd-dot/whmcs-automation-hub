<?php

namespace AutomationHub\Triggers;

use AutomationHub\Lib\TriggerInterface;

/**
 * Class InvoicePaidTrigger
 *
 * Fires when an invoice status changes to Paid in WHMCS.
 * Hook: InvoicePaid
 *
 * @package AutomationHub\Triggers
 * @author Web Wave Digital <https://webwavedigital.co.in>
 */
class InvoicePaidTrigger implements TriggerInterface
{
    public function getKey(): string
    {
        return 'invoice_paid';
    }

    public function getName(): string
    {
        return 'Invoice Paid';
    }

    public function getDescription(): string
    {
        return 'Fires when a client or administrator successfully marks an invoice as Paid.';
    }

    public function getHookName(): string
    {
        return 'InvoicePaid';
    }

    public function getPayload(array $hookParams): array
    {
        return [
            'event'          => 'invoice_paid',
            'invoice_id'     => (int)($hookParams['invoiceid'] ?? $hookParams['InvoiceID'] ?? 0),
            'user_id'        => (int)($hookParams['userid'] ?? $hookParams['UserID'] ?? 0),
            'payment_method' => (string)($hookParams['paymentmethod'] ?? ''),
            'amount'         => (float)($hookParams['amountpaid'] ?? $hookParams['total'] ?? 0.00),
            'date_paid'      => date('c'),
            'timestamp'      => date('c'),
        ];
    }

    public function getSamplePayload(): array
    {
        return [
            'event'          => 'invoice_paid',
            'invoice_id'     => 10582,
            'user_id'        => 88,
            'payment_method' => 'paypal',
            'amount'         => 299.99,
            'date_paid'      => date('c'),
            'timestamp'      => date('c'),
        ];
    }
}
