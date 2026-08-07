<?php

namespace App\Domain\AccumulatorConversation;

/**
 * `PO-U23-001` Decision 4 — the conversational builder recognises exactly
 * two request classes for MVP. A third, real class (mixing an explicit
 * selection with a criteria-based "find more like it" request, e.g.
 * "Arsenal to win, then find two more") is explicitly excluded — it
 * crosses the Capability A / Capability B boundary `ADR-013` deliberately
 * keeps separate, and Product Office named it a future Architecture
 * Office commission, not something to partially implement here.
 */
enum RequestClass
{
    /** "Four Premier League games, lower risk" — routes to Capability B via PlanningBrief. */
    case CriteriaDiscovery;

    /** "Arsenal to win" — routes to the existing manual/guided Builder (Capability A) with a seeded leg. */
    case ExplicitConstruction;

    /** The message was understood but is missing information needed to proceed. */
    case NeedsClarification;

    /** The message asks for something outside today's real Capability B/taxonomy coverage. */
    case Unsupported;
}
