<?php
/**
 * WHMCS Automation Hub - Dynamic Hook Listener Registration
 *
 * Dynamically registers WHMCS hooks for enabled automation triggers
 * and routes events to the RuleEngine.
 *
 * @author Web Wave Digital <https://webwavedigital.co.in>
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

require_once __DIR__ . '/lib/TriggerInterface.php';
require_once __DIR__ . '/lib/ActionInterface.php';
require_once __DIR__ . '/lib/HttpClient.php';
require_once __DIR__ . '/lib/Registry.php';
require_once __DIR__ . '/lib/RuleEngine.php';

use AutomationHub\Lib\Registry;
use AutomationHub\Lib\RuleEngine;
use WHMCS\Database\Capsule;

// Helper to check if any active rule relies on a specific hook
function automationhub_has_active_rules_for_hook(string $hookName): bool
{
    try {
        Registry::init();
        $triggers = Registry::getTriggersByHookName($hookName);
        if (empty($triggers)) {
            return false;
        }

        $triggerKeys = array_map(function($t) { return $t->getKey(); }, $triggers);

        return Capsule::table('mod_automationhub_rules')
            ->whereIn('trigger_class', $triggerKeys)
            ->where('enabled', 1)
            ->exists();
    } catch (\Throwable $e) {
        return false;
    }
}

// 1. Order Placed Hook
add_hook('AfterShoppingCartCheckout', 1, function($vars) {
    if (automationhub_has_active_rules_for_hook('AfterShoppingCartCheckout')) {
        RuleEngine::handleHook('AfterShoppingCartCheckout', $vars);
    }
});

// 2. Invoice Paid Hook
add_hook('InvoicePaid', 1, function($vars) {
    if (automationhub_has_active_rules_for_hook('InvoicePaid')) {
        RuleEngine::handleHook('InvoicePaid', $vars);
    }
});

// 3. Invoice Payment Failed Hook
add_hook('InvoicePaymentFailed', 1, function($vars) {
    if (automationhub_has_active_rules_for_hook('InvoicePaymentFailed')) {
        RuleEngine::handleHook('InvoicePaymentFailed', $vars);
    }
});

// 4. Service Suspended Hook
add_hook('ServiceSuspended', 1, function($vars) {
    if (automationhub_has_active_rules_for_hook('ServiceSuspended')) {
        RuleEngine::handleHook('ServiceSuspended', $vars);
    }
});

// 5. Service Unsuspended Hook
add_hook('ServiceUnsuspended', 1, function($vars) {
    if (automationhub_has_active_rules_for_hook('ServiceUnsuspended')) {
        RuleEngine::handleHook('ServiceUnsuspended', $vars);
    }
});

// 6. Service Terminated Hook
add_hook('ServiceTerminated', 1, function($vars) {
    if (automationhub_has_active_rules_for_hook('ServiceTerminated')) {
        RuleEngine::handleHook('ServiceTerminated', $vars);
    }
});

// 7. Support Ticket Opened Hook
add_hook('TicketOpen', 1, function($vars) {
    if (automationhub_has_active_rules_for_hook('TicketOpen')) {
        RuleEngine::handleHook('TicketOpen', $vars);
    }
});

// 8. Support Ticket Client Reply Hook
add_hook('TicketUserReply', 1, function($vars) {
    if (automationhub_has_active_rules_for_hook('TicketUserReply')) {
        RuleEngine::handleHook('TicketUserReply', $vars);
    }
});

// 9. New Client Registered Hook
add_hook('ClientAdd', 1, function($vars) {
    if (automationhub_has_active_rules_for_hook('ClientAdd')) {
        RuleEngine::handleHook('ClientAdd', $vars);
    }
});

// 10. Daily Cron Job Hook (Domain Expiration Check)
add_hook('DailyCronJob', 1, function($vars) {
    if (automationhub_has_active_rules_for_hook('DailyCronJob')) {
        RuleEngine::handleHook('DailyCronJob', $vars);
    }
});
