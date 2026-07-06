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

        IncidentStatus::query()->where('slug', 'in-progress')->update(['slug' => 'in_progress']);

        $statuses = [
            ['name' => 'Open', 'slug' => 'open', 'description' => 'Newly reported incident awaiting triage.', 'sort_order' => 1, 'is_closed' => false],
            ['name' => 'Assigned', 'slug' => 'assigned', 'description' => 'Incident has been assigned to an analyst.', 'sort_order' => 2, 'is_closed' => false],
            ['name' => 'In Progress', 'slug' => 'in_progress', 'description' => 'Investigation or response is underway.', 'sort_order' => 3, 'is_closed' => false],
            ['name' => 'Resolved', 'slug' => 'resolved', 'description' => 'Corrective action completed and awaiting closure.', 'sort_order' => 4, 'is_closed' => false],
            ['name' => 'Failed', 'slug' => 'failed', 'description' => 'Analyst marked the investigation as failed.', 'sort_order' => 5, 'is_closed' => true],
            ['name' => 'Closed', 'slug' => 'closed', 'description' => 'Incident fully closed.', 'sort_order' => 6, 'is_closed' => true],
        ];

        foreach ($statuses as $status) {
            IncidentStatus::query()->updateOrCreate(['slug' => $status['slug']], $status);
        }
    }
}
