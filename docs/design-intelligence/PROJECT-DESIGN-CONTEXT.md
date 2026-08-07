# SlipGuard Project Design Context

## Purpose

This document records established SlipGuard product-design direction for use
during interface planning, implementation, review and acceptance.

It does not authorize new product scope.

It does not replace Product Office decisions, architecture decisions, feature
requirements, acceptance criteria or approved UX documentation.

When a generated recommendation conflicts with this document, the conflict must
be reported rather than silently resolved in favour of the generated
recommendation.

**Added (Constitutional Review, Finding 6 — No Multi-Repository Custody
Model):** this document's path, `docs/design-intelligence/
PROJECT-DESIGN-CONTEXT.md`, is a per-repository convention, not a shared or
central location. Each Tsotsia project maintains its own
`docs/design-intelligence/` folder locally, describing only that project.
Nothing here implies a single folder shared across projects, and nothing
here should be copied into another project's repository without the
reconciliation the Project Isolation section below already requires.

## Product Identity

SlipGuard is a sports-betting risk-intelligence platform.

It helps users understand the structure and concentration of risk within betting
selections and accumulators.

SlipGuard is not:

- a bookmaker;
- a betting operator;
- a casino product;
- a guaranteed prediction service;
- a funds-handling service;
- an automated bet-placement service;
- a product that controls or moves customer money.

## Product Promise

SlipGuard should help users understand:

- structural risk;
- accumulator exposure;
- selection relationships;
- risk concentration;
- potentially fragile combinations;
- explainable alternatives;
- the reasons behind analysis;
- the implications of changing a selection.

Analysis must remain:

- deterministic;
- explainable;
- traceable;
- non-deceptive;
- clear about limitations;
- free from guaranteed-outcome claims.

## Experience Principle

The enduring SlipGuard experience principle is:

**More human-designed.**

A more human-designed interface demonstrates:

- deliberate judgment;
- contextual usefulness;
- natural hierarchy;
- restraint;
- recognisable language;
- meaningful feedback;
- clear prioritisation;
- appropriate density;
- considered edge cases;
- visible cause and effect;
- avoidance of generic AI-generated SaaS patterns.

This principle must be used during final interface review and Product Office
acceptance.

## User Communication

SlipGuard should communicate in plain, recognisable language.

The interface should explain rather than impress.

Use:

- direct labels;
- clear consequences;
- useful context;
- specific risk explanations;
- actionable next steps;
- calm and respectful wording;
- visible uncertainty where applicable.

Avoid:

- academic risk language;
- unexplained metrics;
- excessive technical terminology;
- fake precision;
- guaranteed-outcome language;
- manipulative urgency;
- bookmaker-style excitement;
- casino language;
- fear-based copy;
- vague AI-generated wording;
- decorative insight statements that do not help a decision.

## Visual Foundation

SlipGuard’s established visual foundation includes:

- Rich Indigo as the primary brand accent;
- Graphite as the primary foundation;
- subtle tonal background variation;
- restrained use of depth;
- calm and deliberate hierarchy;
- strong legibility;
- controlled visual density;
- explainable data presentation;
- purposeful motion only;
- clear separation between primary and supporting information;
- selective emphasis rather than universal emphasis.

## Interface Character

The interface should feel:

- intelligent;
- restrained;
- deliberate;
- trustworthy;
- contemporary;
- structurally clear;
- calm under information density;
- focused on risk understanding rather than excitement;
- recognisably SlipGuard;
- designed with visible human judgment.

It should not feel:

- casino-like;
- speculative;
- sensational;
- overloaded;
- decorative;
- academically intimidating;
- mechanically generated;
- visually noisy;
- like a generic AI dashboard;
- like a bookmaker interface.

## Core Dashboard Convention

Desktop application interfaces use:

- fixed left navigation;
- scrollable right content;
- clear page-level hierarchy;
- predictable action placement;
- stable navigation behaviour;
- consistent content widths;
- deliberate use of side panels or secondary columns;
- responsive transformation for smaller screens.

This convention must not be replaced without explicit UX and Product Office
approval.

## Information Hierarchy

Information hierarchy should generally follow this order:

1. The user’s current objective
2. The most important risk or decision
3. The reason behind that risk or decision
4. The available action
5. Supporting context
6. Secondary metrics
7. Historical or optional detail

Do not give equal visual weight to all information.

Do not use card grids as the default solution to information hierarchy.

## Data Presentation

Data must be:

- explainable;
- connected to the user’s decision;
- labelled clearly;
- accompanied by useful interpretation;
- visually prioritised;
- free from decorative complexity;
- explicit about source and meaning where required.

Avoid:

- decorative charts;
- charts without a decision purpose;
- metrics without explanations;
- unexplained percentages;
- fake confidence scores;
- visualisations that imply prediction certainty;
- excessive dashboard widgets;
- duplicative statistics;
- colour coding without accessible labels.

## Interaction Feedback

Every significant user action must provide clear feedback.

Relevant actions include:

- Analyse;
- Find a candidate;
- Build an accumulator;
- add or remove a selection;
- save;
- retry;
- submit;
- filter;
- change a market;
- change a match;
- resolve an error.

Feedback should communicate:

- that the action was received;
- what is happening;
- whether the user can continue;
- whether the action succeeded;
- what failed;
- what the user should do next.

## Processing Experiences

Processing states must not use fake precision.

Do not show a percentage unless the system can calculate meaningful progress.

Processing feedback may use:

- validated stages;
- current activity;
- completed stages;
- expected next stage;
- elapsed-time guidance where appropriate;
- recoverable waiting-state language;
- transparent timeout behaviour.

Avoid:

- fake progress bars;
- generic AI loading orbs;
- random analysis phrases;
- decorative scanning effects;
- invented processing stages;
- animation that hides a stalled process.

## Product-State Completeness

Significant workflows must consider:

- entry state;
- default state;
- empty state;
- loading state;
- processing state;
- success state;
- recoverable error;
- blocking error;
- timeout state;
- disabled state;
- permission state;
- offline or connection-loss behaviour where relevant;
- responsive state;
- reduced-motion state.

An interface is not complete because its ideal success path works.

## Error Treatment

Errors should:

- explain what happened;
- distinguish user-correctable problems from system failures;
- preserve user work where possible;
- provide a clear recovery action;
- avoid technical leakage;
- avoid blame;
- avoid vague “something went wrong” messaging when a more specific message is
  available.

Release-blocking or system-level defects must never be exposed to users as raw
framework, database or server output.

## Accessibility

User-facing work should account for:

- keyboard access;
- visible focus;
- semantic structure;
- screen-reader communication;
- sufficient contrast;
- non-colour-only meaning;
- touch target size;
- reduced-motion preferences;
- responsive text behaviour;
- error identification;
- status announcements;
- understandable labels;
- predictable interaction.

Accessibility is part of product quality, not an optional enhancement.

## Responsive Behaviour

Responsive design must preserve task clarity rather than merely shrink the
desktop layout.

At minimum, review significant interfaces at:

- 375px;
- 768px;
- 1024px;
- 1440px.

On smaller screens:

- navigation may transform into an app-like drawer or compact pattern;
- primary actions must remain easy to reach;
- secondary information may be progressively disclosed;
- horizontal overflow must be deliberate;
- dense tables may require alternative representations;
- fixed desktop patterns must not create trapped or inaccessible content.

## Motion Principle

Motion must explain at least one of:

- progress;
- state change;
- causality;
- spatial relationship;
- hierarchy;
- continuity.

Motion must not exist only to make the interface appear impressive.

Motion should be:

- restrained;
- interruptible where relevant;
- consistent;
- responsive to user input;
- appropriate to the product tone;
- safe for reduced-motion users.

## Motion Restrictions

Do not add:

- decorative floating motion;
- constant ambient animation;
- casino-like pulses;
- attention-seeking flashes;
- excessive parallax;
- motion on every hover;
- transitions that delay task completion;
- animations that disguise slow performance;
- motion without a documented purpose.

## Brand Assets

Preserve the approved SlipGuard shield and SG monogram.

Do not:

- redraw approved assets;
- replace them with generated alternatives;
- alter proportions without authorization;
- import another project’s logo treatment;
- add decorative containers by default;
- remove established texture or gradient treatment without approval.

Where an approved interaction requires icon-only movement, the icon and wordmark
must be treated as separate assets.

## Prohibited Visual Patterns

Avoid:

- casino aesthetics;
- neon gambling treatments;
- generic navy-first SaaS palettes;
- uncontrolled purple-to-blue gradients;
- excessive cards;
- excessive glassmorphism;
- excessive blur;
- decorative statistics;
- unexplained dashboard widgets;
- rounded-square icon tiles by default;
- generic AI illustrations;
- floating panels without structural purpose;
- gratuitous gradients;
- excessive pill-shaped controls;
- hero sections copied from unrelated SaaS products;
- interfaces that resemble bookmakers;
- decorative complexity presented as intelligence.

## Project Isolation

SlipGuard’s design context must remain isolated from:

- StackEase;
- Keryon;
- Rive Webworks;
- The Lylod’s Group;
- JustWPFix;
- future unrelated products.

Shared Tsotsia governance may be reused.

Project-specific brand, user, workflow and visual rules may not be copied across
repositories without explicit reconciliation.

## Decision Authority

**Corrected (Constitutional Review, Finding 1 — Multiple Authority
Hierarchies):** this section previously restated its own partial ranked
order, diverging from the equivalent lists in `CAPABILITY-ADOPTION-STANDARD.md`
and `SKILL-ROUTING.md`. It now defers to a single canonical ordering instead
of forking one.

This document is subordinate to the canonical authority ordering defined in
`docs/operating-system/capabilities/DESIGN-CAPABILITY-MATRIX.md`'s Conflict
Resolution section (where this document's own place in that order is named
explicitly, as "Project Design Context").

This document is authoritative over:

- general model preferences;
- unapproved generated design recommendations;
- external skill defaults;
- unrelated design trends;
- another project’s brand context.

## Review Questions

Before accepting significant SlipGuard interface work, ask:

1. Does this help the user understand risk?
2. Is the most important decision visually clear?
3. Is the explanation connected to an action?
4. Does the interface feel calmer than the betting environment it analyses?
5. Is the language plain and recognisable?
6. Has any generic AI-SaaS pattern been introduced?
7. Has unnecessary decoration been added?
8. Are all important interface states present?
9. Is motion purposeful?
10. Does the design preserve SlipGuard identity?
11. Does it feel more human-designed?
12. Has any external capability exceeded its authority?
