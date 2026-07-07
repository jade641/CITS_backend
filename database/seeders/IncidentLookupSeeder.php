<?php

namespace Database\Seeders;

use App\Models\IncidentCategory;
use App\Models\IncidentStatus;
use Illuminate\Database\Seeder;

class IncidentLookupSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Malware', 'slug' => 'malware', 'description' => 'Malicious software activity or infection.'],
            ['name' => 'Phishing', 'slug' => 'phishing', 'description' => 'Deceptive messages or credential harvesting attempts.'],
            ['name' => 'Unauthorized Access', 'slug' => 'unauthorized-access', 'description' => 'Suspicious or confirmed unauthorized access events.'],
            ['name' => 'Data Breach', 'slug' => 'data-breach', 'description' => 'Exposure, exfiltration, or destruction of sensitive data.'],
            ['name' => 'Denial Of Service', 'slug' => 'denial-of-service', 'description' => 'Availability-impacting traffic or system overload.'],
            ['name' => 'Insider Threat', 'slug' => 'insider-threat', 'description' => 'Potential malicious or negligent insider activity.'],
            ['name' => 'Vulnerability', 'slug' => 'vulnerability', 'description' => 'Security weaknesses requiring remediation.'],
            ['name' => 'Suspicious Activity', 'slug' => 'suspicious-activity', 'description' => 'Observed behavior requiring further triage.'],
        ];

        foreach ($categories as $category) {
            IncidentCategory::query()->updateOrCreate(['slug' => $category['slug']], $category);
        }

        $statuses = [
            ['name' => 'New', 'slug' => 'new', 'description' => 'Newly reported incident awaiting triage.', 'sort_order' => 1, 'is_closed' => false],
            ['name' => 'Investigating', 'slug' => 'investigating', 'description' => 'Investigation or response is underway.', 'sort_order' => 2, 'is_closed' => false],
            ['name' => 'Contained', 'slug' => 'contained', 'description' => 'Incident has been contained.', 'sort_order' => 3, 'is_closed' => false],
            ['name' => 'Eradicated', 'slug' => 'eradicated', 'description' => 'Threat has been eradicated.', 'sort_order' => 4, 'is_closed' => false],
            ['name' => 'Recovering', 'slug' => 'recovering', 'description' => 'Systems are recovering.', 'sort_order' => 5, 'is_closed' => false],
            ['name' => 'Closed', 'slug' => 'closed', 'description' => 'Incident fully closed.', 'sort_order' => 6, 'is_closed' => true],
        ];

        foreach ($statuses as $status) {
            IncidentStatus::query()->updateOrCreate(['slug' => $status['slug']], $status);
        }
    }
}
