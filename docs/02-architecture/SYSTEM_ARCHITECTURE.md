# System Architecture

## Style
Use a modular Laravel 13 monolith with one primary relational database.

## Experience Boundaries
- Public website: Laravel Blade.
- Customer workspace: Blade and Livewire.
- Operations: Filament.
- Shared backend: domain services, deterministic calculations, authorization, persistence, queues, scheduling, and integrations.

## Initial Domains
Identity, Betting Slip, Analysis, Risk, Journal, Catalogue, and Operations.

## Constraints
Do not introduce microservices, multiple databases, event streaming, Kubernetes, vector databases, separate Python services without proven need, unnecessary repository abstractions, or premature real-time systems.

## Rule
Introduce internal structure as real features require it. Do not reorganize the entire Laravel scaffold before implementation.
