<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications_curr', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('notification_of_user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_read')->default(false);
            $table->string('message');
            $table->string('type')->constrainted('like', 'comment');
            $table->foreignId('on_post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('notification_from_user_id')->constrained('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications_curr');
    }
};