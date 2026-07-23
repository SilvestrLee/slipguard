# ADR-005 — Authentication Strategy

**Status:** Accepted

## Context

Authentication is the foundation of every customer feature: workspace access, ownership of slips and analyses, and Filament operations access all assume a resolved, authenticated `User`. The decision must remain stable through MVP rather than being revisited per milestone.

## Recommended Option

Laravel Breeze, Blade + Livewire stack.

## Rationale

- Minimal: scaffolds exactly the surface needed, nothing more.
- Laravel-native: a first-party starter kit, not a third-party dependency to vet and maintain.
- Fits the Blade architecture already used for the public site.
- Fits the Livewire stack already locked in for the customer workspace (ADR-004).
- Works alongside Filament without conflict — Breeze governs customer auth, Filament governs its own operations login.
- No unnecessary abstractions: published code is ordinary controllers, routes, and views, not a hidden service.
- Easy to maintain: no vendor lock-in beyond the initial scaffold.
- Easily reversible before implementation: nothing has been installed or written against this decision yet, so an alternative (e.g. Fortify, a custom flow) can be substituted at no cost.

## Scope

If approved, authentication supports, for MVP:

- Registration.
- Login.
- Logout.
- Password reset.
- Email verification (if approved).
- Profile management.

## Explicit Non-Scope

No:

- Teams.
- Organizations.
- API tokens.
- Social login.
- Multi-factor authentication.
- Enterprise identity providers.

...unless a future ADR approves them.

## Consequences (if approved)

**Benefits:** fastest path to a conventional, reviewable auth flow; views inherit the customer layout and UX rules directly; low removal cost since nothing is hidden behind a runtime service.

**Limitations:** default views need restyling to match `docs/05-ux/UX_RULES.md`; if email verification is left unapproved, unverified addresses can use the product during MVP.

**Future compatibility:** MFA, SSO, or API access can be added later by extending the scaffolded controllers without changing the `User` contract or ownership policies built against it.

## Approval

**Founder Approval:** Granted — Sprint E-02A (Customer Identity Foundation) directive, 2026-07-23, authorized building the recommended option as specified.
