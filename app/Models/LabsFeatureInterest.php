<?php

namespace App\Models;

use App\Domain\Labs\LabsInterestType;
use Database\Factories\LabsFeatureInterestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabsFeatureInterest extends Model
{
    /** @use HasFactory<LabsFeatureInterestFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'labs_feature_id', 'type'];

    protected function casts(): array
    {
        return [
            'type' => LabsInterestType::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function labsFeature(): BelongsTo
    {
        return $this->belongsTo(LabsFeature::class);
    }
}
