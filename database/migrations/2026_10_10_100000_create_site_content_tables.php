<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('path');
            $table->string('mime', 100)->nullable();
            $table->unsignedInteger('size')->default(0);
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->string('slug', 60);
            $table->string('name');
            $table->string('icon', 60)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['type', 'slug']);
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name', 60)->nullable();
            $table->string('subtitle')->nullable();
            $table->string('badge', 80)->nullable();
            $table->string('status', 80)->nullable();
            $table->string('color', 9)->default('#1d4ed8');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->unsignedTinyInteger('zoom')->default(15);
            $table->text('address')->nullable();
            $table->foreignId('image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('educations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('program')->nullable();
            $table->string('period_label', 80);
            $table->string('tone', 20)->default('done');
            $table->string('city', 80)->nullable();
            $table->text('description')->nullable();
            $table->string('footnote')->nullable();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('roadmap_steps', function (Blueprint $table) {
            $table->id();
            $table->string('period', 60);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('tag', 80)->nullable();
            $table->string('state', 20)->default('normal');
            $table->string('jump_target', 60)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('interests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('icon', 60)->default('star');
            $table->string('tone', 20)->default('royal');
            $table->json('tags')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('stats', function (Blueprint $table) {
            $table->id();
            $table->string('group', 20)->default('summary');
            $table->unsignedInteger('value')->default(0);
            $table->string('suffix', 10)->nullable();
            $table->string('label');
            $table->string('caption')->nullable();
            $table->string('tone', 20)->default('slate');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->text('quote');
            $table->string('name');
            $table->string('role')->nullable();
            $table->foreignId('image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('gallery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('badge', 80)->nullable();
            $table->string('place')->nullable();
            $table->date('taken_on')->nullable();
            $table->foreignId('image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('organizer')->nullable();
            $table->text('description')->nullable();
            $table->string('badge', 80);
            $table->string('icon', 60)->default('medal');
            $table->string('tone', 20)->default('amber');
            $table->string('medal', 20)->nullable();
            $table->string('level', 20)->default('nasional');
            $table->date('achieved_on');
            $table->json('tags')->nullable();
            $table->string('reference_no', 80)->nullable();
            $table->string('keywords')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->foreignId('image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('gallery_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->string('cert_title')->nullable();
            $table->string('cert_organizer')->nullable();
            $table->string('cert_number', 120)->nullable();
            $table->string('cert_predicate')->nullable();
            $table->text('cert_description')->nullable();
            $table->json('cert_tags')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('achievement_category', function (Blueprint $table) {
            $table->foreignId('achievement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();

            $table->primary(['achievement_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievement_category');
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('gallery_items');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('stats');
        Schema::dropIfExists('interests');
        Schema::dropIfExists('roadmap_steps');
        Schema::dropIfExists('educations');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('media');
    }
};
