<?php

use App\Models\Company;
use App\Models\Game;
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
        Schema::create('company_game', function (Blueprint $table) {
            $table->foreignIdFor(Game::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Company::class)->constrained()->onDelete('cascade');
            $table->primary(['company_id', 'game_id']);
            $table->index('game_id');
            $table->index('company_id');

            $table->boolean('is_developer')->default(false);
            $table->boolean('is_publisher')->default(false);
            $table->boolean('is_porting')->default(false);
            $table->boolean('is_supporting')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_game');
    }
};
