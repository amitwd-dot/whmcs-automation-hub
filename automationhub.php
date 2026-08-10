<?php
/**
 * WHMCS Automation Hub Addon Module
 *
 * Open-Source Event-Driven Automation & Integration Engine for WHMCS.
 * Connect internal WHMCS events (Order, Invoice, Ticket, Service, Domain, Client)
 * to external endpoints (Generic Webhooks, Slack, Discord, Email).
 *
 * @package WHMCS\Module\Addon\AutomationHub
 * @author Web Wave Digital <https://webwavedigital.co.in>
 * @license MIT
 * @version 1.0.0
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

require_once __DIR__ . '/lib/TriggerInterface.php';
require_once __DIR__ . '/lib/ActionInterface.php';
require_once __DIR__ . '/lib/HttpClient.php';
require_once __DIR__ . '/lib/Registry.php';
require_once __DIR__ . '/lib/RuleEngine.php';

use WHMCS\Database\Capsule;
use AutomationHub\Lib\Registry;
use AutomationHub\Lib\RuleEngine;

/**
 * Define addon module configuration metadata.
 *
 * @return array
 */
function automationhub_config()
{
    return [
        'name'        => 'WHMCS Automation Hub',
        'description' => 'Open-Source event-driven automation engine connecting WHMCS events to Webhooks, Slack, Discord, and Email alerts.',
        'author'      => 'Web Wave Digital',
        'language'    => 'english',
        'version'     => '1.0.0',
        'fields'      => []
    ];
}

/**
 * Addon module activation callback.
 * Creates required database tables: mod_automationhub_rules and mod_automationhub_logs.
 *
 * @return array Status array
 */
function automationhub_activate()
{
    try {
        $schema = Capsule::schema();

        // 1. Create Rules Table
        if (!$schema->hasTable('mod_automationhub_rules')) {
            $schema->create('mod_automationhub_rules', function($table) {
                $table->increments('id');
                $table->string('name', 255);
                $table->string('trigger_class', 100);
                $table->string('action_class', 100);
                $table->text('action_config')->nullable();
                $table->tinyInteger('enabled')->default(1);
                $table->timestamp('last_fired_at')->nullable();
                $table->timestamps();
            });
        }

        // 2. Create Activity Logs Table
        if (!$schema->hasTable('mod_automationhub_logs')) {
            $schema->create('mod_automationhub_logs', function($table) {
                $table->increments('id');
                $table->integer('rule_id')->default(0);
                $table->string('rule_name', 255);
                $table->string('trigger_class', 100);
                $table->string('action_class', 100);
                $table->string('status', 20); // success | failed
                $table->text('error_message')->nullable();
                $table->longText('payload')->nullable();
                $table->float('execution_time_ms', 8, 2)->default(0.00);
                $table->timestamp('created_at')->useCurrent();
            });
        }

        return [
            'status'      => 'success',
            'description' => 'WHMCS Automation Hub activated successfully. Database tables created.',
        ];
    } catch (\Throwable $e) {
        return [
            'status'      => 'error',
            'description' => 'Activation Failed: ' . $e->getMessage(),
        ];
    }
}

/**
 * Addon module deactivation callback.
 *
 * @return array Status array
 */
function automationhub_deactivate()
{
    return [
        'status'      => 'success',
        'description' => 'WHMCS Automation Hub deactivated successfully. Database tables preserved.',
    ];
}

/**
 * Admin Area Module Output Router & Controller.
 *
 * @param array $vars Addon variables
 * @return void
 */
function automationhub_output($vars)
{
    Registry::init();

    $moduleLink = 'addonmodules.php?module=automationhub';
    $action = $_GET['action'] ?? '';
    $tab = $_GET['tab'] ?? 'rules';

    // Handle Form Action Actions (POST / GET state mutations)
    if ($action === 'save_rule' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $ruleId = (int)($_GET['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $triggerClass = trim($_POST['trigger_class'] ?? '');
        $actionClass = trim($_POST['action_class'] ?? '');
        $actionConfig = $_POST['action_config'] ?? [];
        $enabled = isset($_POST['enabled']) ? 1 : 0;

        if (!empty($name) && !empty($triggerClass) && !empty($actionClass)) {
            $data = [
                'name'          => $name,
                'trigger_class' => $triggerClass,
                'action_class'  => $actionClass,
                'action_config' => json_encode($actionConfig, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'enabled'       => $enabled,
                'updated_at'    => date('Y-m-d H:i:s'),
            ];

            if ($ruleId > 0) {
                Capsule::table('mod_automationhub_rules')->where('id', $ruleId)->update($data);
                echo '<div class="alert alert-success"><i class="fa fa-check-circle"></i> Automation rule updated successfully.</div>';
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                Capsule::table('mod_automationhub_rules')->insert($data);
                echo '<div class="alert alert-success"><i class="fa fa-check-circle"></i> New automation rule created successfully.</div>';
            }
        }
        $tab = 'rules';
    } elseif ($action === 'toggle_rule') {
        $ruleId = (int)($_GET['id'] ?? 0);
        if ($ruleId > 0) {
            $rule = Capsule::table('mod_automationhub_rules')->where('id', $ruleId)->first();
            if ($rule) {
                $newStatus = $rule->enabled ? 0 : 1;
                Capsule::table('mod_automationhub_rules')->where('id', $ruleId)->update(['enabled' => $newStatus]);
                echo '<div class="alert alert-info"><i class="fa fa-info-circle"></i> Rule status updated.</div>';
            }
        }
        $tab = 'rules';
    } elseif ($action === 'delete_rule') {
        $ruleId = (int)($_GET['id'] ?? 0);
        if ($ruleId > 0) {
            Capsule::table('mod_automationhub_rules')->where('id', $ruleId)->delete();
            echo '<div class="alert alert-success"><i class="fa fa-trash"></i> Automation rule deleted.</div>';
        }
        $tab = 'rules';
    } elseif ($action === 'clear_logs') {
        Capsule::table('mod_automationhub_logs')->truncate();
        echo '<div class="alert alert-success"><i class="fa fa-trash"></i> Activity logs cleared.</div>';
        $tab = 'logs';
    } elseif ($action === 'test_rule') {
        $ruleId = (int)($_GET['id'] ?? 0);
        if ($ruleId > 0) {
            try {
                $testResult = RuleEngine::testRule($ruleId);
                if ($testResult['status'] === 'success') {
                    echo '<div class="alert alert-success"><i class="fa fa-check-circle"></i> <strong>Test Execution Successful!</strong> Action executed in ' . $testResult['execution_time_ms'] . ' ms. Check Activity Log for payload details.</div>';
                } else {
                    echo '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> <strong>Test Execution Failed:</strong> ' . htmlspecialchars($testResult['error_message']) . '</div>';
                }
            } catch (\Throwable $e) {
                echo '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Test Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        }
        $tab = 'rules';
    }

    // Render Admin Header
    echo '<div style="margin-bottom:20px; background:linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%); padding:24px; border-radius:12px; color:#fff; display:flex; justify-content:space-between; align-items:center;">';
    echo '  <div>';
    echo '      <h2 style="margin:0; font-size:22px; font-weight:800; display:flex; align-items:center; gap:10px;">';
    echo '          <i class="fa fa-bolt" style="color:#6366f1;"></i> WHMCS Automation Hub';
    echo '      </h2>';
    echo '      <div style="font-size:13px; color:#94a3b8; margin-top:4px;">';
    echo '          Open-Source Event-Driven Automation Engine by <a href="https://webwavedigital.co.in" target="_blank" style="color:#38bdf8; font-weight:bold; text-decoration:none;">Web Wave Digital</a>';
    echo '      </div>';
    echo '  </div>';
    echo '  <div>';
    echo '      <a href="https://webwavedigital.co.in" target="_blank" class="btn btn-sm" style="background:rgba(255,255,255,0.1); color:#fff; border:1px solid rgba(255,255,255,0.2); font-weight:700;">Web Wave Digital</a>';
    echo '  </div>';
    echo '</div>';

    // Render Navigation Tabs
    echo '<ul class="nav nav-tabs" style="margin-bottom:20px;">';
    echo '  <li class="' . ($tab === 'rules' && $action !== 'add_rule' && $action !== 'edit_rule' ? 'active' : '') . '"><a href="' . $moduleLink . '&tab=rules"><i class="fa fa-list"></i> Automation Rules</a></li>';
    echo '  <li class="' . ($action === 'add_rule' || $action === 'edit_rule' ? 'active' : '') . '"><a href="' . $moduleLink . '&action=add_rule"><i class="fa fa-plus-circle"></i> Add New Rule</a></li>';
    echo '  <li class="' . ($tab === 'logs' ? 'active' : '') . '"><a href="' . $moduleLink . '&tab=logs"><i class="fa fa-history"></i> Activity Logs</a></li>';
    echo '</ul>';

    // Route Views
    if ($action === 'add_rule' || $action === 'edit_rule') {
        require __DIR__ . '/admin/rule_form.php';
    } elseif ($tab === 'logs') {
        require __DIR__ . '/admin/activity_log.php';
    } else {
        require __DIR__ . '/admin/rules_list.php';
    }
}
