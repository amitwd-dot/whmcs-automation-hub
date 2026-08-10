<?php

namespace AutomationHub\Actions;

use AutomationHub\Lib\ActionInterface;
use AutomationHub\Lib\HttpClient;

/**
 * Class DiscordAction
 *
 * Formatted Discord Webhook Action with rich embeds.
 *
 * @package AutomationHub\Actions
 * @author Web Wave Digital <https://webwavedigital.co.in>
 */
class DiscordAction implements ActionInterface
{
    public function getKey(): string
    {
        return 'discord';
    }

    public function getName(): string
    {
        return 'Discord Webhook Embed';
    }

    public function getDescription(): string
    {
        return 'Posts a rich formatted embed message card to a Discord webhook channel.';
    }

    public function getConfigFields(): array
    {
        return [
            [
                'name'        => 'webhook_url',
                'label'       => 'Discord Webhook URL',
                'type'        => 'text',
                'description' => 'Discord Webhook URL (e.g. https://discord.com/api/webhooks/...)',
                'required'    => true,
                'default'     => '',
            ],
            [
                'name'        => 'bot_username',
                'label'       => 'Bot Username',
                'type'        => 'text',
                'description' => 'Custom username displayed for the Discord bot poster.',
                'required'    => false,
                'default'     => 'WHMCS Automation Hub',
            ],
            [
                'name'        => 'embed_color',
                'label'       => 'Embed Hex Color',
                'type'        => 'text',
                'description' => 'Hexadecimal color code for embed border line (e.g. #6366f1 or 65280).',
                'required'    => false,
                'default'     => '#6366f1',
            ],
        ];
    }

    public function execute(array $payload, array $config): array
    {
        $url = trim($config['webhook_url'] ?? '');

        if (empty($url)) {
            return [
                'success' => false,
                'message' => 'Discord Webhook URL is required.',
            ];
        }

        $eventName = ucwords(str_replace('_', ' ', $payload['event'] ?? 'WHMCS Event'));
        $colorHex = ltrim($config['embed_color'] ?? '#6366f1', '#');
        $colorInt = hexdec($colorHex) ?: 6516465;

        $embedFields = [];
        foreach ($payload as $key => $val) {
            if ($key === 'event' || is_array($val)) {
                continue;
            }
            $label = ucwords(str_replace('_', ' ', $key));
            $embedFields[] = [
                'name'   => $label,
                'value'  => (string)(is_bool($val) ? ($val ? 'Yes' : 'No') : $val),
                'inline' => true,
            ];
        }

        $discordPayload = [
            'username'   => !empty($config['bot_username']) ? trim($config['bot_username']) : 'WHMCS Automation Hub',
            'avatar_url' => 'https://webwavedigital.co.in/assets/img/logo.png',
            'embeds'     => [
                [
                    'title'       => "⚡ WHMCS Event: {$eventName}",
                    'description' => "Event triggered automatically by WHMCS Automation Hub.",
                    'color'       => $colorInt,
                    'fields'      => array_slice($embedFields, 0, 25), // Discord limits to 25 fields
                    'footer'      => [
                        'text' => 'WHMCS Automation Hub by Web Wave Digital',
                    ],
                    'timestamp'   => date('c'),
                ],
            ],
        ];

        try {
            $response = HttpClient::post($url, $discordPayload);
            $is2xx = ($response['status_code'] >= 200 && $response['status_code'] < 300);

            return [
                'success'  => $is2xx,
                'message'  => $is2xx
                    ? "Discord embed sent successfully (HTTP {$response['status_code']})."
                    : "Discord webhook failed with HTTP status code {$response['status_code']}.",
                'response' => $response,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => "Discord Action Error: " . $e->getMessage(),
            ];
        }
    }
}
