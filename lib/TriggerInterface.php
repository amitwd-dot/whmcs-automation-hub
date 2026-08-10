<?php

namespace AutomationHub\Lib;

/**
 * Interface TriggerInterface
 *
 * Pluggable interface representing a WHMCS event trigger.
 * Each trigger class implements this interface to normalize WHMCS hook parameters
 * into a clean, consistent payload passed to actions.
 *
 * @package AutomationHub\Lib
 * @author Web Wave Digital <https://webwavedigital.co.in>
 */
interface TriggerInterface
{
    /**
     * Get the unique key identifying this trigger.
     *
     * @return string E.g. 'invoice_paid'
     */
    public function getKey(): string;

    /**
     * Get the human-readable display name for the trigger.
     *
     * @return string E.g. 'Invoice Paid'
     */
    public function getName(): string;

    /**
     * Get a short description explaining when this trigger fires.
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Get the WHMCS hook name this trigger listens to.
     * Return empty string for custom/cron-based triggers.
     *
     * @return string E.g. 'InvoicePaid', 'TicketOpen'
     */
    public function getHookName(): string;

    /**
     * Normalize WHMCS raw hook parameters into a clean payload array.
     *
     * @param array $hookParams Raw hook parameters passed by WHMCS
     * @return array Standardized key-value payload array
     */
    public function getPayload(array $hookParams): array;

    /**
     * Get a realistic sample payload used for testing rules without a real event.
     *
     * @return array Sample payload array
     */
    public function getSamplePayload(): array;
}
