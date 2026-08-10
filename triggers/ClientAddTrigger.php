<?php

namespace AutomationHub\Triggers;

use AutomationHub\Lib\TriggerInterface;

/**
 * Class ClientAddTrigger
 *
 * Fires when a new client account is registered in WHMCS.
 * Hook: ClientAdd
 *
 * @package AutomationHub\Triggers
 * @author Web Wave Digital <https://webwavedigital.co.in>
 */
class ClientAddTrigger implements TriggerInterface
{
    public function getKey(): string
    {
        return 'client_add';
    }

    public function getName(): string
    {
        return 'New Client Registered';
    }

    public function getDescription(): string
    {
        return 'Fires immediately after a new client completes account registration.';
    }

    public function getHookName(): string
    {
        return 'ClientAdd';
    }

    public function getPayload(array $hookParams): array
    {
        return [
            'event'        => 'client_add',
            'user_id'      => (int)($hookParams['user_id'] ?? $hookParams['userid'] ?? 0),
            'first_name'   => (string)($hookParams['firstname'] ?? ''),
            'last_name'    => (string)($hookParams['lastname'] ?? ''),
            'company_name' => (string)($hookParams['companyname'] ?? ''),
            'email'        => (string)($hookParams['email'] ?? ''),
            'country'      => (string)($hookParams['country'] ?? ''),
            'created_at'   => date('c'),
            'timestamp'    => date('c'),
        ];
    }

    public function getSamplePayload(): array
    {
        return [
            'event'        => 'client_add',
            'user_id'      => 150,
            'first_name'   => 'Alex',
            'last_name'    => 'Morgan',
            'company_name' => 'Nexus Innovations Ltd',
            'email'        => 'alex.morgan@example.com',
            'country'      => 'US',
            'created_at'   => date('c'),
            'timestamp'    => date('c'),
        ];
    }
}
