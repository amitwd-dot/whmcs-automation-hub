# Contributing to WHMCS Automation Hub

Thank you for considering contributing to **WHMCS Automation Hub** by [Web Wave Digital](https://webwavedigital.co.in)!

This project is built around **one file = one integration**. Adding a new event trigger or action requires adding a single PHP file without editing any core engine code.

---

## 🚀 How to Add a New Trigger (Under 30 Minutes)

Every trigger represents a WHMCS event and implements `AutomationHub\Lib\TriggerInterface`.

### Step 1: Create a PHP file in `/triggers/`
Name your file using upper camel case ending with `Trigger.php` (e.g. `CustomInvoiceCreatedTrigger.php`).

```php
<?php

namespace AutomationHub\Triggers;

use AutomationHub\Lib\TriggerInterface;

/**
 * Class CustomInvoiceCreatedTrigger
 */
class CustomInvoiceCreatedTrigger implements TriggerInterface
{
    public function getKey(): string
    {
        return 'custom_invoice_created'; // Unique string identifier
    }

    public function getName(): string
    {
        return 'Custom Invoice Created'; // Admin UI Display Name
    }

    public function getDescription(): string
    {
        return 'Fires whenever a new invoice is generated.';
    }

    public function getHookName(): string
    {
        return 'InvoiceCreationAdminArea'; // WHMCS Hook Name
    }

    public function getPayload(array $hookParams): array
    {
        // Normalize raw WHMCS hook parameters into a clean array
        return [
            'event'      => 'custom_invoice_created',
            'invoice_id' => (int)($hookParams['invoiceid'] ?? 0),
            'amount'     => (float)($hookParams['amount'] ?? 0.00),
            'timestamp'  => date('c'),
        ];
    }

    public function getSamplePayload(): array
    {
        // Return a realistic payload used when admins click "Test Rule"
        return [
            'event'      => 'custom_invoice_created',
            'invoice_id' => 8891,
            'amount'     => 120.00,
            'timestamp'  => date('c'),
        ];
    }
}
```

### Step 2: Test your Trigger
Create a corresponding fixture JSON file in `/tests/fixtures/custom_invoice_created.json` and run PHPUnit tests:

```bash
vendor/bin/phpunit tests/TriggersTest.php
```

---

## 🎯 How to Add a New Action (Under 30 Minutes)

Every action represents a destination integration (e.g., Telegram, Microsoft Teams, Twilio SMS) and implements `AutomationHub\Lib\ActionInterface`.

### Step 1: Create a PHP file in `/actions/`
Name your file ending with `Action.php` (e.g. `TelegramAction.php`).

```php
<?php

namespace AutomationHub\Actions;

use AutomationHub\Lib\ActionInterface;
use AutomationHub\Lib\HttpClient;

/**
 * Class TelegramAction
 */
class TelegramAction implements ActionInterface
{
    public function getKey(): string
    {
        return 'telegram'; // Unique string identifier
    }

    public function getName(): string
    {
        return 'Telegram Bot Message'; // Admin UI Display Name
    }

    public function getDescription(): string
    {
        return 'Posts a message to a Telegram Chat using a Telegram Bot Token.';
    }

    public function getConfigFields(): array
    {
        // Form fields automatically rendered in the WHMCS Admin UI
        return [
            [
                'name'        => 'bot_token',
                'label'       => 'Telegram Bot Token',
                'type'        => 'password',
                'description' => 'Bot token received from @BotFather',
                'required'    => true,
            ],
            [
                'name'        => 'chat_id',
                'label'       => 'Target Chat ID',
                'type'        => 'text',
                'description' => 'Target Telegram Chat or Channel ID',
                'required'    => true,
            ],
        ];
    }

    public function execute(array $payload, array $config): array
    {
        $botToken = trim($config['bot_token'] ?? '');
        $chatId = trim($config['chat_id'] ?? '');

        if (empty($botToken) || empty($chatId)) {
            return ['success' => false, 'message' => 'Bot Token and Chat ID are required.'];
        }

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
        $text = "⚡ WHMCS Event: " . ($payload['event'] ?? 'Alert');

        try {
            $response = HttpClient::post($url, [
                'chat_id'    => $chatId,
                'text'       => $text,
                'parse_mode' => 'HTML',
            ]);

            $is2xx = ($response['status_code'] >= 200 && $response['status_code'] < 300);

            return [
                'success'  => $is2xx,
                'message'  => $is2xx ? 'Telegram message sent.' : 'Telegram API returned error.',
                'response' => $response,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}
```

---

## 📋 Pull Request Checklist

Before submitting your PR:
1. Ensure your code follows **PSR-12** formatting.
2. Include PHPDoc comments on your class and public methods.
3. Run `vendor/bin/phpunit` to ensure all unit tests pass.
4. Verify that new HTTP actions use `HttpClient::post()` to benefit from built-in SSRF protection.
