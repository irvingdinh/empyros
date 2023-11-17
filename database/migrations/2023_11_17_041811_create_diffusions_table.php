<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('diffusions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');

            $table->string('style');
            $table->string('status')->default('PENDING');

            $table->json('input');
            $table->json('output')->nullable();
            $table->json('error')->nullable();

            $table->string('attachment')->nullable();
            $table->integer('attachment_width')->nullable();
            $table->integer('attachment_height')->nullable();
            $table->bigInteger('attachment_file_size')->nullable();

            $table->string('thumbnail')->nullable();
            $table->integer('thumbnail_width')->nullable();
            $table->integer('thumbnail_height')->nullable();
            $table->bigInteger('thumbnail_file_size')->nullable();

            $table->boolean('is_enhance')->default(false);

            $table->string('privacy')->default('PRIVATE');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->on('users')->references('id');

            $table->index('style');
            $table->index('status');
            $table->index('privacy');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diffusions');
    }
};
