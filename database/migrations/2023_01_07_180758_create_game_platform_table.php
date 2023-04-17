<?php

use App\Models\Game;
use App\Models\Platform;
use App\Models\Theme;
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
        Schema::create('game_platform', function (Blueprint $table) {
            $table->foreignIdFor(Game::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Platform::class)->constrained()->onDelete('cascade');
            $table->primary(['game_id', 'platform_id']);
            $table->index('game_id');
            $table->index('platform_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_theme');
    }
};
