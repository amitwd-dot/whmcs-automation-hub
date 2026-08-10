<?php

namespace AutomationHub\Lib;

/**
 * Class Registry
 *
 * Central registry for discovering, instantiating, and retrieving available
 * Triggers and Actions from the /triggers and /actions directories.
 *
 * @package AutomationHub\Lib
 * @author Web Wave Digital <https://webwavedigital.co.in>
 */
class Registry
{
    /** @var array<string, TriggerInterface> Map of trigger_key => TriggerInterface */
    private static $triggers = [];

    /** @var array<string, ActionInterface> Map of action_key => ActionInterface */
    private static $actions = [];

    /** @var bool Flag tracking whether registry has been loaded */
    private static $loaded = false;

    /**
     * Initialize and discover all triggers and actions.
     *
     * @param string|null $baseDir Root directory of the module
     * @return void
     */
    public static function init(?string $baseDir = null): void
    {
        if (self::$loaded) {
            return;
        }

        $baseDir = $baseDir ?: dirname(__DIR__);

        self::loadTriggers($baseDir . '/triggers');
        self::loadActions($baseDir . '/actions');

        self::$loaded = true;
    }

    /**
     * Load all trigger classes from the triggers directory.
     *
     * @param string $dir Absolute path to triggers folder
     * @return void
     */
    private static function loadTriggers(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = glob($dir . '/*Trigger.php');
        if (!$files) {
            return;
        }

        foreach ($files as $file) {
            require_once $file;
            $className = 'AutomationHub\\Triggers\\' . pathinfo($file, PATHINFO_FILENAME);

            if (class_exists($className)) {
                $reflection = new \ReflectionClass($className);
                if ($reflection->implementsInterface(TriggerInterface::class) && !$reflection->isAbstract()) {
                    /** @var TriggerInterface $instance */
                    $instance = new $className();
                    self::$triggers[$instance->getKey()] = $instance;
                }
            }
        }
    }

    /**
     * Load all action classes from the actions directory.
     *
     * @param string $dir Absolute path to actions folder
     * @return void
     */
    private static function loadActions(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = glob($dir . '/*Action.php');
        if (!$files) {
            return;
        }

        foreach ($files as $file) {
            require_once $file;
            $className = 'AutomationHub\\Actions\\' . pathinfo($file, PATHINFO_FILENAME);

            if (class_exists($className)) {
                $reflection = new \ReflectionClass($className);
                if ($reflection->implementsInterface(ActionInterface::class) && !$reflection->isAbstract()) {
                    /** @var ActionInterface $instance */
                    $instance = new $className();
                    self::$actions[$instance->getKey()] = $instance;
                }
            }
        }
    }

    /**
     * Register a trigger instance manually (useful for testing or external plugins).
     *
     * @param TriggerInterface $trigger
     * @return void
     */
    public static function registerTrigger(TriggerInterface $trigger): void
    {
        self::$triggers[$trigger->getKey()] = $trigger;
    }

    /**
     * Register an action instance manually (useful for testing or external plugins).
     *
     * @param ActionInterface $action
     * @return void
     */
    public static function registerAction(ActionInterface $action): void
    {
        self::$actions[$action->getKey()] = $action;
    }

    /**
     * Get all registered triggers.
     *
     * @return array<string, TriggerInterface>
     */
    public static function getTriggers(): array
    {
        self::init();
        return self::$triggers;
    }

    /**
     * Get a trigger instance by its key.
     *
     * @param string $key Trigger key e.g. 'invoice_paid'
     * @return TriggerInterface|null
     */
    public static function getTriggerByKey(string $key): ?TriggerInterface
    {
        self::init();
        return self::$triggers[$key] ?? null;
    }

    /**
     * Get all registered triggers listening to a specific WHMCS hook name.
     *
     * @param string $hookName WHMCS hook name e.g. 'InvoicePaid'
     * @return array<TriggerInterface>
     */
    public static function getTriggersByHookName(string $hookName): array
    {
        self::init();
        $matched = [];
        foreach (self::$triggers as $trigger) {
            if ($trigger->getHookName() === $hookName) {
                $matched[] = $trigger;
            }
        }
        return $matched;
    }

    /**
     * Get all registered actions.
     *
     * @return array<string, ActionInterface>
     */
    public static function getActions(): array
    {
        self::init();
        return self::$actions;
    }

    /**
     * Get an action instance by its key.
     *
     * @param string $key Action key e.g. 'webhook'
     * @return ActionInterface|null
     */
    public static function getActionByKey(string $key): ?ActionInterface
    {
        self::init();
        return self::$actions[$key] ?? null;
    }

    /**
     * Reset registry state (useful for unit tests).
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$triggers = [];
        self::$actions = [];
        self::$loaded = false;
    }
}
