<?php

namespace AutomationHub\Triggers;

use AutomationHub\Lib\TriggerInterface;

/**
 * Class ServiceUnsuspendedTrigger
 *
 * Fires when a hosting service is unsuspended in WHMCS.
 * Hook: ServiceUnsuspended
 *
 * @package AutomationHub\Triggers
 * @author Web Wave Digital <https://webwavedigital.co.in>
 */
class ServiceUnsuspendedTrigger implements TriggerInterface
{
    public function getKey(): string
    {
        return 'service_unsuspended';
    }

    public function getName(): string
    {
        return 'Service Unsuspended';
    }

    public function getDescription(): string
    {
        return 'Fires when a previously suspended service is restored and unsuspended.';
    }

    public function getHookName(): string
    {
        return 'ServiceUnsuspended';
    }

    public function getPayload(array $hookParams): array
    {
        return [
            'event'      => 'service_unsuspended',
            'service_id' => (int)($hookParams['serviceid'] ?? 0),
            'user_id'    => (int)($hookParams['userid'] ?? 0),
            'domain'     => (string)($hookParams['domain'] ?? ''),
            'timestamp'  => date('c'),
        ];
    }

    public function getSamplePayload(): array
    {
        return [
            'event'      => 'service_unsuspended',
            'service_id' => 342,
            'user_id'    => 55,
            'domain'     => 'client-domain.net',
            'timestamp'  => date('c'),
        ];
    }
}
