<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_openings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('location')->default('Biyagama');
            $table->string('employment_type')->nullable();
            $table->text('summary')->nullable();
            $table->text('requirements')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_opening_id')->nullable()->constrained('job_openings')->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone', 30);
            $table->string('street_address')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('position_applied');
            $table->json('employment_history')->nullable();
            $table->text('cover_letter')->nullable();
            $table->string('resume_path')->nullable();
            $table->string('status', 32)->default('new');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });

        if (Schema::hasTable('job_openings')) {
            DB::table('job_openings')->insert([
                [
                    'title' => 'Graphic Designer (Intermediate / Trainee)',
                    'slug' => 'graphic-designer-intermediate-trainee',
                    'location' => 'Biyagama',
                    'employment_type' => 'Full-time',
                    'summary' => 'Join our creative team to design print-ready artwork, brand assets, and promotional materials.',
                    'requirements' => implode("\n", [
                        'Must have valid Graphic Design certification or higher academic qualification.',
                        'Portfolio required (for intermediate applicants; optional for trainees).',
                        'Minimum 1 year experience for intermediate level.',
                        'Must face a practical session during the interview.',
                        'English literacy will be considered.',
                    ]),
                    'sort_order' => 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Accounts & Administrative Assistant',
                    'slug' => 'accounts-administrative-assistant',
                    'location' => 'Biyagama',
                    'employment_type' => 'Full-time',
                    'summary' => 'Support our finance and office operations with accurate records and smooth day-to-day administration.',
                    'requirements' => implode("\n", [
                        'G.C.E. (A/L) in Commerce Stream required.',
                        'Diploma in Accounting / Business Administration is an advantage.',
                        'Skilled in MS Office & accounting software.',
                        'Good communication (Sinhala & English) & multitasking ability.',
                        'Experienced or Trainee candidates may apply.',
                    ]),
                    'sort_order' => 2,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('job_openings');
    }
};
