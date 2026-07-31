<?php

namespace App\Domain\Labs;

/**
 * SD-002's fixed status vocabulary (docs/00-governance/DECISION_LOG.md) —
 * every Labs feature shows exactly one of these, never a custom label.
 */
enum LabsFeatureStatus: string
{
    case Research = 'research';
    case Planned = 'planned';
    case Designing = 'designing';
    case InDevelopment = 'in_development';
    case Beta = 'beta';
    case Released = 'released';

    public function label(): string
    {
        return match ($this) {
            self::Research => 'Research',
            self::Planned => 'Planned',
            self::Designing => 'Designing',
            self::InDevelopment => 'In Development',
            self::Beta => 'Beta',
            self::Released => 'Released',
        };
    }
}
