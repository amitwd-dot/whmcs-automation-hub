<?php

namespace AutomationHub\Tests;

use PHPUnit\Framework\TestCase;
use AutomationHub\Lib\Registry;
use AutomationHub\Lib\HttpClient;
use AutomationHub\Actions\WebhookAction;
use AutomationHub\Actions\SlackAction;
use AutomationHub\Actions\DiscordAction;
use AutomationHub\Actions\EmailAction;

class ActionsTest extends TestCase
{
    protected function setUp(): void
    {
        Registry::reset();
        Registry::init(dirname(__DIR__));
    }

    public function testRegistryDiscoversAll4Actions(): void
    {
        $actions = Registry::getActions();
        $this->assertCount(4, $actions);
        $this->assertArrayHasKey('webhook', $actions);
        $this->assertArrayHasKey('slack', $actions);
        $this->assertArrayHasKey('discord', $actions);
        $this->assertArrayHasKey('email', $actions);
    }

    public function testActionConfigFieldsStructure(): void
    {
        $actions = Registry::getActions();
        foreach ($actions as $key => $action) {
            $fields = $action->getConfigFields();
            $this->assertIsArray($fields);
            $this->assertNotEmpty($fields);

            foreach ($fields as $field) {
                $this->assertArrayHasKey('name', $field);
                $this->assertArrayHasKey('label', $field);
                $this->assertArrayHasKey('type', $field);
            }
        }
    }

    public function testWebhookActionMissingUrlReturnsError(): void
    {
        $action = new WebhookAction();
        $result = $action->execute(['event' => 'test'], ['webhook_url' => '']);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Webhook URL is required', $result['message']);
    }

    public function testHttpClientSsrfValidationBlocksLocalhost(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('SSRF Blocked');
        HttpClient::validateUrl('http://localhost/internal-api');
    }

    public function testHttpClientSsrfValidationBlocksPrivateIP(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('SSRF Blocked');
        HttpClient::validateUrl('http://192.168.1.1/admin');
    }

    public function testHttpClientSsrfValidationBlocksMetadataIP(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('SSRF Blocked');
        HttpClient::validateUrl('http://169.254.169.254/latest/meta-data/');
    }
}
