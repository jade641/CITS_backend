<?php

namespace App\Support;

class AuthorizationMatrix
{
    public const ADMINISTRATOR = 'administrator';

    public const SECURITY_ANALYST = 'security-analyst';

    public const USER = 'user';

    /**
     * @return array<int, array{name: string, slug: string, description: string}>
     */
    public static function roles(): array
    {
        return [
            [
                'name' => 'Administrator',
                'slug' => self::ADMINISTRATOR,
                'description' => 'Full platform administration and oversight.',
            ],
            [
                'name' => 'Security Analyst',
                'slug' => self::SECURITY_ANALYST,
                'description' => 'Investigates and manages cybersecurity incidents.',
            ],
            [
                'name' => 'User',
                'slug' => self::USER,
                'description' => 'Reports incidents and tracks their own submissions.',
            ],
        ];
    }

    /**
     * @return array<int, array{name: string, slug: string, description: string}>
     */
    public static function permissions(): array
    {
        return [
            ['name' => 'View Dashboard', 'slug' => 'dashboard.view', 'description' => 'View dashboard metrics and widgets.'],
            ['name' => 'View Users', 'slug' => 'users.view', 'description' => 'View user accounts.'],
            ['name' => 'Manage Users', 'slug' => 'users.manage', 'description' => 'Create, edit, and deactivate users.'],
            ['name' => 'View Roles', 'slug' => 'roles.view', 'description' => 'View roles and permissions.'],
            ['name' => 'View Incidents', 'slug' => 'incidents.view', 'description' => 'View incidents that are allowed by policy.'],
            ['name' => 'Create Incidents', 'slug' => 'incidents.create', 'description' => 'Create new incidents.'],
            ['name' => 'Update Incidents', 'slug' => 'incidents.update', 'description' => 'Update incident details.'],
            ['name' => 'Delete Incidents', 'slug' => 'incidents.delete', 'description' => 'Delete incident records.'],
            ['name' => 'Assign Incidents', 'slug' => 'incidents.assign', 'description' => 'Assign incidents to analysts.'],
            ['name' => 'Change Incident Status', 'slug' => 'incidents.change-status', 'description' => 'Transition incident workflow states.'],
            ['name' => 'Comment On Incidents', 'slug' => 'incidents.comment', 'description' => 'Add comments to incidents.'],
            ['name' => 'Upload Incident Evidence', 'slug' => 'incidents.upload', 'description' => 'Upload evidence files for incidents.'],
            ['name' => 'View Audit Logs', 'slug' => 'audit-logs.view', 'description' => 'Review security audit logs.'],
            ['name' => 'View Reports', 'slug' => 'reports.view', 'description' => 'View generated reports.'],
            ['name' => 'Export Reports', 'slug' => 'reports.export', 'description' => 'Export PDF and CSV reports.'],
            ['name' => 'View Analytics', 'slug' => 'analytics.view', 'description' => 'View analytical dashboards.'],
            ['name' => 'View Notifications', 'slug' => 'notifications.view', 'description' => 'View personal notifications.'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function rolePermissions(): array
    {
        $allPermissions = array_map(
            static fn (array $permission): string => $permission['slug'],
            self::permissions(),
        );

        return [
            self::ADMINISTRATOR => $allPermissions,
            self::SECURITY_ANALYST => [
                'dashboard.view',
                'users.view',
                'roles.view',
                'incidents.view',
                'incidents.create',
                'incidents.update',
                'incidents.assign',
                'incidents.change-status',
                'incidents.comment',
                'incidents.upload',
                'audit-logs.view',
                'reports.view',
                'reports.export',
                'analytics.view',
                'notifications.view',
            ],
            self::USER => [
                'dashboard.view',
                'incidents.view',
                'incidents.create',
                'incidents.comment',
                'incidents.upload',
                'notifications.view',
            ],
        ];
    }
}
