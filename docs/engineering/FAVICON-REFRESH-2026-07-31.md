# Approved Favicon Refresh

Date: 2026-07-31  
Authority: direct founder approval  
Status: implemented and verified

## Source

The approved source is `/Users/silvestr/Downloads/slipguard-ico.ico`.

Repository inspection confirmed that the source ICO contains one embedded 236 × 236 RGBA PNG. That embedded approved artwork was used for every generated derivative.

## Implementation

- Replaced `public/favicon.ico`.
- Regenerated the 16, 32, 48, 180, 192 and 512-pixel PNG derivatives.
- Rebuilt the public ICO with browser-appropriate 16, 32 and 48-pixel entries.
- Added the ICO explicitly to the shared favicon-link partial used by public, guest and authenticated layouts.
- Kept the approved logo SVG unchanged; the favicon remains one artwork across light and dark themes.

## Compression

- `favicon.ico`: approximately 6KB.
- `favicon-512.png`: approximately 45KB.
- All smaller PNG derivatives remain below 30KB.
- These satisfy the automated limits of 10KB for the ICO and 75KB for the 512-pixel PNG.

## Scope

No dependencies, layout structure, navigation, theme tokens or application behaviour changed.

## Verification

- 51 focused feature tests passed with 424 assertions.
- Public, guest and authenticated layouts all render the shared ICO and PNG links.
- Production frontend build passed.
- `git diff --check` passed.
