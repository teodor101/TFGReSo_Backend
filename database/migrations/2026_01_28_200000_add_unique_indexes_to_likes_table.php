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
        Schema::table('likes', function (Blueprint $table) {
            $table->unique(['post_id', 'user_id'], 'likes_post_user_unique');
            $table->unique(['comment_id', 'user_id'], 'likes_comment_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('likes', function (Blueprint $table) {
            $table->dropUnique('likes_post_user_unique');
            $table->dropUnique('likes_comment_user_unique');
        });
    }
};

