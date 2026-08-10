<?php

namespace AutomationHub\Triggers;

use AutomationHub\Lib\TriggerInterface;
use WHMCS\Database\Capsule;

/**
 * Class DomainExpiringTrigger
 *
 * Custom cron-based trigger checking for domains expiring in 30, 14, or 7 days.
 * Hook: DailyCronJob
 *
 * @package AutomationHub\Triggers
 * @author Web Wave Digital <https://webwavedigital.co.in>
 */
class DomainExpiringTrigger implements TriggerInterface
{
    public function getKey(): string
    {
        return 'domain_expiring';
    }

    public function getName(): string
    {
        return 'Domain Expiring Soon';
    }

    public function getDescription(): string
    {
        return 'Fires during daily cron job for active domains expiring in exactly 30, 14, or 7 days.';
    }

    public function getHookName(): string
    {
        return 'DailyCronJob';
    }

    public function getPayload(array $hookParams): array
    {
        // If pre-normalized payload array passed directly
        if (isset($hookParams['domain_name'])) {
            return array_merge(['event' => 'domain_expiring'], $hookParams);
        }

        // Fetch domains expiring in 30, 14, or 7 days from WHMCS database
        $expiringDomains = $this->fetchExpiringDomains([30, 14, 7]);

        return [
            'event'            => 'domain_expiring',
            'expiring_count'   => count($expiringDomains),
            'domains'          => $expiringDomains,
            'windows_checked'  => [30, 14, 7],
            'timestamp'        => date('c'),
        ];
    }

    /**
     * Query WHMCS database for active domains matching expiration day windows.
     *
     * @param array<int> $daysWindows List of day counts e.g. [30, 14, 7]
     * @return array List of domain records
     */
    public function fetchExpiringDomains(array $daysWindows = [30, 14, 7]): array
    {
        try {
            $results = [];
            $today = date('Y-m-d');

            foreach ($daysWindows as $days) {
                $targetDate = date('Y-m-d', strtotime("+{$days} days"));

                $domains = Capsule::table('tbldomains')
                    ->where('status', 'Active')
                    ->whereDate('expirydate', '=', $targetDate)
                    ->get();

                foreach ($domains as $domain) {
                    $results[] = [
                        'domain_id'    => (int)$domain->id,
                        'user_id'      => (int)$domain->userid,
                        'domain_name'  => (string)$domain->domain,
                        'expiry_date'  => (string)$domain->expirydate,
                        'days_left'    => $days,
                        'registrar'    => (string)$domain->registrar,
                        'recurring'    => (float)$domain->recurringamount,
                    ];
                }
            }

            return $results;
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getSamplePayload(): array
    {
        return [
            'event'          => 'domain_expiring',
            'expiring_count' => 2,
            'domains'        => [
                [
                    'domain_id'   => 402,
                    'user_id'     => 88,
                    'domain_name' => 'client-portal-example.com',
                    'expiry_date' => date('Y-m-d', strtotime('+14 days')),
                    'days_left'   => 14,
                    'registrar'   => 'enom',
                    'recurring'   => 14.99,
                ],
                [
                    'domain_id'   => 519,
                    'user_id'     => 104,
                    'domain_name' => 'innovative-tech.org',
                    'expiry_date' => date('Y-m-d', strtotime('+7 days')),
                    'days_left'   => 7,
                    'registrar'   => 'namecheap',
                    'recurring'   => 19.99,
                ],
            ],
            'windows_checked' => [30, 14, 7],
            'timestamp'       => date('c'),
        ];
    }
}
