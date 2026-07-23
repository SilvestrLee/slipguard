# SlipGuard Product Build Blueprint

**Version:** 1.0  
**Status:** Approved for implementation  
**Owner:** Product Office

## 1. MVP Objective

Deliver the smallest trustworthy SlipGuard experience that proves users value an explainable assessment of betting-slip risk.

## 2. Primary Journey

Public homepage  
→ Registration  
→ Login  
→ Customer dashboard  
→ Create analysis  
→ Enter slip selections manually  
→ Validate selections  
→ Run deterministic analysis  
→ View risk report  
→ Review weakest leg and risk drivers  
→ Save analysis  
→ Record decision in journal  
→ Review analysis history

## 3. Customer Navigation

- Dashboard
- Analyze Slip
- History
- Journal
- Help
- Profile
- Settings

Keep the initial navigation compact. Do not add empty destinations.

## 4. Public Website Navigation

- Home
- How It Works
- Why SlipGuard
- Responsible Betting
- Help
- Sign In
- Get Started

Blog may be added when there is an actual publishing process.

## 5. Operations Navigation

- Overview
- Users
- Analyses
- Sports Catalogue
- Bookmakers
- Risk Rules
- Feature Flags
- Incidents
- System Health

Only build operations sections required by an active customer feature.

## 6. MVP Feature Scope

### Included

- Authentication.
- Onboarding introduction.
- Customer dashboard.
- Manual slip creation.
- Multiple slip legs.
- Basic sport, competition, event, market, selection, and odds fields.
- Input validation.
- Deterministic risk scoring.
- Risk band.
- Weakest-leg detection.
- Accumulator-risk explanation.
- Plain-language report.
- Saved analyses.
- Analysis history.
- Simple journal entry.
- Basic profile and preferences.
- Minimal Filament operations area.
- Feature flags for unfinished or gated capabilities.
- Pest coverage for critical flows.

### Deferred

- OCR or screenshot upload.
- Bookmaker slip parsing.
- External live odds feeds.
- Automated event matching.
- Safe Accumulator Builder.
- Personal behavioural intelligence.
- Notifications.
- Multilingual interface.
- Premium plans.
- Referral system.
- Public match-prediction pages.
- Community features.

### Prohibited

- Winner predictions.
- Guaranteed or “safe” bets.
- Fabricated confidence.
- AI-generated calculations.
- Manipulative engagement mechanics.
- Unreviewable black-box scores.

## 7. Core Screens

### Public Homepage

Must communicate:

- What SlipGuard does.
- What it does not do.
- How analysis works.
- Why explanations can be trusted.
- Responsible-use position.
- Primary call to action.

### Dashboard

Must answer:

- What can I do now?
- What did I analyse recently?
- Is there anything worth reviewing?

Initial content:

- Analyze a Slip CTA.
- Recent analyses.
- Compact educational guidance.
- Empty state for new users.

### Analyze Slip

The user can:

- Name the slip optionally.
- Select bookmaker optionally.
- Add and remove legs.
- Enter decimal odds.
- Enter event, market, and selection descriptions.
- Review validation errors.
- Submit only when minimum valid data exists.

### Risk Report

Must display:

- Overall risk score.
- Human-readable risk band.
- Weakest leg.
- Main risk drivers.
- Accumulator compounding warning.
- Per-leg contribution.
- Clear next actions.
- Methodology disclosure.
- Non-prediction disclaimer.

Avoid presenting one number without explanation.

### History

Must provide:

- Date.
- Slip name or generated label.
- Number of legs.
- Combined odds.
- Risk band.
- Status.
- Link to report.

### Journal

For MVP, the user can record:

- Intended action: placed, revised, skipped.
- Optional note.
- Optional eventual result.
- Reflection.

Do not turn the journal into accounting or bankroll management in MVP.

## 8. Feature Gating

### Public

- Educational pages.
- Product explanation.
- Registration.

### Registered Free User

- Manual analysis.
- Limited saved-history retention if gating is later required.
- Journal.
- Profile.

### Premium Candidate — Post-MVP

Potentially gate:

- Higher analysis volume.
- Longer history.
- OCR.
- Advanced explanations.
- Personal behaviour insights.
- Safe Accumulator Builder.

Do not implement payment gating until the core analysis experience proves useful.

## 9. Product Success Signals

MVP signals:

- User completes first analysis.
- User opens the weakest-leg explanation.
- User saves or revisits a report.
- User records a decision.
- User returns to analyze another slip.
- User reports that the analysis was understandable.

Do not optimize for bet volume.

## 10. MVP Acceptance Summary

MVP is product-complete when a new user can complete the full primary journey without staff assistance and every displayed risk conclusion is deterministic, explainable, tested, and traceable.