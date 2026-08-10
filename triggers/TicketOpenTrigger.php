<?php

namespace AutomationHub\Triggers;

use AutomationHub\Lib\TriggerInterface;

/**
 * Class TicketOpenTrigger
 *
 * Fires when a new support ticket is opened in WHMCS.
 * Hook: TicketOpen
 *
 * @package AutomationHub\Triggers
 * @author Web Wave Digital <https://webwavedigital.co.in>
 */
class TicketOpenTrigger implements TriggerInterface
{
    public function getKey(): string
    {
        return 'ticket_open';
    }

    public function getName(): string
    {
        return 'Support Ticket Opened';
    }

    public function getDescription(): string
    {
        return 'Fires when a client or admin creates a new support ticket.';
    }

    public function getHookName(): string
    {
        return 'TicketOpen';
    }

    public function getPayload(array $hookParams): array
    {
        return [
            'event'         => 'ticket_open',
            'ticket_id'     => (int)($hookParams['ticketid'] ?? 0),
            'ticket_mask'   => (string)($hookParams['ticketmask'] ?? ''),
            'user_id'       => (int)($hookParams['userid'] ?? 0),
            'department_id' => (int)($hookParams['deptid'] ?? 0),
            'dept_name'     => (string)($hookParams['deptname'] ?? ''),
            'subject'       => (string)($hookParams['subject'] ?? ''),
            'message'       => (string)($hookParams['message'] ?? ''),
            'priority'      => (string)($hookParams['priority'] ?? 'Medium'),
            'timestamp'     => date('c'),
        ];
    }

    public function getSamplePayload(): array
    {
        return [
            'event'         => 'ticket_open',
            'ticket_id'     => 7819,
            'ticket_mask'   => 'ABC-102938',
            'user_id'       => 120,
            'department_id' => 1,
            'dept_name'     => 'Technical Support',
            'subject'       => 'Urgent: Unable to access cPanel control panel',
            'message'       => 'Hello team, I am receiving a 500 internal server error when navigating to cPanel.',
            'priority'      => 'High',
            'timestamp'     => date('c'),
        ];
    }
}
