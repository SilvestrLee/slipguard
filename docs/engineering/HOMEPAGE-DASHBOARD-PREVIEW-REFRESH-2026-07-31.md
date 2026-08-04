# Homepage Dashboard Preview Refresh

Date: 2026-07-31  
Authority: direct founder instruction

## Confirmed from the repository

The homepage hero uses genuine, theme-specific captures of the authenticated
Demo Workspace. The established source format is a 1400×820 CSS viewport
captured at 2× resolution, with PNG sources and compressed WebP delivery
assets.

## Implementation

Both captures were regenerated from the current local Demo Workspace without
reseeding or changing records. They now show the approved current logo and the
current dashboard experience. The four existing image files were replaced in
place, preserving dimensions and hero behaviour.

The delivered WebP assets remain compressed to approximately 113 KB (light)
and 99 KB (dark). A revision query was added to both homepage image URLs so a
browser or intermediary cache cannot retain the old-logo captures.

## Boundaries

No dashboard, demo data, hero layout, mobile drag behaviour, theme logic or
deterministic application behaviour changed.

## Verification

- 45 focused tests / 326 assertions passed.
- Production frontend build passed.
- `git diff --check` passed.
- Browser verification confirmed the revised light and dark URLs load the
  corresponding 2800×1640 WebP asset at full opacity with no page errors.
