<?php

namespace AutomationHub\Triggers;

use AutomationHub\Lib\TriggerInterface;

/**
 * Class InvoicePaymentFailedTrigger
 *
 * Fires when an invoice payment attempt fails in WHMCS.
 * Hook: InvoicePaymentFailed
 *
 * @package AutomationHub\Triggers
 * @author Web Wave Digital <https://webwavedigital.co.in>
 */
class InvoicePaymentFailedTrigger implements TriggerInterface
{
    public function getKey(): string
    {
        return 'invoice_payment_failed';
    }

    public function getName(): string
    {
        return 'Invoice Payment Failed';
    }

    public function getDescription(): string
    {
        return 'Fires when an automated or manual payment attempt fails for an invoice.';
    }

    public function getHookName(): string
    {
        return 'InvoicePaymentFailed';
    }

    public function getPayload(array $hookParams): array
    {
        return [
            'event'          => 'invoice_payment_failed',
            'invoice_id'     => (int)($hookParams['invoiceid'] ?? 0),
            'user_id'        => (int)($hookParams['userid'] ?? 0),
            'payment_method' => (string)($hookParams['paymentmethod'] ?? ''),
            'error_gateway'  => (string)($hookParams['gateway'] ?? ''),
            'timestamp'      => date('c'),
        ];
    }

    public function getSamplePayload(): array
    {
        return [
            'event'          => 'invoice_payment_failed',
            'invoice_id'     => 10594,
            'user_id'        => 104,
            'payment_method' => 'stripe',
            'error_gateway'  => 'Card Declined (Insufficent Funds)',
            'timestamp'      => date('c'),
        ];
    }
}
