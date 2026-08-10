<?php

namespace AutomationHub\Tests;

use PHPUnit\Framework\TestCase;
use AutomationHub\Lib\Registry;
use AutomationHub\Triggers\OrderPlacedTrigger;
use AutomationHub\Triggers\InvoicePaidTrigger;
use AutomationHub\Triggers\InvoicePaymentFailedTrigger;
use AutomationHub\Triggers\ServiceSuspendedTrigger;
use AutomationHub\Triggers\ServiceUnsuspendedTrigger;
use AutomationHub\Triggers\ServiceTerminatedTrigger;
use AutomationHub\Triggers\TicketOpenTrigger;
use AutomationHub\Triggers\TicketUserReplyTrigger;
use AutomationHub\Triggers\DomainExpiringTrigger;
use AutomationHub\Triggers\ClientAddTrigger;

class TriggersTest extends TestCase
{
    protected function setUp(): void
    {
        Registry::reset();
        Registry::init(dirname(__DIR__));
    }

    public function testRegistryDiscoversAll10Triggers(): void
    {
        $triggers = Registry::getTriggers();
        $this->assertCount(10, $triggers);
        $this->assertArrayHasKey('order_placed', $triggers);
        $this->assertArrayHasKey('invoice_paid', $triggers);
        $this->assertArrayHasKey('invoice_payment_failed', $triggers);
        $this->assertArrayHasKey('service_suspended', $triggers);
        $this->assertArrayHasKey('service_unsuspended', $triggers);
        $this->assertArrayHasKey('service_terminated', $triggers);
        $this->assertArrayHasKey('ticket_open', $triggers);
        $this->assertArrayHasKey('ticket_user_reply', $triggers);
        $this->assertArrayHasKey('domain_expiring', $triggers);
        $this->assertArrayHasKey('client_add', $triggers);
    }

    public function testOrderPlacedTriggerPayload(): void
    {
        $trigger = new OrderPlacedTrigger();
        $params = ['OrderID' => 1024, 'OrderNumber' => 'ORD-982341', 'UserID' => 42, 'Total' => 149.00];
        $payload = $trigger->getPayload($params);

        $this->assertEquals('order_placed', $payload['event']);
        $this->assertEquals(1024, $payload['order_id']);
        $this->assertEquals('ORD-982341', $payload['order_number']);
        $this->assertEquals(42, $payload['user_id']);
        $this->assertEquals(149.00, $payload['amount']);
    }

    public function testInvoicePaidTriggerPayload(): void
    {
        $trigger = new InvoicePaidTrigger();
        $params = ['invoiceid' => 10582, 'userid' => 88, 'amountpaid' => 299.99, 'paymentmethod' => 'paypal'];
        $payload = $trigger->getPayload($params);

        $this->assertEquals('invoice_paid', $payload['event']);
        $this->assertEquals(10582, $payload['invoice_id']);
        $this->assertEquals(88, $payload['user_id']);
        $this->assertEquals(299.99, $payload['amount']);
        $this->assertEquals('paypal', $payload['payment_method']);
    }

    public function testTicketOpenTriggerPayload(): void
    {
        $trigger = new TicketOpenTrigger();
        $params = [
            'ticketid'   => 7819,
            'ticketmask' => 'ABC-102938',
            'userid'     => 120,
            'deptname'   => 'Technical Support',
            'subject'    => 'Server issue',
            'message'    => 'Help needed',
            'priority'   => 'High',
        ];
        $payload = $trigger->getPayload($params);

        $this->assertEquals('ticket_open', $payload['event']);
        $this->assertEquals(7819, $payload['ticket_id']);
        $this->assertEquals('ABC-102938', $payload['ticket_mask']);
        $this->assertEquals('Technical Support', $payload['dept_name']);
        $this->assertEquals('High', $payload['priority']);
    }

    public function testSamplePayloadFixturesMatchFormat(): void
    {
        $triggers = Registry::getTriggers();
        foreach ($triggers as $key => $trigger) {
            $sample = $trigger->getSamplePayload();
            $this->assertIsArray($sample);
            $this->assertArrayHasKey('event', $sample);
            $this->assertEquals($key, $sample['event']);

            // Verify corresponding fixture file exists
            $fixturePath = dirname(__DIR__) . "/tests/fixtures/{$key}.json";
            $this->assertFileExists($fixturePath);
            $fixtureData = json_decode(file_get_contents($fixturePath), true);
            $this->assertIsArray($fixtureData);
            $this->assertEquals($key, $fixtureData['event']);
        }
    }
}
