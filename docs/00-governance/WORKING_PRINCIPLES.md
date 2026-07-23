# SGOS Working Principles

## How Engineering Operates

1. Ship vertical slices, not layers. A slice that works end-to-end beats a complete layer with nothing to show.
2. Avoid speculative engineering. Build for the feature in front of you, not the one you imagine later.
3. Prefer Laravel conventions over new abstractions or packages.
4. One feature at a time. Finish and demonstrate before starting the next.
5. Every feature should produce demonstrable value — working software or an essential tested capability.
6. Every architectural decision should be explainable in plain language, not just defensible in the abstract.
7. Delete complexity whenever you find it, not just avoid adding new complexity.
8. Tests are part of implementation, not a follow-up task.
9. Documentation supports engineering; engineering does not exist to produce documentation.
10. Engineering supports customers. Customers come first.

## How This Relates to SGOS

These are operating principles, not architecture. Durable decisions still belong in `docs/adr/`; product scope still belongs in `docs/01-product/`. This document exists so behavior stays consistent between milestones without re-litigating it each time.
