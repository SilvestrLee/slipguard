# SlipGuard Product Blueprint

**Correction (`PO-U23-002`, 2026-08-07):** several lists below no longer describe current state and are corrected in place — see `FEATURE_MATRIX.md`'s own correction note for the fuller shipped-feature record. "Weakest leg"/"weakest-leg detection" is corrected to "main contributing factor" in the Primary Journey and MVP Included lists below, since both describe the base Analyze → Report path, which shows the Main Contributing Factor (a risk-factor concept, `resources/views/livewire/betting-slips/report.blade.php`), not a per-leg ranking. A genuinely separate weakest-leg ranking engine (`RankLegsByStructuralWeakness`, the Marginal Structural Contribution model, `U-06.2A`/`E-06D.1`) does exist and is real, wired into the Planner and Capability B — not absent, just outside this document's base-analysis scope. Customer and Public Navigation are corrected to match the real, live navigation components. "Safe Accumulator Builder" (Deferred) shipped as Capability B, "Build an Accumulator," MVP-gated behind `MARKET_WIDE_PLANNER_ENABLED`. "Screenshot upload" (Deferred) shipped in MVP (manual transcription, no OCR — OCR itself correctly remains deferred). "Live odds" (Deferred) is now MVP-gated, not excluded, since Capability B draws on it.

## MVP Objective
Deliver the smallest trustworthy experience that proves users value explainable betting-slip risk analysis.

## Primary Journey
Homepage → Register → Login → Dashboard → Analyze Slip → Enter selections (manually, pasted text, PDF, or screenshot) → Validate → Analyze → View report → Review main contributing factor → Save → Journal → History

## Customer Navigation
- Dashboard
- Analyse Slip
- Build an Accumulator (MVP, gated behind `MARKET_WIDE_PLANNER_ENABLED`)
- Analysis History
- Journal
- Planning History
- SlipGuard Labs
- Help
- Profile
- Settings

## Public Navigation
- Home, Analyse, Planner, Reports, Pricing (primary)
- About, Contact, FAQ, SlipGuard Labs, Release Notes, Privacy, Terms (footer)
- Sign In
- Get Started

## Operations Navigation
- Overview
- Users
- Analyses
- Sports Catalogue
- Bookmakers
- Risk Rules
- Feature Flags
- Incidents
- System Health

## MVP Included
Authentication, customer dashboard, manual multi-leg slip creation (plus paste-text, PDF, and screenshot intake), validation, deterministic risk scoring, risk band, main contributing factor detection, accumulator-risk explanation, plain-language report, saved analyses, history, journal, planner, Build an Accumulator (gated), profile, minimal Filament operations, feature flags, and Pest coverage.

## Deferred
OCR (real image-to-text extraction), per-bookmaker automated parsing, behavioural intelligence, notifications, multilingual UI, premium billing, referrals, and community.

## Prohibited
Winner predictions, guaranteed or safe-bet claims, fabricated confidence, AI-generated calculations, and manipulative gambling engagement mechanics.

## MVP Completion
A new user completes the primary journey without staff help, and every conclusion is deterministic, explainable, tested, and traceable.
