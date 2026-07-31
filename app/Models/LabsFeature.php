<?php

namespace App\Models;

use App\Domain\Labs\LabsFeatureStatus;
use Database\Factories\LabsFeatureFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabsFeature extends Model
{
    /** @use HasFactory<LabsFeatureFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'why_it_matters',
        'status',
        'notify_enabled',
        'beta_enabled',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'status' => LabsFeatureStatus::class,
            'notify_enabled' => 'boolean',
            'beta_enabled' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function interests(): HasMany
    {
        return $this->hasMany(LabsFeatureInterest::class);
    }

    /**
     * @param  Builder<LabsFeature>  $query
     * @return Builder<LabsFeature>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->orderBy('sort_order');
    }
}
