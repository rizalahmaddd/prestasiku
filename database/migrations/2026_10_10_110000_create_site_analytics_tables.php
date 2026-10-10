<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->char('visitor', 40);
            $table->date('visited_on');
            $table->unsignedTinyInteger('hour');
            $table->string('channel', 20);
            $table->string('source', 80);
            $table->string('referrer_host', 191)->nullable();
            $table->string('campaign', 120)->nullable();
            $table->char('country', 2)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('device', 10);
            $table->string('browser', 40);
            $table->string('os', 40);
            $table->timestamp('created_at')->nullable();

            $table->index(['visited_on', 'visitor']);
        });

        Schema::create('site_events', function (Blueprint $table) {
            $table->id();
            $table->char('visitor', 40);
            $table->date('occurred_on');
            $table->string('name', 30);
            $table->string('label', 191)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['occurred_on', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_events');
        Schema::dropIfExists('site_visits');
    }
};
