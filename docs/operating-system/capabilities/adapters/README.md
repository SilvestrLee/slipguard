# SlipGuard Capability Adapters

This directory holds SlipGuard's own, project-local **Project Adapter**
records — how SlipGuard authorizes, weighs, restricts, and evidences its
use of capabilities whose canonical identity is defined centrally.

Canonical, shared capability definitions (provider, upstream source,
maturity, provenance, governance and evidence contracts) live in the
standalone Capability System repository, not here:

- Remote (durable identity): `SilvestrLee/capability-system`
- Local operator path, where present on this machine:
  `/Users/silvestr/Documents/capability-system`

An adapter record in this directory never duplicates that central
identity/provenance information beyond a `capability_id` reference back to
it. It only ever contains SlipGuard's own authorization, authority weight,
local enablement, restrictions, and evidence pointers — see the central
repository's `adapters/CONTRACT.md` and `schemas/adapter.schema.yaml` for
the full contract these records conform to.

## Files

- `<capability-slug>.adapter.yaml` — one real adapter record per capability
  SlipGuard has formally integrated with the central system. Currently:
  `ui-ux-pro-max.adapter.yaml`.
- `evidence/` — structured resolution/invocation records
  (`schemas/evidence.schema.yaml`-conformant, from the central repository)
  for tasks specifically concerning SlipGuard's integration with the
  central Capability System.

## Relationship to existing SlipGuard governance

This directory does not replace, supersede, or duplicate:

- `docs/operating-system/capabilities/CAPABILITY-REGISTRY.md` — SlipGuard's
  own authoritative capability status/evaluation/pilot record, unchanged by
  this integration;
- `docs/operating-system/capabilities/DESIGN-CAPABILITY-MATRIX.md` and
  `docs/design-intelligence/SKILL-ROUTING.md` — SlipGuard's own
  responsibility/authority-ordering and activation rules, unchanged;
- `CHANGELOG.md`'s "Specialist Design Capability Usage" sections —
  SlipGuard's existing, pre-`CAPSYS` narrative disclosure convention for
  design-capability evidence, which remains in normal use for ordinary
  product work. The structured records under `evidence/` here specifically
  concern the capability-system integration itself, not a general
  replacement for that convention.

Introduced under `CAPSYS-P2` (2026-08-09). See
`docs/operating-system/capabilities/CAPABILITY-REGISTRY.md`'s own "Central
Capability System Integration" section for the pointer into this
directory.
