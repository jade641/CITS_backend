<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update users table with role column
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('Analyst')->after('status');
        });

        // 2. Update incidents table with severity scoring and findings columns
        Schema::table('incidents', function (Blueprint $table) {
            $table->string('confidentiality_impact', 20)->nullable()->after('affected_asset');
            $table->string('integrity_impact', 20)->nullable()->after('confidentiality_impact');
            $table->string('availability_impact', 20)->nullable()->after('integrity_impact');
            $table->integer('affected_systems_count')->default(0)->after('availability_impact');
            $table->string('data_sensitivity', 50)->nullable()->after('affected_systems_count');
            $table->boolean('severity_override')->default(false)->after('data_sensitivity');
            $table->text('severity_override_justification')->nullable()->after('severity_override');

            $table->string('root_cause_category', 50)->nullable()->after('resolution_notes');
            $table->text('root_cause_explanation')->nullable()->after('root_cause_category');
            $table->text('lessons_learned')->nullable()->after('root_cause_explanation');
            $table->text('rejection_reason')->nullable()->after('lessons_learned');
        });

        // 3. Update incident_attachments table with file_hash and description columns
        Schema::table('incident_attachments', function (Blueprint $table) {
            $table->string('file_hash', 64)->nullable()->after('mime_type');
            $table->text('description')->nullable()->after('file_hash');
        });

        // 4. Create incident_timelines table
        Schema::create('incident_timelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('incidents')->cascadeOnDelete();
            $table->timestamp('occurred_at');
            $table->text('description');
            $table->timestamps();
        });

        // 5. Create incident_iocs table
        Schema::create('incident_iocs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('incidents')->cascadeOnDelete();
            $table->string('type'); // IP, domain, hash, email
            $table->string('value');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 6. Create incident_affected_systems table
        Schema::create('incident_affected_systems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('incidents')->cascadeOnDelete();
            $table->string('asset_name');
            $table->string('asset_type');
            $table->string('impact_level'); // None/Low/Medium/High
            $table->timestamps();
        });

        // 7. Create incident_actions_taken table
        Schema::create('incident_actions_taken', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('incidents')->cascadeOnDelete();
            $table->timestamp('occurred_at');
            $table->text('action');
            $table->string('performed_by');
            $table->timestamps();
        });

        // 8. Create incident_remediation_actions table
        Schema::create('incident_remediation_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('incidents')->cascadeOnDelete();
            $table->text('description');
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->date('due_date');
            $table->string('status'); // Pending/In Progress/Done
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_remediation_actions');
        Schema::dropIfExists('incident_actions_taken');
        Schema::dropIfExists('incident_affected_systems');
        Schema::dropIfExists('incident_iocs');
        Schema::dropIfExists('incident_timelines');

        Schema::table('incident_attachments', function (Blueprint $table) {
            $table->dropColumn(['file_hash', 'description']);
        });

        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn([
                'confidentiality_impact',
                'integrity_impact',
                'availability_impact',
                'affected_systems_count',
                'data_sensitivity',
                'severity_override',
                'severity_override_justification',
                'root_cause_category',
                'root_cause_explanation',
                'lessons_learned',
                'rejection_reason',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
