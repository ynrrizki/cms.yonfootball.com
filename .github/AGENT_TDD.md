# Enforce Agent TDD (Human & CI Guidance)

This file documents how AI agents and humans should enforce the repository TDD policy.

- Primary policy document: `docs/AGENT_TDD.md`.
- CI should run `php artisan test --compact` on pull requests to validate tests.
- Agents should include tests with any functional changes; missing tests should block the change unless explicitly waived by a human reviewer.
