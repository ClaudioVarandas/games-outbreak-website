<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->dateTime('first_release_date');
            $table->text('summary')->nullable();
            $table->text('storyline')->nullable();
            $table->string('cover_image_id')->nullable();
            $table->jsonb('artworks')->nullable();
            $table->jsonb('cached_images')->nullable();
            $table->jsonb('age_ratings')->default('[]');
            $table->integer('category');
            $table->integer('is_indie')->default(false);
            $table->boolean('is_parent')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('source');
            $table->bigInteger('source_id');
            $table->integer('source_parent_id')->nullable();
            $table->jsonb('raw_data')->default('[]');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('games');
    }
};
