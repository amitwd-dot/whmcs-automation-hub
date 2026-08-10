<?php
/**
 * WHMCS Automation Hub - Add/Edit Rule Form View
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

$ruleId = (int)($_GET['id'] ?? 0);
$rule = null;
if ($ruleId > 0) {
    $rule = Capsule::table('mod_automationhub_rules')->where('id', $ruleId)->first();
}

$ruleName = $rule ? htmlspecialchars($rule->name) : '';
$ruleTrigger = $rule ? $rule->trigger_class : '';
$ruleAction = $rule ? $rule->action_class : '';
$ruleEnabled = $rule ? (bool)$rule->enabled : true;

$existingConfig = [];
if ($rule && !empty($rule->action_config)) {
    $existingConfig = is_array($rule->action_config)
        ? $rule->action_config
        : (json_decode($rule->action_config, true) ?: []);
}

// Build Action Field Schema Metadata for JavaScript dynamic rendering
$actionFieldSchemas = [];
foreach ($actions as $key => $actionObj) {
    $actionFieldSchemas[$key] = $actionObj->getConfigFields();
}
?>

<div class="cms-card" style="background:#fff; border-radius:12px; padding:28px; box-shadow:0 4px 15px rgba(0,0,0,0.04); margin-top:20px; max-width:850px;">
    <h3 style="margin-top:0; font-weight:800; color:#0f172a; border-bottom:2px solid #f1f5f9; padding-bottom:14px; margin-bottom:24px;">
        <i class="fa fa-sliders text-primary"></i> <?php echo $ruleId > 0 ? 'Edit Automation Rule' : 'Create New Automation Rule'; ?>
    </h3>

    <form method="POST" action="<?php echo $moduleLink; ?>&action=save_rule&id=<?php echo $ruleId; ?>">
        <div class="form-group" style="margin-bottom:20px;">
            <label style="font-weight:700; color:#334155;">Rule Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Post Slack notification on High Priority Ticket" value="<?php echo $ruleName; ?>" required style="height:44px; border-radius:8px;">
        </div>

        <div class="row" style="margin-bottom:20px;">
            <div class="col-md-6">
                <div class="form-group">
                    <label style="font-weight:700; color:#334155;">Select Trigger Event <span class="text-danger">*</span></label>
                    <select name="trigger_class" class="form-control" required style="height:44px; border-radius:8px;">
                        <option value="">-- Choose Event Trigger --</option>
                        <?php foreach ($triggers as $key => $trig): ?>
                            <option value="<?php echo $key; ?>" <?php echo ($ruleTrigger === $key) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($trig->getName()); ?> (<?php echo $trig->getHookName() ?: 'Cron'; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label style="font-weight:700; color:#334155;">Select Action <span class="text-danger">*</span></label>
                    <select name="action_class" id="action_class_select" class="form-control" required style="height:44px; border-radius:8px;" onchange="renderActionConfigFields()">
                        <option value="">-- Choose Target Action --</option>
                        <?php foreach ($actions as $key => $act): ?>
                            <option value="<?php echo $key; ?>" <?php echo ($ruleAction === $key) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($act->getName()); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Dynamic Action Config Fields Container -->
        <div id="action_config_container" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:20px; margin-bottom:24px; display:none;">
            <h4 style="margin-top:0; font-weight:700; color:#0f172a; font-size:14px; margin-bottom:14px;">
                <i class="fa fa-cogs text-info"></i> Action Configuration Settings
            </h4>
            <div id="dynamic_fields_wrapper"></div>
        </div>

        <div class="form-group" style="margin-bottom:24px;">
            <label style="font-weight:700; color:#334155; cursor:pointer;">
                <input type="checkbox" name="enabled" value="1" <?php echo $ruleEnabled ? 'checked' : ''; ?> style="width:18px; height:18px; vertical-align:middle; margin-right:8px;">
                <strong>Enable Rule Immediately</strong>
            </label>
        </div>

        <div style="display:flex; gap:14px; align-items:center;">
            <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg, #6366f1, #4f46e5); border:none; font-weight:700; padding:10px 24px; border-radius:8px;">
                <i class="fa fa-check"></i> Save Automation Rule
            </button>
            <a href="<?php echo $moduleLink; ?>&tab=rules" class="btn btn-default" style="font-weight:700; padding:10px 20px; border-radius:8px; background:#f1f5f9; color:#475569; border:1px solid #cbd5e1;">Cancel</a>
        </div>
    </form>
</div>

<script>
    const actionSchemas = <?php echo json_encode($actionFieldSchemas, JSON_UNESCAPED_SLASHES); ?>;
    const existingConfig = <?php echo json_encode($existingConfig, JSON_UNESCAPED_SLASHES); ?>;

    function renderActionConfigFields() {
        const select = document.getElementById("action_class_select");
        const container = document.getElementById("action_config_container");
        const wrapper = document.getElementById("dynamic_fields_wrapper");

        const actionKey = select.value;
        wrapper.innerHTML = "";

        if (!actionKey || !actionSchemas[actionKey]) {
            container.style.display = "none";
            return;
        }

        const fields = actionSchemas[actionKey];
        if (fields.length === 0) {
            container.style.display = "none";
            return;
        }

        fields.forEach(field => {
            const fieldVal = existingConfig[field.name] !== undefined ? existingConfig[field.name] : (field.default || "");
            const fieldId = "cfg_" + field.name;

            let inputHtml = "";
            if (field.type === "textarea") {
                inputHtml = `<textarea name="action_config[${field.name}]" id="${fieldId}" class="form-control" rows="3" ${field.required ? 'required' : ''} style="border-radius:8px;">${escapeHtml(fieldVal)}</textarea>`;
            } else if (field.type === "select" && field.options) {
                let opts = "";
                for (let optKey in field.options) {
                    let sel = (fieldVal === optKey) ? 'selected' : '';
                    opts += `<option value="${optKey}" ${sel}>${field.options[optKey]}</option>`;
                }
                inputHtml = `<select name="action_config[${field.name}]" id="${fieldId}" class="form-control" ${field.required ? 'required' : ''} style="height:40px; border-radius:8px;">${opts}</select>`;
            } else {
                let inputType = field.type === 'password' ? 'password' : 'text';
                inputHtml = `<input type="${inputType}" name="action_config[${field.name}]" id="${fieldId}" class="form-control" value="${escapeHtml(fieldVal)}" ${field.required ? 'required' : ''} style="height:40px; border-radius:8px;">`;
            }

            const html = `
                <div class="form-group" style="margin-bottom:14px;">
                    <label for="${fieldId}" style="font-weight:700; font-size:13px; color:#334155;">
                        ${escapeHtml(field.label)} ${field.required ? '<span class="text-danger">*</span>' : ''}
                    </label>
                    ${inputHtml}
                    ${field.description ? `<small class="text-muted" style="display:block; margin-top:4px;">${escapeHtml(field.description)}</small>` : ''}
                </div>
            `;
            wrapper.innerHTML += html;
        });

        container.style.display = "block";
    }

    function escapeHtml(str) {
        if (typeof str !== 'string') return str;
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    // Auto trigger on initial page load if editing
    document.addEventListener("DOMContentLoaded", function() {
        renderActionConfigFields();
    });
</script>
