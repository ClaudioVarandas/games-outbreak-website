<?php

namespace App\Console\Commands\Igdb;

use App\Models\Company;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Platform;
use App\Models\Theme;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use MarcReichel\IGDBLaravel\Models\Game as IGDBGame;

final class FetchGames extends Command
{
    private const GAME_FIELDS = [
        'artworks' => ['*'],
        'cover' => ['image_id'],
        'age_ratings' => ['category', 'rating'],
        'game_modes' => ['name', 'slug'],
        'genres' => ['name', 'slug'],
        'involved_companies' => ['*', 'company.name'],
        'platforms' => ['abbreviation', 'name', 'slug'],
        'release_dates' => ['date', 'platform.abbreviation', 'region'],
        'videos' => ['video_id'],
        'keywords' => ['name'],
        'themes' => ['name'],
        'websites',
        'screenshots' => ['*'],
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'go:igdb:fetch
                            {startDate? : The start date (d-m-Y)}
                            {endDate? : The end date (d-m-Y)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch upcoming games';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $inputStartDate = $this->input->getArgument('startDate');
        $inputEndDate = $this->input->getArgument('endDate');

        try {
            $startDate = Carbon::parse($inputStartDate);
            $endDate = Carbon::parse($inputEndDate);
        } catch (Exception $e) {
            $this->error($e->getMessage());

            return 1;
        }

        if (is_null($inputEndDate)) {
            $endDate->addDays(14);
        }

        if ($endDate <= $startDate) {
            $this->error("The end date must be greater than the start date.");

            return 1;
        }

        $upcomingGames = IGDBGame::with(self::GAME_FIELDS)
            ->whereIn('platforms', [6, 169, 167, 130, 48, 49])
            ->whereBetween('first_release_date', $startDate, $endDate)
            ->orderBy('first_release_date', 'asc')
            ->limit(500)
            ->get();

        foreach ($upcomingGames as $igdbGame) {
            try {
                DB::beginTransaction();

                $game = $this->handleGameData($igdbGame);

                if (in_array($game->category, [2])) {
                    $parentIgdbGame = IGDBGame::with(self::GAME_FIELDS)
                        ->where('id', $igdbGame->parent_game)
                        ->first();

                    $this->handleGameData($parentIgdbGame);

                    if (is_null($igdbGame->cover_image_id)
                        && !empty($parentIgdbGame->cover['image_id'])) {
                        $game->cover_image_id = $parentIgdbGame->cover['image_id'];
                        $game->save();
                    }
                }

                //$this->handleGameImages($igdbGame);

                DB::commit();
            } catch (Exception $e) {
                dump($e->getTrace());
                DB::rollBack();
                $this->error($e->getMessage());
                continue;
            }
        }

        return 0;
    }

    private function handleGameImages(Game $game)
    {
        foreach ($game::CACHE_IMAGE_FORMATS as $format) {


        }
    }

    private function handleGameData(IGDBGame $game): Game
    {
        [
            $gameData,
            $genres,
            $themes,
            $platforms,
            $companies
        ] = $this->transformIDDBGameIntoDataArrays($game);

        $genreIds = [];
        foreach ($genres as $genre) {
            $genreIds[] = Genre::updateOrCreate($genre)->id;
        }
        $themeIds = [];
        foreach ($themes as $theme) {
            $themeIds[] = Theme::updateOrCreate($theme)->id;
        }
        $platformIds = [];
        foreach ($platforms as $platform) {
            $platformIds[] = Platform::updateOrCreate($platform)->id;
        }
        $companiesIds = [];
        foreach ($companies as $company) {
            $companyModel = Company::updateOrCreate(
                Arr::only($company, ['name', 'slug', 'is_developer', 'is_publisher'])
            );

            $companiesIds[$companyModel->id] = Arr::only($company, [
                'is_developer',
                'is_publisher',
                'is_porting',
                'is_supporting'
            ]);
        }

        /** @var Game $game */
        $game = Game::updateOrCreate([
            'source' => 'igdb',
            'source_id' => $game->id
        ], $gameData);

        $game->genres()->sync($genreIds);
        $game->themes()->sync($themeIds);
        $game->platforms()->sync($platformIds);
        $game->companies()->sync($companiesIds);

        return $game;
    }

    /**
     * @param IGDBGame $game
     * @param Game|null $parentGame
     * @return array
     */
    private function transformIDDBGameIntoDataArrays(IGDBGame $game): array
    {
        $ageRatings = $game->age_ratings ?? [];

        $isIndie = false;
        $genres = $this->processGenres($game, $isIndie);
        $themes = $this->processThemes($game);
        $platforms = $this->processPlatforms($game);
        $companies = $this->processCompanies($game);

        $isParent = true;
        $parentId = null;
        if (isset($game->version_parent)) {
            $isParent = false;
            $parentId = $game->version_parent;
        }

        if (isset($game->parent_game)) {
            $parentId = $game->parent_game;
        }

        return [
            [
                'name' => $game->name,
                'slug' => $game->slug,
                'first_release_date' => $game->first_release_date,
                'summary' => $game->summary,
                'storyline' => $game->storyline,
                'cover_image_id' => $game->cover['image_id'] ?? null,
                'artworks' => json_encode($game->artworks ?? []),
                'age_ratings' => json_encode($ageRatings),
                'category' => $game->category,
                'is_indie' => $isIndie,
                'is_parent' => $isParent,
                'source_parent_id' => $parentId,
                'raw_data' => json_encode($game)
            ],
            $genres,
            $themes,
            $platforms,
            $companies
        ];
    }

    private function processGenres(IGDBGame $game, bool &$isIndie): array
    {
        $genres = $game->genres ?? [];

        foreach ($genres as $key => $genre) {
            if ($genre['id'] === 32) {
                $isIndie = true;
            }
            unset($genre['id']);
            unset($genre['slug']);
            $genre['slug'] = Str::slug($genre['name']);
            $genres[$key] = $genre;
        }

        return $genres;
    }

    private function processThemes(IGDBGame $game): array
    {
        $themes = $game->themes ?? [];

        foreach ($themes as $key => $theme) {
            unset($theme['id']);
            unset($theme['slug']);
            $theme['slug'] = Str::slug($theme['name']);
            $themes[$key] = $theme;
        }

        return $themes;
    }

    private function processPlatforms(IGDBGame $game): array
    {
        $platforms = $game->platforms ?? [];

        foreach ($platforms as $key => $platform) {
            unset($platform['id']);
            unset($platform['slug']);
            $platform['slug'] = Str::slug($platform['name']);
            $platform['abbreviation'] = $platform['abbreviation'] ?? $platform['slug'];
            $platforms[$key] = $platform;
        }

        return $platforms;
    }

    private function processCompanies(IGDBGame $game): array
    {
        $companies = $game->involved_companies ?? [];

        foreach ($companies as $key => $iCompany) {
            $iCompany['name'] = $iCompany['company']['name'];
            $iCompany['slug'] = Str::slug($iCompany['name']);
            $iCompany['is_developer'] = $iCompany['developer'];
            $iCompany['is_publisher'] = $iCompany['publisher'];
            $iCompany['is_porting'] = $iCompany['porting'];
            $iCompany['is_supporting'] = $iCompany['supporting'];
            unset($iCompany['id']);
            unset($iCompany['company']);
            unset($iCompany['game']);
            unset($iCompany['checksum']);
            unset($iCompany['created_at']);
            unset($iCompany['updated_at']);
            unset($iCompany['developer']);
            unset($iCompany['publisher']);
            unset($iCompany['porting']);
            unset($iCompany['supporting']);
            $companies[$key] = $iCompany;
        }

        return $companies;
    }

}
