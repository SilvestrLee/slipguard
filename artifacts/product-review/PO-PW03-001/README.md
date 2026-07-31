# PW-03 Product Review Evidence

## Scope

Public website atmosphere behaviour only. The existing four forms, their
placement, proportions, palette, opacity, and blur remain unchanged.

## Calibration

At 996px of document scroll, the four layers resolve to:

- Layer 1: -28px
- Layer 2: +40px
- Layer 3: -20px
- Layer 4: +32px

Displacement is capped at 1,400px of source scroll. Opposing directions and
restrained depth differences create environmental separation without allowing
the forms to leave their established composition.

## Evidence

- `desktop-initial-after.png`
- `desktop-scrolled-after.png`
- `tablet-initial-after.png`
- `tablet-scrolled-after.png`
- `mobile-initial-after.png`
- `mobile-scrolled-after.png`
- `desktop-reduced-motion.png`
- `motion-measurements.json`

The measurement file records the initial, scrolled, and stopped computed
transforms for every target viewport. `stoppedImmediately` is true at desktop,
tablet, and mobile: no transform changes after the final scroll frame.

In reduced-motion mode every layer reports `transform: none` and no inline
shift.

## Behavioural Guarantees

- Document scroll is the only public-page input.
- One passive listener and at most one `requestAnimationFrame` update are used
  per scroll frame.
- There are no timers, pointer listeners, resize-driven movement, easing,
  inertia, or idle drift.
- The shared controller retains the authenticated workspace scroll source for
  application routes.
