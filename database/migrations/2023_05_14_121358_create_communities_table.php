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
        Schema::create('communities', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('community_name');
            $table->string('community_description');
            $table->string('community_icon_image_url');
            $table->string('community_banner_image_url');
            $table->foreignId('owner_user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('hide_posts_from_home')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communities');
    }
};
