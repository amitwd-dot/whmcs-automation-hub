<?php

namespace AutomationHub\Actions;

use AutomationHub\Lib\ActionInterface;
use AutomationHub\Lib\HttpClient;

/**
 * Class WebhookAction
 *
 * Generic Webhook Action that posts a structured JSON payload to any destination URL
 * (e.g. Zapier, Make, n8n, custom API endpoint). Includes SSRF safety validation.
 *
 * @package AutomationHub\Actions
 * @author Web Wave Digital <https://webwavedigital.co.in>
 */
class WebhookAction implements ActionInterface
{
    public function getKey(): string
    {
        return 'webhook';
    }

    public function getName(): string
    {
        return 'Generic Webhook (JSON POST)';
    }

    public function getDescription(): string
    {
        return 'Sends a HTTP POST JSON payload to any configured external webhook endpoint (Zapier, Make, n8n, custom server).';
    }

    public function getConfigFields(): array
    {
        return [
            [
                'name'        => 'webhook_url',
                'label'       => 'Webhook Target URL',
                'type'        => 'text',
                'description' => 'Target HTTP or HTTPS endpoint URL to receive event JSON payload.',
                'required'    => true,
                'default'     => '',
            ],
            [
                'name'        => 'custom_headers',
                'label'       => 'Custom HTTP Headers',
                'type'        => 'textarea',
                'description' => 'Optional extra headers (one per line, e.g. Authorization: Bearer token123)',
                'required'    => false,
                'default'     => '',
            ],
            [
                'name'        => 'secret_token',
                'label'       => 'Secret Signature Token',
                'type'        => 'text',
                'description' => 'Optional secret included in X-AutomationHub-Secret header for verifying payload authenticity.',
                'required'    => false,
                'default'     => '',
            ],
        ];
    }

    public function execute(array $payload, array $config): array
    {
        $url = trim($config['webhook_url'] ?? '');

        if (empty($url)) {
            return [
                'success' => false,
                'message' => 'Webhook URL is required.',
            ];
        }

        $headers = [];

        // Parse custom headers
        if (!empty($config['custom_headers'])) {
            $lines = explode("\n", str_replace("\r", "", $config['custom_headers']));
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line) && strpos($line, ':') !== false) {
                    list($key, $value) = explode(':', $line, 2);
                    $headers[trim($key)] = trim($value);
                }
            }
        }

        // Add secret token header if configured
        if (!empty($config['secret_token'])) {
            $headers['X-AutomationHub-Secret'] = trim($config['secret_token']);
        }

        try {
            $response = HttpClient::post($url, $payload, $headers);

            if (!empty($response['error'])) {
                return [
                    'success'  => false,
                    'message'  => $response['error'],
                    'response' => $response,
                ];
            }

            $is2xx = ($response['status_code'] >= 200 && $response['status_code'] < 300);

            return [
                'success'  => $is2xx,
                'message'  => $is2xx
                    ? "Webhook delivered successfully (HTTP {$response['status_code']})."
                    : "Webhook failed with HTTP status code {$response['status_code']}.",
                'response' => $response,
            ];
        } catch (\InvalidArgumentException $e) {
            return [
                'success' => false,
                'message' => "Validation Error: " . $e->getMessage(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => "Webhook Execution Exception: " . $e->getMessage(),
            ];
        }
    }
}
