<?php
/**
 * WHMCS Automation Hub - Activity Log View
 *
 * @author Web Wave Digital <https://webwavedigital.co.in>
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Database\Capsule;

$page = max(1, (int)($_GET['p'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

$totalLogs = Capsule::table('mod_automationhub_logs')->count();
$totalPages = ceil($totalLogs / $perPage);

$logs = Capsule::table('mod_automationhub_logs')
    ->orderBy('id', 'DESC')
    ->skip($offset)
    ->take($perPage)
    ->get();
?>

<div class="cms-card" style="background:#fff; border-radius:12px; padding:24px; box-shadow:0 4px 15px rgba(0,0,0,0.04); margin-top:20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <h3 style="margin:0; font-size:18px; font-weight:800; color:#0f172a;">Execution Activity Logs</h3>
            <div style="font-size:13px; color:#64748b; margin-top:4px;">Audit trail of all automated rule executions, status codes, timings, and payload snapshots.</div>
        </div>
        <?php if ($totalLogs > 0): ?>
            <a href="<?php echo $moduleLink; ?>&action=clear_logs" class="btn btn-default btn-xs" onclick="return confirm('Clear all activity logs?');" style="color:#ef4444; border-color:#fca5a5; font-weight:700;">
                <i class="fa fa-trash"></i> Clear Logs
            </a>
        <?php endif; ?>
    </div>

    <?php if ($totalLogs === 0): ?>
        <div style="text-align:center; padding:50px 20px; background:#f8fafc; border:2px dashed #cbd5e1; border-radius:12px;">
            <div style="font-size:36px; color:#94a3b8; margin-bottom:10px;"><i class="fa fa-history"></i></div>
            <h4 style="font-weight:700; color:#334155; margin-bottom:6px;">No Execution Activity Logged Yet</h4>
            <p style="color:#64748b; font-size:13px;">Rule execution history will appear here automatically when WHMCS events fire.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover" style="border-collapse:separate; border-spacing:0; width:100%;">
                <thead>
                    <tr style="background:#f8fafc; color:#475569; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">
                        <th style="padding:12px 16px; border-bottom:2px solid #e2e8f0;">ID / Time</th>
                        <th style="padding:12px 16px; border-bottom:2px solid #e2e8f0;">Rule Name</th>
                        <th style="padding:12px 16px; border-bottom:2px solid #e2e8f0;">Trigger & Action</th>
                        <th style="padding:12px 16px; border-bottom:2px solid #e2e8f0;">Status</th>
                        <th style="padding:12px 16px; border-bottom:2px solid #e2e8f0;">Duration</th>
                        <th style="padding:12px 16px; border-bottom:2px solid #e2e8f0; text-align:right;">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:14px 16px; vertical-align:middle; font-size:12px;">
                                <strong style="color:#0f172a;">#<?php echo $log->id; ?></strong><br>
                                <span style="color:#64748b; font-size:11px;"><?php echo date('M j, Y H:i:s', strtotime($log->created_at)); ?></span>
                            </td>
                            <td style="padding:14px 16px; vertical-align:middle;">
                                <strong style="color:#0f172a; font-size:13px;"><?php echo htmlspecialchars($log->rule_name); ?></strong>
                            </td>
                            <td style="padding:14px 16px; vertical-align:middle; font-size:12px;">
                                <code style="background:#f1f5f9; color:#475569; padding:3px 6px; border-radius:4px;"><?php echo htmlspecialchars($log->trigger_class); ?></code>
                                <i class="fa fa-arrow-right text-muted" style="margin:0 4px;"></i>
                                <code style="background:#f1f5f9; color:#475569; padding:3px 6px; border-radius:4px;"><?php echo htmlspecialchars($log->action_class); ?></code>
                            </td>
                            <td style="padding:14px 16px; vertical-align:middle;">
                                <?php if ($log->status === 'success'): ?>
                                    <span class="label label-success" style="font-size:11px; font-weight:700; padding:5px 10px; border-radius:12px; background:#f0fdf4; color:#166534; border:1px solid #bbf7d0;">
                                        ✓ SUCCESS
                                    </span>
                                <?php else: ?>
                                    <span class="label label-danger" style="font-size:11px; font-weight:700; padding:5px 10px; border-radius:12px; background:#fef2f2; color:#991b1b; border:1px solid #fecaca;">
                                        ✕ FAILED
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:14px 16px; vertical-align:middle; font-size:12px; color:#64748b; font-family:monospace;">
                                <?php echo number_format($log->execution_time_ms, 2); ?> ms
                            </td>
                            <td style="padding:14px 16px; vertical-align:middle; text-align:right;">
                                <button type="button" class="btn btn-xs btn-default" onclick="showLogDetails(<?php echo $log->id; ?>)" style="font-weight:700; border-radius:6px;">
                                    <i class="fa fa-eye"></i> View Payload
                                </button>
                                <div id="log_payload_<?php echo $log->id; ?>" style="display:none;"><?php echo htmlspecialchars($log->payload); ?></div>
                                <div id="log_error_<?php echo $log->id; ?>" style="display:none;"><?php echo htmlspecialchars($log->error_message); ?></div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        <?php if ($totalPages > 1): ?>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; font-size:13px; color:#64748b;">
                <div>Showing page <strong><?php echo $page; ?></strong> of <strong><?php echo $totalPages; ?></strong></div>
                <ul class="pagination pagination-sm" style="margin:0;">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="<?php echo ($i === $page) ? 'active' : ''; ?>">
                            <a href="<?php echo $moduleLink; ?>&tab=logs&p=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Modal for Viewing Log Payload Details -->
<div class="modal fade" id="logDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius:12px; overflow:hidden;">
            <div class="modal-header" style="background:#0f172a; color:#fff; padding:16px 20px;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
                <h4 class="modal-header-title" style="margin:0; font-weight:700; font-size:16px;">
                    <i class="fa fa-code"></i> Execution Details & Payload Snapshot
                </h4>
            </div>
            <div class="modal-body" style="padding:20px;">
                <div id="modal_error_box" style="display:none;" class="alert alert-danger">
                    <strong>Error Message:</strong> <span id="modal_error_text"></span>
                </div>
                <h5 style="font-weight:700; color:#334155; margin-top:0;">Normalized Event Payload (JSON)</h5>
                <pre id="modal_payload_text" style="background:#0f172a; color:#38bdf8; padding:16px; border-radius:8px; font-family:monospace; font-size:12px; max-height:400px; overflow:auto;"></pre>
            </div>
            <div class="modal-footer" style="background:#f8fafc;">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight:700; border-radius:6px;">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showLogDetails(logId) {
        const payloadText = document.getElementById("log_payload_" + logId).innerText;
        const errorText = document.getElementById("log_error_" + logId).innerText;

        let prettyPayload = payloadText;
        try {
            const parsed = JSON.parse(payloadText);
            prettyPayload = JSON.stringify(parsed, null, 2);
        } catch (e) {}

        document.getElementById("modal_payload_text").innerText = prettyPayload;

        const errorBox = document.getElementById("modal_error_box");
        const errorSpan = document.getElementById("modal_error_text");

        if (errorText && errorText.trim().length > 0) {
            errorSpan.innerText = errorText;
            errorBox.style.display = "block";
        } else {
            errorBox.style.display = "none";
        }

        $('#logDetailsModal').modal('show');
    }
</script>
