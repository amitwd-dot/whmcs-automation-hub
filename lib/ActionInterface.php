<?php

namespace AutomationHub\Lib;

/**
 * Interface ActionInterface
 *
 * Pluggable interface representing an action executed when a rule fires.
 * Each action defines its configuration fields (for dynamic form rendering)
 * and the execution logic (e.g. sending a webhook, Slack message, Discord embed).
 *
 * @package AutomationHub\Lib
 * @author Web Wave Digital <https://webwavedigital.co.in>
 */
interface ActionInterface
{
    /**
     * Get the unique key identifying this action.
     *
     * @return string E.g. 'webhook', 'slack'
     */
    public function getKey(): string;

    /**
     * Get the human-readable display name for the action.
     *
     * @return string E.g. 'Generic Webhook'
     */
    public function getName(): string;

    /**
     * Get a short description explaining what this action does.
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Get the configuration fields required by this action.
     * Used by the Admin UI to auto-generate form fields.
     *
     * Expected array structure per field:
     * [
     *     'name'        => 'webhook_url',
     *     'label'       => 'Webhook Target URL',
     *     'type'        => 'text|textarea|password|select',
     *     'description' => 'Help text displayed below field',
     *     'required'    => true,
     *     'options'     => ['key' => 'label'], // Optional, for type=select
     *     'default'     => 'default_value'
     * ]
     *
     * @return array List of field definition arrays
     */
    public function getConfigFields(): array;

    /**
     * Execute the action with normalized trigger payload and action configuration.
     *
     * @param array $payload Normalized payload array from TriggerInterface
     * @param array $config Admin-configured key-value pairs for this action
     * @return array Result containing 'success' (bool), 'message' (string), and optional 'response' (array|string)
     */
    public function execute(array $payload, array $config): array;
}
