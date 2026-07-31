<?php

namespace Database\Seeders;

use App\Domain\Labs\LabsFeatureStatus;
use App\Models\LabsFeature;

/**
 * `PO-U19.1-001` — real, published content, not a `Database\Seeders\Demo\*`
 * fixture. This is the first real `LabsFeature` record in the repository
 * (zero existed before this): "Native Mobile Apps," `InDevelopment`, per
 * the founder-supplied copy in the commission itself — not invented here.
 * Idempotent (`updateOrCreate` by slug) so re-running never duplicates it.
 */
class LabsMobileAppFeatureSeeder
{
    public const SLUG = 'native-mobile-apps';

    public function run(): LabsFeature
    {
        return LabsFeature::updateOrCreate(
            ['slug' => self::SLUG],
            [
                'title' => 'Native apps are on the roadmap.',
                'summary' => "Native iPhone and Android experiences are currently being built with the same deterministic intelligence that powers SlipGuard on the web.",
                'why_it_matters' => "Our goal is not simply to shrink the website onto a smaller screen. We're designing a mobile experience that helps you understand betting risk quickly, clearly and confidently, wherever you are.",
                'status' => LabsFeatureStatus::InDevelopment,
                'notify_enabled' => true,
                'beta_enabled' => false,
                'is_published' => true,
                'sort_order' => 0,
            ]
        );
    }
}
