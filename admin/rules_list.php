<?php
/**
 * WHMCS Automation Hub - Rules List View
 *
 * @author Web Wave Digital <https://webwavedigital.co.in>
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Database\Capsule;
use AutomationHub\Lib\Registry;

Registry::init();
$triggers = Registry::getTriggers();
$actions = Registry::getActions();

$rules = Capsule::table('mod_automationhub_rules')->orderBy('id', 'DESC')->get();
?>

<div class="cms-card" style="background:#fff; border-radius:12px; padding:24px; box-shadow:0 4px 15px rgba(0,0,0,0.04); margin-top:20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <h3 style="margin:0; font-size:18px; font-weight:800; color:#0f172a;">Configured Automation Rules</h3>
            <div style="font-size:13px; color:#64748b; margin-top:4px;">Connect internal WHMCS events to external webhooks, Slack, Discord, or Email notifications.</div>
        </div>
        <a href="<?php echo $moduleLink; ?>&action=add_rule" class="btn btn-primary" style="background:linear-gradient(135deg, #6366f1, #4f46e5); border:none; font-weight:700; padding:9px 18px; border-radius:8px;">
            <i class="fa fa-plus"></i> Add New Rule
        </a>
    </div>

    <?php if (count($rules) === 0): ?>
        <div style="text-align:center; padding:50px 20px; background:#f8fafc; border:2px dashed #cbd5e1; border-radius:12px;">
            <div style="font-size:36px; color:#94a3b8; margin-bottom:10px;"><i class="fa fa-bolt"></i></div>
            <h4 style="font-weight:700; color:#334155; margin-bottom:6px;">No Automation Rules Configured</h4>
            <p style="color:#64748b; font-size:13px; margin-bottom:20px;">Create your first automation rule to connect WHMCS events to external platforms.</p>
            <a href="<?php echo $moduleLink; ?>&action=add_rule" class="btn btn-primary btn-sm" style="font-weight:700;">Create First Rule</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover" style="border-collapse:separate; border-spacing:0; width:100%;">
                <thead>
                    <tr style="background:#f8fafc; color:#475569; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">
                        <th style="padding:12px 16px; border-bottom:2px solid #e2e8f0;">Rule Name</th>
                        <th style="padding:12px 16px; border-bottom:2px solid #e2e8f0;">Trigger Event</th>
                        <th style="padding:12px 16px; border-bottom:2px solid #e2e8f0;">Target Action</th>
                        <th style="padding:12px 16px; border-bottom:2px solid #e2e8f0;">Status</th>
                        <th style="padding:12px 16px; border-bottom:2px solid #e2e8f0;">Last Fired</th>
                        <th style="padding:12px 16px; border-bottom:2px solid #e2e8f0; text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rules as $rule): ?>
                        <?php
                            $trigObj = $triggers[$rule->trigger_class] ?? null;
                            $actObj = $actions[$rule->action_class] ?? null;
                        ?>
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:14px 16px; vertical-align:middle;">
                                <strong style="color:#0f172a; font-size:14px;"><?php echo htmlspecialchars($rule->name); ?></strong>
                            </td>
                            <td style="padding:14px 16px; vertical-align:middle;">
                                <span class="label label-info" style="background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; font-size:11px; font-weight:700; padding:5px 10px; border-radius:12px;">
                                    <i class="fa fa-flash"></i> <?php echo htmlspecialchars($trigObj ? $trigObj->getName() : $rule->trigger_class); ?>
                                </span>
                            </td>
                            <td style="padding:14px 16px; vertical-align:middle;">
                                <span class="label label-primary" style="background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; font-size:11px; font-weight:700; padding:5px 10px; border-radius:12px;">
                                    <i class="fa fa-paper-plane"></i> <?php echo htmlspecialchars($actObj ? $actObj->getName() : $rule->action_class); ?>
                                </span>
                            </td>
                            <td style="padding:14px 16px; vertical-align:middle;">
                                <a href="<?php echo $moduleLink; ?>&action=toggle_rule&id=<?php echo $rule->id; ?>" class="label <?php echo $rule->enabled ? 'label-success' : 'label-default'; ?>" style="font-size:11px; font-weight:700; padding:5px 10px; text-decoration:none;">
                                    <?php echo $rule->enabled ? '✓ ACTIVE' : '✕ DISABLED'; ?>
                                </a>
                            </td>
                            <td style="padding:14px 16px; vertical-align:middle; color:#64748b; font-size:12px;">
                                <?php echo $rule->last_fired_at ? date('M j, Y H:i', strtotime($rule->last_fired_at)) : '<span style="color:#94a3b8;">Never</span>'; ?>
                            </td>
                            <td style="padding:14px 16px; vertical-align:middle; text-align:right;">
                                <div style="display:inline-flex; gap:6px;">
                                    <a href="<?php echo $moduleLink; ?>&action=test_rule&id=<?php echo $rule->id; ?>" class="btn btn-xs btn-success" style="font-weight:700; border-radius:6px;" title="Test Rule execution">
                                        <i class="fa fa-play"></i> Test Rule
                                    </a>
                                    <a href="<?php echo $moduleLink; ?>&action=edit_rule&id=<?php echo $rule->id; ?>" class="btn btn-xs btn-warning" style="font-weight:700; border-radius:6px;">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                    <a href="<?php echo $moduleLink; ?>&action=delete_rule&id=<?php echo $rule->id; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure you want to delete this automation rule?');" style="font-weight:700; border-radius:6px;">
                                        <i class="fa fa-trash"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
