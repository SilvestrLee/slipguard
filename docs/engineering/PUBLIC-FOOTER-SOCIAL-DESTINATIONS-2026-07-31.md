# Public Footer Social Destinations

Date: 2026-07-31  
Authority: direct founder instruction  
Status: implemented and browser-verified

## Implementation

- Added X, YouTube and LinkedIn immediately below:
  “An independent intelligence layer between the bettor and the bookmaker.”
- Added a reusable `x-social-icon` component containing the three vector marks.
- Added a small footer profile register with nullable URLs.
- While a URL is null, the icon:
  - remains visible;
  - is not interactive;
  - exposes an accessible “account link coming soon” label;
  - does not render a placeholder `href`.
- Once an official URL is verified, supplying it promotes the corresponding item to an external link with `noopener noreferrer`.

## Verification

- 53 focused tests passed with 438 assertions.
- Production frontend build passed.
- `git diff --check` passed.
- Browser verification:
  - desktop light theme;
  - mobile dark theme;
  - three visible SVG icons;
  - zero social links while URLs remain unavailable;
  - correct accessible labels;
  - no browser page errors.

## Scope

No social account URL was assumed or fabricated. No navigation, footer information architecture, theme token or dependency changed.
