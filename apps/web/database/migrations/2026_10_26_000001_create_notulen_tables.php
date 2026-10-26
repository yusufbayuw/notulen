<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
        Schema::create('organizations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });
        Schema::create('organization_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20);
            $table->timestamps();
            $table->unique(['organization_id', 'user_id']);
        });
        Schema::create('meetings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('language', 8)->default('id');
            $table->string('status', 30)->default('uploaded');
            $table->timestamps();
            $table->index(['organization_id', 'created_at']);
        });
        Schema::create('recordings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meeting_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('object_path', 512);
            $table->string('original_filename');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->char('sha256', 64);
            $table->timestamps();
        });
        Schema::create('processing_jobs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recording_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('type', 30)->default('transcribe');
            $table->string('status', 30)->default('queued');
            $table->string('error_code', 80)->nullable();
            $table->timestamps();
        });
        Schema::create('transcript_segments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->unsignedInteger('start_ms');
            $table->unsignedInteger('end_ms');
            $table->string('speaker_label', 60)->nullable();
            $table->text('text');
            $table->timestamps();
            $table->unique(['meeting_id', 'position']);
            $table->index(['organization_id', 'meeting_id']);
        });
        Schema::create('meeting_summaries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meeting_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('model_name')->nullable();
            $table->json('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_summaries');
        Schema::dropIfExists('transcript_segments');
        Schema::dropIfExists('processing_jobs');
        Schema::dropIfExists('recordings');
        Schema::dropIfExists('meetings');
        Schema::dropIfExists('organization_members');
        Schema::dropIfExists('organizations');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
