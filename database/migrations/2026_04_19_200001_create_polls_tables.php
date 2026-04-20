<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contensio_polls', function (Blueprint $table) {
            $table->id();
            $table->string('question', 500);
            $table->enum('status', ['draft', 'active', 'closed'])->default('active')->index();
            $table->enum('show_results', ['always', 'after_vote', 'after_close'])->default('after_vote');
            $table->boolean('allow_guests')->default(true)->comment('Allow non-logged-in users to vote');
            $table->timestamp('ends_at')->nullable()->comment('Auto-close at this date/time');
            $table->timestamps();
        });

        Schema::create('contensio_poll_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained('contensio_polls')->cascadeOnDelete();
            $table->string('label', 300);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['poll_id', 'sort_order']);
        });

        Schema::create('contensio_poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained('contensio_polls')->cascadeOnDelete();
            $table->foreignId('option_id')->constrained('contensio_poll_options')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45);
            $table->timestamp('created_at')->useCurrent();

            // One vote per user (logged-in)
            $table->unique(['poll_id', 'user_id'], 'one_vote_per_user');
            // One vote per IP per poll (guests)
            $table->index(['poll_id', 'ip_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contensio_poll_votes');
        Schema::dropIfExists('contensio_poll_options');
        Schema::dropIfExists('contensio_polls');
    }
};
