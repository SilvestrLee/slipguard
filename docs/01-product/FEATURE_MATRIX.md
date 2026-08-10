# Feature Matrix

**Correction (`PO-U23-001A`, 2026-08-07):** this table predates Programme U-17 and was not updated as that programme shipped — several rows below no longer describe current state and are corrected in place rather than silently left wrong. "Safe Accumulator Builder" was this table's original name for what shipped as Capability B, "Build an Accumulator"; "Bookmaker parsers" never shipped in the row's own original sense (no per-bookmaker format parsing exists) but a deterministic, taxonomy-based text/PDF parser did (`ParseSlipText`), which is the row's closest real referent. Other MVP-scope drift beyond these rows (e.g. Planner/Programme U-07 has no row at all) is not corrected here — out of this reconciliation's narrow scope (Capability B and its new conversational entry layer only), flagged rather than silently left for a future pass to discover again.

**Correction (`PO-MVP-FREEZE-001`, 2026-08-10):** Capability B's and the conversational entry layer's gate description was stale — both previously read "pending public-launch authorization"; that authorization was already given (`PO-MVP-004`, 2026-08-03) and both `.env`/`.env.example` correctly set `MARKET_WIDE_PLANNER_ENABLED=true`. Corrected below. Settings, Help, and Contact rows added — all three shipped (`PO-U24-002`, `PO-U24-003`, `PO-U22-001`) but had no row in this table at all. See `docs/product/MVP_SCOPE_LOCK.md` Section 12 for the full frozen MVP capability matrix.

| Feature | Stage | User Value | Complexity | Priority |
|---|---:|---:|---:|---|
| Authentication | MVP | High | Low | High |
| Dashboard | MVP | High | Low | High |
| Manual slip entry | MVP | Very High | Medium | High |
| Deterministic analysis | MVP | Very High | High | High |
| Weakest-leg detection | MVP | Very High | Medium | High |
| Risk report | MVP | Very High | Medium | High |
| History | MVP | High | Medium | High |
| Journal | MVP | Medium | Medium | Medium |
| Settings | MVP | Medium | Low | Medium |
| Help & Methodology | MVP | Medium | Low | Medium |
| Contact | MVP | Medium | Low | Medium |
| Filament operations | MVP | Operational | Medium | High |
| Deterministic text/PDF slip parsing | MVP | High | Medium | High |
| Screenshot upload (no OCR — manual transcription) | MVP | Medium | Low | Medium |
| OCR (real image-to-text extraction) | Post-MVP | High | High | Medium |
| Build an Accumulator (Capability B — formerly "Safe Accumulator Builder") | MVP, gated behind `MARKET_WIDE_PLANNER_ENABLED` — launch-enabled per `PO-MVP-004` | High | High | High |
| Conversational accumulator entry (natural-language layer over Capability B; no live AI provider — deterministic interpreter only) | MVP, same gate as Capability B, same launch-enabled state | Medium | Medium | Medium |
| Premium membership | Post-MVP | Business | Medium | Later |
| Referral system | Future | Low core value | Medium | Low |
