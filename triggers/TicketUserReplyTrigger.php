<?php

namespace AutomationHub\Triggers;

use AutomationHub\Lib\TriggerInterface;

/**
 * Class TicketUserReplyTrigger
 *
 * Fires when a client posts a reply to an existing support ticket.
 * Hook: TicketUserReply
 *
 * @package AutomationHub\Triggers
 * @author Web Wave Digital <https://webwavedigital.co.in>
 */
class TicketUserReplyTrigger implements TriggerInterface
{
    public function getKey(): string
    {
        return 'ticket_user_reply';
    }

    public function getName(): string
    {
        return 'Ticket Client Reply';
    }

    public function getDescription(): string
    {
        return 'Fires when a client responds to an active support ticket.';
    }

    public function getHookName(): string
    {
        return 'TicketUserReply';
    }

    public function getPayload(array $hookParams): array
    {
        return [
            'event'       => 'ticket_user_reply',
            'ticket_id'   => (int)($hookParams['ticketid'] ?? 0),
            'reply_id'    => (int)($hookParams['replyid'] ?? 0),
            'user_id'     => (int)($hookParams['userid'] ?? 0),
            'subject'     => (string)($hookParams['subject'] ?? ''),
            'message'     => (string)($hookParams['message'] ?? ''),
            'priority'    => (string)($hookParams['priority'] ?? 'Medium'),
            'timestamp'   => date('c'),
        ];
    }

    public function getSamplePayload(): array
    {
        return [
            'event'       => 'ticket_user_reply',
            'ticket_id'   => 7819,
            'reply_id'    => 14920,
            'user_id'     => 120,
            'subject'     => 'Re: Urgent: Unable to access cPanel control panel',
            'message'     => 'I checked again after clearing my DNS cache and the issue persists.',
            'priority'    => 'High',
            'timestamp'   => date('c'),
        ];
    }
}
