<?php

namespace AutomationHub\Tests;

use PHPUnit\Framework\TestCase;
use AutomationHub\Lib\Registry;
use AutomationHub\Lib\RuleEngine;

class RuleEngineTest extends TestCase
{
    protected function setUp(): void
    {
        Registry::reset();
        Registry::init(dirname(__DIR__));
    }

    public function testExecuteRuleWithMockActionSuccess(): void
    {
        $rule = [
            'id'            => 1,
            'name'          => 'Test Webhook Rule',
            'trigger_class' => 'invoice_paid',
            'action_class'  => 'webhook',
            'action_config' => json_encode(['webhook_url' => 'https://example.com/webhook']),
            'enabled'       => 1,
        ];

        $payload = [
            'event'      => 'invoice_paid',
            'invoice_id' => 100,
            'amount'     => 50.00,
        ];

        // RuleEngine should handle execution without throwing exception
        $result = RuleEngine::executeRule($rule, $payload, true);

        $this->assertEquals(1, $result['rule_id']);
        $this->assertEquals('Test Webhook Rule', $result['rule_name']);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('execution_time_ms', $result);
    }

    public function testExecuteRuleWithUnregisteredAction(): void
    {
        $rule = [
            'id'            => 99,
            'name'          => 'Invalid Action Rule',
            'trigger_class' => 'client_add',
            'action_class'  => 'non_existent_action',
            'action_config' => json_encode([]),
            'enabled'       => 1,
        ];

        $payload = ['event' => 'client_add'];

        $result = RuleEngine::executeRule($rule, $payload, true);

        $this->assertEquals('failed', $result['status']);
        $this->assertStringContainsString("is not registered", $result['error_message']);
    }
}
