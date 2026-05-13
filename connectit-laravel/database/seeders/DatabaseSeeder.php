<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SlaPolicy;
use App\Models\SystemSetting;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();
        $this->seedSlaPolicies();
        $this->seedSystemSettings();
    }

    private function seedUsers(): void
    {
        $users = [
            ['name' => 'Ultra Admin',  'email' => 'ultra@connectit.local',   'role' => UserRole::UltraSuperAdmin],
            ['name' => 'Super Admin',  'email' => 'super@connectit.local',   'role' => UserRole::SuperAdmin],
            ['name' => 'Admin User',   'email' => 'admin@connectit.local',   'role' => UserRole::Admin],
            ['name' => 'Agent One',    'email' => 'agent1@connectit.local',  'role' => UserRole::Agent],
            ['name' => 'Agent Two',    'email' => 'agent2@connectit.local',  'role' => UserRole::Agent],
            ['name' => 'Sub Admin',    'email' => 'subadmin@connectit.local','role' => UserRole::SubAdmin],
            ['name' => 'Regular User', 'email' => 'user@connectit.local',    'role' => UserRole::User],
        ];

        foreach ($users as $u) {
            User::firstOrCreate(['email' => $u['email']], [
                'uid'           => Str::uuid()->toString(),
                'name'          => $u['name'],
                'password_hash' => Hash::make('password'),
                'role'          => $u['role'],
                'is_active'     => true,
                'provider'      => 'email',
                'department'    => 'IT',
            ]);
        }
    }

    private function seedSlaPolicies(): void
    {
        $policies = [
            ['name' => 'Critical SLA', 'priority' => '1 - Critical', 'response_time_hours' => 1,  'resolution_time_hours' => 4],
            ['name' => 'High SLA',     'priority' => '2 - High',     'response_time_hours' => 2,  'resolution_time_hours' => 8],
            ['name' => 'Moderate SLA', 'priority' => '3 - Moderate', 'response_time_hours' => 4,  'resolution_time_hours' => 24],
            ['name' => 'Low SLA',      'priority' => '4 - Low',      'response_time_hours' => 8,  'resolution_time_hours' => 72],
        ];

        foreach ($policies as $p) {
            SlaPolicy::firstOrCreate(
                ['priority' => $p['priority'], 'category' => null],
                array_merge($p, ['is_active' => true])
            );
        }
    }

    private function seedSystemSettings(): void
    {
        $settings = [
            ['setting_key' => 'app_name',           'setting_value' => 'ConnectIT ITSM',    'setting_type' => 'string'],
            ['setting_key' => 'company_name',        'setting_value' => 'My Company',        'setting_type' => 'string'],
            ['setting_key' => 'default_sla_response','setting_value' => '4',                 'setting_type' => 'integer'],
            ['setting_key' => 'default_sla_resolve', 'setting_value' => '24',                'setting_type' => 'integer'],
            ['setting_key' => 'email_notifications', 'setting_value' => 'true',              'setting_type' => 'boolean'],
            ['setting_key' => 'whatsapp_notifications','setting_value' => 'false',           'setting_type' => 'boolean'],
            ['setting_key' => 'ai_suggestions',      'setting_value' => 'true',              'setting_type' => 'boolean'],
            ['setting_key' => 'primary_color',       'setting_value' => '#81B532',           'setting_type' => 'string'],
        ];

        foreach ($settings as $s) {
            SystemSetting::firstOrCreate(['setting_key' => $s['setting_key']], $s);
        }
    }
}
