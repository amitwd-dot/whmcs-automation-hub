<?php

namespace AutomationHub\Actions;

use AutomationHub\Lib\ActionInterface;
use AutomationHub\Lib\HttpClient;

/**
 * Class SlackAction
 *
 * Formatted Slack Notification Action using Slack Incoming Webhooks.
 *
 * @package AutomationHub\Actions
 * @author Web Wave Digital <https://webwavedigital.co.in>
 */
class SlackAction implements ActionInterface
{
    public function getKey(): string
    {
        return 'slack';
    }

    public function getName(): string
    {
        return 'Slack Notification';
    }

    public function getDescription(): string
    {
        return 'Posts a formatted message block to a Slack channel using an Incoming Webhook URL.';
    }

    public function getConfigFields(): array
    {
        return [
            [
                'name'        => 'webhook_url',
                'label'       => 'Slack Incoming Webhook URL',
                'type'        => 'text',
                'description' => 'Slack Incoming Webhook URL (e.g. https://hooks.slack.com/services/...)',
                'required'    => true,
                'default'     => '',
            ],
            [
                'name'        => 'channel',
                'label'       => 'Slack Channel Override',
                'type'        => 'text',
                'description' => 'Optional channel name (e.g. #alerts or #tickets)',
                'required'    => false,
                'default'     => '',
            ],
            [
                'name'        => 'custom_title',
                'label'       => 'Notification Title',
                'type'        => 'text',
                'description' => 'Optional custom title heading for the Slack message card.',
                'required'    => false,
                'default'     => 'WHMCS Automation Alert',
            ],
        ];
    }

    public function execute(array $payload, array $config): array
    {
        $url = trim($config['webhook_url'] ?? '');

        if (empty($url)) {
            return [
                'success' => false,
                'message' => 'Slack Webhook URL is required.',
            ];
        }

        $eventName = ucwords(str_replace('_', ' ', $payload['event'] ?? 'WHMCS Event'));
        $title = !empty($config['custom_title']) ? trim($config['custom_title']) : "WHMCS Alert: {$eventName}";

        $fields = [];
        foreach ($payload as $key => $val) {
            if ($key === 'event' || is_array($val)) {
                continue;
            }
            $label = ucwords(str_replace('_', ' ', $key));
            $fields[] = [
                'type' => 'mrkdwn',
                'text' => "*{$label}:*\n`" . (is_bool($val) ? ($val ? 'Yes' : 'No') : (string)$val) . "`",
            ];
        }

        $blocks = [
            [
                'type' => 'header',
                'text' => [
                    'type'  => 'plain_text',
                    'text'  => "⚡ {$title}",
                    'emoji' => true,
                ],
            ],
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "An automated WHMCS trigger event (*{$eventName}*) was executed by *WHMCS Automation Hub*.",
                ],
            ],
        ];

        if (!empty($fields)) {
            // Slack fields section accepts up to 10 items per section
            $chunkedFields = array_chunk($fields, 10);
            foreach ($chunkedFields as $chunk) {
                $blocks[] = [
                    'type'   => 'section',
                    'fields' => $chunk,
                ];
            }
        }

        $slackPayload = [
            'text'   => "⚡ {$title}",
            'blocks' => $blocks,
        ];

        if (!empty($config['channel'])) {
            $slackPayload['channel'] = trim($config['channel']);
        }

        try {
            $response = HttpClient::post($url, $slackPayload);
            $is2xx = ($response['status_code'] >= 200 && $response['status_code'] < 300);

            return [
                'success'  => $is2xx,
                'message'  => $is2xx
                    ? "Slack notification delivered successfully (HTTP {$response['status_code']})."
                    : "Slack webhook failed with status code {$response['status_code']}.",
                'response' => $response,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => "Slack Action Error: " . $e->getMessage(),
            ];
        }
    }
}
