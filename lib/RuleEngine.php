<?php

namespace AutomationHub\Lib;

use WHMCS\Database\Capsule;

/**
 * Class RuleEngine
 *
 * Core engine responsible for loading enabled automation rules, matching WHMCS events,
 * normalizing payloads via Triggers, executing Actions, and recording activity logs.
 *
 * @package AutomationHub\Lib
 * @author Web Wave Digital <https://webwavedigital.co.in>
 */
class RuleEngine
{
    /**
     * Handle a fired WHMCS hook event by executing matching rules.
     *
     * @param string $hookName Name of WHMCS hook e.g. 'InvoicePaid'
     * @param array $hookParams Raw parameter array from WHMCS hook
     * @return array Results summary for executed rules
     */
    public static function handleHook(string $hookName, array $hookParams = []): array
    {
        Registry::init();
        $triggers = Registry::getTriggersByHookName($hookName);

        if (empty($triggers)) {
            return [];
        }

        $results = [];

        foreach ($triggers as $trigger) {
            $rules = self::getEnabledRulesForTrigger($trigger->getKey());
            $payload = $trigger->getPayload($hookParams);

            foreach ($rules as $rule) {
                $results[] = self::executeRule($rule, $payload, false);
            }
        }

        return $results;
    }

    /**
     * Execute a specific rule using a provided or sample payload.
     *
     * @param object|array $rule Rule object or associative array from database
     * @param array $payload Normalized payload data
     * @param bool $isTest Whether this is a manual test execution
     * @return array Execution result details
     */
    public static function executeRule($rule, array $payload, bool $isTest = false): array
    {
        Registry::init();

        $ruleObj = (object)$rule;
        $ruleId = (int)$ruleObj->id;
        $ruleName = $ruleObj->name ?? 'Unnamed Rule';
        $triggerKey = $ruleObj->trigger_class ?? '';
        $actionKey = $ruleObj->action_class ?? '';

        $actionConfig = [];
        if (!empty($ruleObj->action_config)) {
            $actionConfig = is_array($ruleObj->action_config)
                ? $ruleObj->action_config
                : (json_decode($ruleObj->action_config, true) ?: []);
        }

        $action = Registry::getActionByKey($actionKey);

        $startTime = microtime(true);
        $status = 'success';
        $errorMessage = '';
        $actionOutput = [];

        if (!$action) {
            $status = 'failed';
            $errorMessage = "Action key '{$actionKey}' is not registered or supported.";
        } else {
            try {
                $actionResult = $action->execute($payload, $actionConfig);

                if (isset($actionResult['success']) && !$actionResult['success']) {
                    $status = 'failed';
                    $errorMessage = $actionResult['message'] ?? 'Action returned failure status.';
                }

                $actionOutput = $actionResult;
            } catch (\Throwable $e) {
                $status = 'failed';
                $errorMessage = "Exception during action execution: " . $e->getMessage();
            }
        }

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        // Record log entry
        self::logExecution([
            'rule_id'           => $ruleId,
            'rule_name'         => $ruleName . ($isTest ? ' (Test Run)' : ''),
            'trigger_class'     => $triggerKey,
            'action_class'      => $actionKey,
            'status'            => $status,
            'error_message'     => $errorMessage,
            'payload'           => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'execution_time_ms' => $durationMs,
            'created_at'        => date('Y-m-d H:i:s'),
        ]);

        // Update last_fired_at timestamp on rule if database is available
        if ($ruleId > 0 && !$isTest) {
            self::updateRuleLastFired($ruleId);
        }

        return [
            'rule_id'           => $ruleId,
            'rule_name'         => $ruleName,
            'status'            => $status,
            'error_message'     => $errorMessage,
            'execution_time_ms' => $durationMs,
            'action_output'     => $actionOutput,
            'payload'           => $payload,
        ];
    }

    /**
     * Trigger a manual rule test execution with sample or custom payload.
     *
     * @param int $ruleId ID of rule to test
     * @param array $customPayload Optional custom payload to override trigger sample
     * @return array Execution result
     * @throws \InvalidArgumentException If rule does not exist
     */
    public static function testRule(int $ruleId, array $customPayload = []): array
    {
        Registry::init();
        $rule = Capsule::table('mod_automationhub_rules')->where('id', $ruleId)->first();

        if (!$rule) {
            throw new \InvalidArgumentException("Rule ID {$ruleId} not found.");
        }

        $triggerKey = $rule->trigger_class;
        $trigger = Registry::getTriggerByKey($triggerKey);

        $payload = !empty($customPayload)
            ? $customPayload
            : ($trigger ? $trigger->getSamplePayload() : ['event' => 'test_event', 'timestamp' => date('c')]);

        return self::executeRule($rule, $payload, true);
    }

    /**
     * Retrieve all active rules configured for a specific trigger key.
     *
     * @param string $triggerKey Trigger key e.g. 'invoice_paid'
     * @return array List of rule objects
     */
    public static function getEnabledRulesForTrigger(string $triggerKey): array
    {
        try {
            return Capsule::table('mod_automationhub_rules')
                ->where('trigger_class', $triggerKey)
                ->where('enabled', 1)
                ->get()
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Save an execution log entry to the database.
     *
     * @param array $logData Log fields
     * @return void
     */
    private static function logExecution(array $logData): void
    {
        try {
            Capsule::table('mod_automationhub_logs')->insert($logData);
        } catch (\Throwable $e) {
            // Fail gracefully if database write fails during log insertion
        }
    }

    /**
     * Update last_fired_at timestamp for a rule.
     *
     * @param int $ruleId Rule ID
     * @return void
     */
    private static function updateRuleLastFired(int $ruleId): void
    {
        try {
            Capsule::table('mod_automationhub_rules')
                ->where('id', $ruleId)
                ->update(['last_fired_at' => date('Y-m-d H:i:s')]);
        } catch (\Throwable $e) {
            // Fail gracefully
        }
    }
}
