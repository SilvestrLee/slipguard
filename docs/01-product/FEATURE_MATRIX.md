# Feature Matrix

**Correction (`PO-U23-001A`, 2026-08-07):** this table predates Programme U-17 and was not updated as that programme shipped — several rows below no longer describe current state and are corrected in place rather than silently left wrong. "Safe Accumulator Builder" was this table's original name for what shipped as Capability B, "Build an Accumulator"; "Bookmaker parsers" never shipped in the row's own original sense (no per-bookmaker format parsing exists) but a deterministic, taxonomy-based text/PDF parser did (`ParseSlipText`), which is the row's closest real referent. Other MVP-scope drift beyond these rows (e.g. Planner/Programme U-07 has no row at all) is not corrected here — out of this reconciliation's narrow scope (Capability B and its new conversational entry layer only), flagged rather than silently left for a future pass to discover again.

| Feature | Stage | User Value | Complexity | Priority |
|---|---|---:|---:|---|
| Authentication | MVP | High | Low | High |
| Dashboard | MVP | High | Low | High |
| Manual slip entry | MVP | Very High | Medium | High |
| Deterministic analysis | MVP | Very High | High | High |
| Weakest-leg detection | MVP | Very High | Medium | High |
| Risk report | MVP | Very High | Medium | High |
| History | MVP | High | Medium | High |
| Journal | MVP | Medium | Medium | Medium |
| Filament operations | MVP | Operational | Medium | High |
| Deterministic text/PDF slip parsing | MVP | High | Medium | High |
| Screenshot upload (no OCR — manual transcription) | MVP | Medium | Low | Medium |
| OCR (real image-to-text extraction) | Post-MVP | High | High | Medium |
| Build an Accumulator (Capability B — formerly "Safe Accumulator Builder") | MVP, gated behind `MARKET_WIDE_PLANNER_ENABLED` pending public-launch authorization | High | High | High |
| Conversational accumulator entry (natural-language layer over Capability B; no live AI provider — deterministic interpreter only) | MVP, same gate as Capability B | Medium | Medium | Medium |
| Premium membership | Post-MVP | Business | Medium | Later |
| Referral system | Future | Low core value | Medium | Low |
