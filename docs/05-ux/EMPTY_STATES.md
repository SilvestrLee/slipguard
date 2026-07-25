# Empty States

| Field | Value |
|---|---|
| Version | 1.0 |
| Status | Approved |
| Applies To | SlipGuard MVP |
| Owner | Product Office |
| Last Updated | 2026-07-24 |
| Related Documents | [Design Language](DESIGN_LANGUAGE.md), [Component Principles](COMPONENT_PRINCIPLES.md), [Iconography](ICONOGRAPHY.md), [Image Guidelines](IMAGE_GUIDELINES.md), [Accessibility](ACCESSIBILITY.md) |

---

Every empty, error, or unavailable screen must reinforce trust rather than feel unfinished. An empty state is still a message from the product — it should be as considered as any screen with content on it (`DESIGN_LANGUAGE.md`'s Trust-First Principles).

## Tone (applies to every state below)

Helpful. Encouraging. Professional. Never playful, never sarcastic, never "Oops!", never emoji-driven. SlipGuard's calm, analytical personality (`DESIGN_LANGUAGE.md`'s Product Personality) doesn't relax just because there's nothing to show.

## Template

Each state below defines: Purpose, Headline, Supporting text, Recommended illustration, Primary CTA, Secondary CTA, Tone note (only where it varies from the default above), User emotion, Accessibility considerations.

---

### No Slips

| Field | Value |
|---|---|
| Purpose | First-time user has never created a slip. |
| Headline | "No slips yet." |
| Supporting text | "Add the legs from a slip you're considering, and SlipGuard will show you its structural risk before you place it." |
| Recommended illustration | A minimal ticket/document illustration (`IMAGE_GUIDELINES.md`'s "Ticket mockups"). |
| Primary CTA | "Analyze a slip" → slip builder. |
| Secondary CTA | None. |
| User emotion | Invited, not behind. |
| Accessibility | Illustration marked decorative (`aria-hidden`); headline is a real heading, not styled body text. |

### No Analyses

| Field | Value |
|---|---|
| Purpose | User has slips but none has completed analysis yet. |
| Headline | "No analyses yet." |
| Supporting text | "Mark a slip as ready to see its structural risk report here." |
| Recommended illustration | Minimal document/report icon (`ICONOGRAPHY.md`). |
| Primary CTA | "View your slips" → slip index. |
| Secondary CTA | None. |
| User emotion | Guided to the next concrete step. |
| Accessibility | Same as No Slips. |

### No Journal Entries

| Field | Value |
|---|---|
| Purpose | User hasn't recorded a journal entry yet. |
| Headline | "Your journal is empty." |
| Supporting text | "After you analyze a slip, record what you decided and what you learned — over time this helps you spot your own patterns." |
| Recommended illustration | Minimal document/pencil motif. |
| Primary CTA | "Analyze a slip" (if none exist) or "Add a journal entry" (if analyses exist but no entries do). |
| Secondary CTA | None. |
| User emotion | Reflective, not gamified — no streak or "day 1" framing (`docs/09-compliance/PRODUCT_GUARDRAILS.md`). |
| Accessibility | Same as No Slips. |

### No Archived Slips

| Field | Value |
|---|---|
| Purpose | The Archived filter/tab has nothing in it. |
| Headline | "No archived slips." |
| Supporting text | "Slips you archive will appear here." |
| Recommended illustration | None required — a plain text empty state is sufficient for a filtered sub-view. |
| Primary CTA | None — this is a filtered view of an already-populated list; no action is being blocked. |
| Secondary CTA | "View all slips" (clears the filter). |
| User emotion | Neutral — this is an expected, unremarkable state. |
| Accessibility | Text-only; announced via the same live region as other filter changes. |

### No History

| Field | Value |
|---|---|
| Purpose | The History view has nothing to show yet. |
| Headline | "Nothing here yet." |
| Supporting text | "Once you've completed a few analyses, you'll be able to look back on them here." |
| Recommended illustration | Minimal clock/timeline motif (`ICONOGRAPHY.md`). |
| Primary CTA | "Analyze a slip." |
| Secondary CTA | None. |
| User emotion | Same as No Analyses. |
| Accessibility | Same as No Slips. |

### No Notifications

| Field | Value |
|---|---|
| Purpose | Notification centre/inbox is empty. |
| Headline | "You're all caught up." |
| Supporting text | "We'll let you know here when there's something worth your attention." |
| Recommended illustration | None required — text-only. |
| Primary CTA | None. |
| Secondary CTA | None. |
| User emotion | Reassured, not "missing out" — never framed as a gap to fill. |
| Accessibility | Text-only. |

### No Saved Reports

| Field | Value |
|---|---|
| Purpose | A saved/bookmarked-reports view has nothing in it. |
| Headline | "No saved reports." |
| Supporting text | "Save a report from any analysis to find it here later." |
| Recommended illustration | Minimal document icon. |
| Primary CTA | "View your analyses." |
| Secondary CTA | None. |
| User emotion | Same as No Analyses. |
| Accessibility | Same as No Slips. |

### Unavailable Analysis

| Field | Value |
|---|---|
| Purpose | The Analysis Availability Gate (`docs/03-data-science/RISK_RULE_SET_2026_1.md` §17) returned Unavailable — Tier 1 (unsupported sport) or Tier 2 (too many unrecognized markets). |
| Headline | "We couldn't analyze this slip." |
| Supporting text | States the specific reason in plain language, e.g. "One or more legs are for a sport SlipGuard doesn't yet support" or "Too many selections on this slip couldn't be recognized." Never a generic "something went wrong" — the gate always knows why (`EXPLAINABILITY_SYSTEM.md`'s Customer Trust). |
| Recommended illustration | Minimal warning/information icon, not an error/danger icon — this is a known limitation, not a system failure. |
| Primary CTA | "Edit this slip" → builder, scrolled/highlighted to the affected leg(s) where possible. |
| Secondary CTA | "Learn what SlipGuard supports" → a supported-sports/markets reference. |
| User emotion | Informed, not blamed — the slip isn't "wrong," SlipGuard's coverage is just limited today. |
| Accessibility | Reason text is real text (not baked into an image), announced on load via a live region if reached without a full page navigation. |

### Unsupported Sport

| Field | Value |
|---|---|
| Purpose | Specific case of Unavailable Analysis — Tier 1 hard gate, a named non-football sport. |
| Headline | "SlipGuard doesn't support [sport] yet." |
| Supporting text | "Structural risk analysis is currently available for football only. We're evaluating other sports for future coverage." |
| Recommended illustration | Minimal shield or information icon — never the sport's own iconography (`ICONOGRAPHY.md`'s Icons to Avoid). |
| Primary CTA | "Edit this slip" (remove/replace the unsupported leg). |
| Secondary CTA | None. |
| User emotion | Informed, not dismissed. |
| Accessibility | Same as Unavailable Analysis. |

### No OCR Results *(reserved — OCR is out of MVP scope, `PROJECT.md`'s MVP Non-Goals)*

| Field | Value |
|---|---|
| Purpose | Reserved for a future slip-image-upload feature; not buildable today. |
| Headline | Not yet defined. |
| Supporting text | Not yet defined. |
| Recommended illustration | Not yet defined. |
| Primary CTA | Not yet defined. |
| Secondary CTA | Not yet defined. |
| User emotion | Not yet defined. |
| Accessibility | Not yet defined. |

Documented here only as a placeholder so a future OCR sprint has a location to fill in, per Just-in-Time Documentation (`CLAUDE.md`) — do not design this state speculatively.

### Future Parser Unavailable *(reserved — bookmaker parsing is out of MVP scope)*

| Field | Value |
|---|---|
| Purpose | Reserved for a future bookmaker-slip-parsing feature; not buildable today. |
| Headline | Not yet defined. |
| Supporting text | Not yet defined. |
| Recommended illustration | Not yet defined. |
| Primary CTA | Not yet defined. |
| Secondary CTA | Not yet defined. |
| User emotion | Not yet defined. |
| Accessibility | Not yet defined. |

Same status as No OCR Results — a placeholder, not a design.

### Network Failure

| Field | Value |
|---|---|
| Purpose | A request failed due to connectivity, not a SlipGuard error. |
| Headline | "Connection lost." |
| Supporting text | "Check your connection and try again — nothing you've entered has been lost." |
| Recommended illustration | Minimal, neutral icon (e.g. a simple signal/connection glyph) — no alarming imagery. |
| Primary CTA | "Try again" (retries the failed action in place). |
| Secondary CTA | None. |
| User emotion | Reassured that no work was lost — this is the single most important thing to state. |
| Accessibility | Announced via `role="alert"` if it interrupts an in-progress action (e.g. a form submission). |

### Permission Denied

| Field | Value |
|---|---|
| Purpose | User attempted to view/act on a resource they don't own (`security-authorization` conventions — policy-denied). |
| Headline | "You don't have access to this." |
| Supporting text | "This may belong to another account, or may no longer be available." |
| Recommended illustration | None required — text-only, calm, non-accusatory (never implies the user did something wrong). |
| Primary CTA | "Back to your slips" (or the equivalent safe, owned destination). |
| Secondary CTA | None. |
| User emotion | Redirected, not scolded. |
| Accessibility | Returns a real `403`/redirect, not a silently empty page — a screen reader user must be told access was denied, not left to infer it from missing content. |

### Unexpected Error

| Field | Value |
|---|---|
| Purpose | An unhandled failure (500-class) not attributable to a known gate or permission rule. |
| Headline | "Something went wrong on our end." |
| Supporting text | "This has been logged. Try again in a moment, or come back later." |
| Recommended illustration | None required — text-only, calm. |
| Primary CTA | "Try again." |
| Secondary CTA | "Back to dashboard." |
| User emotion | Reassured this is SlipGuard's fault, not theirs, and that nothing further is required of them. |
| Accessibility | Announced via `role="alert"`; error boundary never swallows focus (focus returns to a sensible, operable element). |

### Maintenance Mode

| Field | Value |
|---|---|
| Purpose | The whole application is temporarily unavailable for planned maintenance. |
| Headline | "SlipGuard is briefly unavailable." |
| Supporting text | "We're making a scheduled update. Please check back shortly." |
| Recommended illustration | Minimal shield or clock icon — reinforces calm reliability, not alarm. |
| Primary CTA | None (nothing to act on). |
| Secondary CTA | None. |
| User emotion | Unbothered — this should read as routine care, not a crisis. |
| Accessibility | Page has a real `<title>` and heading stating the status; no auto-refresh that could disorient assistive technology users — let the user reload manually. |
