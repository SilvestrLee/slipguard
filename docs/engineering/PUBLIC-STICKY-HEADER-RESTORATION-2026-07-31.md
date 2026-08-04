# Public Sticky Header Restoration

Date: 2026-07-31  
Authority: direct founder instruction

## Confirmed from the repository and browser

The accepted glass surface was still present in source and rendered with a
12px backdrop blur and 70% surface opacity. Two implementation details prevented
the intended sticky experience:

1. `position: sticky` was applied to the header inside a wrapper whose height
   matched the header, constraining the sticky element so it scrolled away.
2. The static responsive `md:h-10` class overrode the condensed `h-8` class,
   leaving the desktop icon at 40px after the scroll threshold.

## Implementation

Sticky positioning now belongs to the Alpine wrapper, which is a direct member
of the document flow. The glass surface remains on the semantic header.

The logo's responsive condensed state now explicitly resolves to `!h-8`. The
important modifier is deliberate: it overrides the static `md:h-10`
pre-hydration safety fallback once Alpine confirms the header is condensed,
producing the intended 40px-to-32px transition on desktop. The product mark
remains static and receives no rotation transform.

No navigation, atmosphere, theme, content or deterministic product behaviour
changed.

## Verification

- 48 focused tests / 343 assertions passed.
- Production frontend build passed.
- `git diff --check` passed.
- Browser measurements in light and dark themes confirmed the header remains
  at `top: 0`, retains `blur(12px)`, and condenses the icon from 40px to
  32px.
- The icon's computed transform remains `none` before and after scrolling.
