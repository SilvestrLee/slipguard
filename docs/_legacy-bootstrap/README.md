# Legacy Bootstrap Documentation

## Why These Files Exist

During the initial repository bootstrap, two independent documentation write operations occurred against the same working tree: the intentional SGOS v1.0 build-out and an earlier, concurrent session still executing against a pre-SGOS milestone. That earlier session produced a full parallel documentation tree — `01-foundation/`, `02-product/`, `03-architecture/`, `04-intelligence/`, `06-delivery/`, and `07-decisions/` — covering the same ground as the SGOS structure under a different numbering scheme.

These files were never committed to git. They are preserved here rather than deleted so no historical drafting work is silently lost.

## Why They Are No Longer Authoritative

SGOS v1.0 (`docs/00-governance/` through `docs/09-compliance/`, `docs/adr/`) is the structure actually referenced by `CLAUDE.md`'s Required Reading list and is the only structure the team has been building against. Where the two trees overlap, this legacy tree is a superseded draft, not a second source of truth. See `docs/00-governance/DECISION_LOG.md` and `docs/adr/` for the decisions that are actually in force.

## Supersession

SGOS v1.0 supersedes every document in this directory. Do not read these files for current product, architecture, or risk-engine direction — read the corresponding SGOS document instead.

## Removal

These files may be safely deleted once their content has been verified to add nothing that SGOS lacks. They are excluded from Required Reading and should not be cited in implementation work.
