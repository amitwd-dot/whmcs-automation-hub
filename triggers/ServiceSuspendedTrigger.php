<?php

namespace AutomationHub\Triggers;

use AutomationHub\Lib\TriggerInterface;

/**
 * Class ServiceSuspendedTrigger
 *
 * Fires when a hosting service is suspended in WHMCS.
 * Hook: ServiceSuspended
 *
 * @package AutomationHub\Triggers
 * @author Web Wave Digital <https://webwavedigital.co.in>
 */
class ServiceSuspendedTrigger implements TriggerInterface
{
    public function getKey(): string
    {
        return 'service_suspended';
    }

    public function getName(): string
    {
        return 'Service Suspended';
    }

    public function getDescription(): string
    {
        return 'Fires when a client service or hosting account is suspended (e.g. for non-payment or abuse).';
    }

    public function getHookName(): string
    {
        return 'ServiceSuspended';
    }

    public function getPayload(array $hookParams): array
    {
        return [
            'event'          => 'service_suspended',
            'service_id'     => (int)($hookParams['serviceid'] ?? 0),
            'user_id'        => (int)($hookParams['userid'] ?? 0),
            'domain'         => (string)($hookParams['domain'] ?? ''),
            'reason'         => (string)($hookParams['suspendreason'] ?? 'Non-payment'),
            'timestamp'      => date('c'),
        ];
    }

    public function getSamplePayload(): array
    {
        return [
            'event'          => 'service_suspended',
            'service_id'     => 342,
            'user_id'        => 55,
            'domain'         => 'client-domain.net',
            'reason'         => 'Overdue invoice #10492',
            'timestamp'      => date('c'),
        ];
    }
}
