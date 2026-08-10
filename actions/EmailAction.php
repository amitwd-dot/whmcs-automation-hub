<?php

namespace AutomationHub\Actions;

use AutomationHub\Lib\ActionInterface;

/**
 * Class EmailAction
 *
 * Custom Email Notification Action that dispatches a formatted HTML alert email
 * to an admin-specified email address using WHMCS mail helpers or standard PHP mail.
 *
 * @package AutomationHub\Actions
 * @author Web Wave Digital <https://webwavedigital.co.in>
 */
class EmailAction implements ActionInterface
{
    public function getKey(): string
    {
        return 'email';
    }

    public function getName(): string
    {
        return 'Custom Email Alert';
    }

    public function getDescription(): string
    {
        return 'Sends a customized email notification to an admin-specified email address.';
    }

    public function getConfigFields(): array
    {
        return [
            [
                'name'        => 'recipient_email',
                'label'       => 'Recipient Email Address',
                'type'        => 'text',
                'description' => 'Target email address to receive event notifications.',
                'required'    => true,
                'default'     => '',
            ],
            [
                'name'        => 'email_subject',
                'label'       => 'Email Subject',
                'type'        => 'text',
                'description' => 'Subject line for the notification email.',
                'required'    => true,
                'default'     => 'WHMCS Alert: Event Triggered',
            ],
        ];
    }

    public function execute(array $payload, array $config): array
    {
        $recipient = trim($config['recipient_email'] ?? '');
        $subject = trim($config['email_subject'] ?? 'WHMCS Automation Alert');

        if (empty($recipient) || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'A valid recipient email address is required.',
            ];
        }

        $eventName = ucwords(str_replace('_', ' ', $payload['event'] ?? 'WHMCS Event'));

        // Build HTML email message body
        $html = "<h2>⚡ WHMCS Event Alert: {$eventName}</h2>";
        $html .= "<p>An automated trigger event fired at <strong>" . date('Y-m-d H:i:s') . "</strong>.</p>";
        $html .= "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse; font-family:sans-serif; font-size:13px; width:100%; max-width:600px;'>";
        $html .= "<thead style='background:#f8fafc;'><tr><th>Field Key</th><th>Value</th></tr></thead><tbody>";

        foreach ($payload as $key => $val) {
            if (is_array($val)) {
                $val = json_encode($val, JSON_UNESCAPED_SLASHES);
            }
            $html .= "<tr><td><strong>" . htmlspecialchars($key) . "</strong></td><td>" . htmlspecialchars((string)$val) . "</td></tr>";
        }

        $html .= "</tbody></table>";
        $html .= "<br><hr><p style='font-size:11px; color:#64748b;'>Sent automatically by WHMCS Automation Hub by Web Wave Digital (https://webwavedigital.co.in)</p>";

        try {
            // Attempt using WHMCS sendAdminEmail or localAPI if available
            if (function_exists('sendAdminNotification')) {
                sendAdminNotification('system', $subject, $html);
                return [
                    'success' => true,
                    'message' => "Email alert sent via WHMCS notification system to {$recipient}.",
                ];
            }

            // Fallback to PHP native mail with HTML headers
            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=utf-8\r\n";
            $headers .= "From: WHMCS Automation Hub <no-reply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ">\r\n";

            $sent = mail($recipient, $subject, $html, $headers);

            return [
                'success' => $sent,
                'message' => $sent
                    ? "Custom email alert sent successfully to {$recipient}."
                    : "Failed to dispatch email to {$recipient} via PHP mail().",
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => "Email Action Exception: " . $e->getMessage(),
            ];
        }
    }
}
