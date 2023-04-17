<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Game
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $summary
 * @property string|null $cover_image_id
 * @property mixed $age_ratings
 * @property int $category
 * @property string $first_release_date
 * @property string $source
 * @property int $source_id
 * @property mixed $raw_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Game newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Game newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Game query()
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereAgeRatings($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereCoverImageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereFirstReleaseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereRawData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Game whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Game extends Model
{
    use HasFactory;

    public const CACHE_IMAGE_FORMATS = [
        'cover_big',
        'cover_small'
    ];
    protected $fillable = [
        'name',
        'slug',
        'first_release_date',
        'summary',
        'storyline',
        'cover_image_id',
        'artworks',
        'cached_images',
        'age_ratings',
        'category',
        'is_indie',
        'is_parent',
        'source_parent_id',
        'source',
        'source_id',
        'raw_data'
    ];

    protected $casts = [
      'artwork' => 'array'
    ];

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function themes(): BelongsToMany
    {
        return $this->belongsToMany(Theme::class);
    }

    public function platforms(): BelongsToMany
    {
        return $this->belongsToMany(Platform::class);
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class);
    }
}
