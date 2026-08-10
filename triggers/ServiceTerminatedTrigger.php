<?php

namespace AutomationHub\Triggers;

use AutomationHub\Lib\TriggerInterface;

/**
 * Class ServiceTerminatedTrigger
 *
 * Fires when a hosting service is terminated in WHMCS.
 * Hook: ServiceTerminated
 *
 * @package AutomationHub\Triggers
 * @author Web Wave Digital <https://webwavedigital.co.in>
 */
class ServiceTerminatedTrigger implements TriggerInterface
{
    public function getKey(): string
    {
        return 'service_terminated';
    }

    public function getName(): string
    {
        return 'Service Terminated';
    }

    public function getDescription(): string
    {
        return 'Fires when a service is permanently terminated and removed from the server.';
    }

    public function getHookName(): string
    {
        return 'ServiceTerminated';
    }

    public function getPayload(array $hookParams): array
    {
        return [
            'event'      => 'service_terminated',
            'service_id' => (int)($hookParams['serviceid'] ?? 0),
            'user_id'    => (int)($hookParams['userid'] ?? 0),
            'domain'     => (string)($hookParams['domain'] ?? ''),
            'timestamp'  => date('c'),
        ];
    }

    public function getSamplePayload(): array
    {
        return [
            'event'      => 'service_terminated',
            'service_id' => 310,
            'user_id'    => 19,
            'domain'     => 'expired-account.com',
            'timestamp'  => date('c'),
        ];
    }
}
