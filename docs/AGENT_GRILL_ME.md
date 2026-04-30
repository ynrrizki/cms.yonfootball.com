# AGENT_GRILL_ME.md: Mandatory Code Review & Rigorous Questioning Protocol

**Objective:** This document enforces a mandatory, non-negotiable Code Review cycle for all AI Agents operating within this repository. When reviewing code, you must adopt a skeptical, critical mindset—challenging assumptions, identifying edge cases, exposing inconsistencies, and suggesting robust alternatives.

## ⚙️ PROTOCOL EXECUTION ORDER

**Critical Sequencing Rule:** Agents must follow this strict order when implementing and reviewing features:

1. **[STAGE 0] Ambiguity Gate & Pre-Flight Grill (AGENT_GRILL_ME / QUESTION-first)**
  - When: Before any test or implementation work starts
  - Workflow: [PHASE: QUESTION] on the requested logic and architecture
  - Output: Clarified requirements + Bulletproof Plan
  - Exit Condition: Ambiguities resolved and plan agreed

2. **[STAGE 1] Implementation (AGENT_TDD)** (`docs/AGENT_TDD.md`)
  - When: After Stage 0 plan is approved
  - Workflow: [STATE: RED] → [STATE: GREEN] → [STATE: REFACTOR]
  - Output: Tested, working code with passing test suite
  - Exit Condition: All tests pass, Pint formatting applied

3. **[STAGE 2] Post-Implementation Audit (AGENT_GRILL_ME)** (`docs/AGENT_GRILL_ME.md`)
  - When: After TDD REFACTOR phase completes
  - Workflow: [PHASE: OBSERVE] → [PHASE: QUESTION] → [PHASE: VALIDATE] → [PHASE: SUGGEST]
  - Input: The refactored code from AGENT_TDD
  - Output: Code review report with improvement suggestions
  - Exit Condition: All critical/important findings addressed or documented

**Rationale:**
- TDD ensures code is **functionally correct** (passes tests)
- GRILL_ME ensures code is **production-ready** (safe, performant, maintainable)
- Running TDD first prevents wasted review effort on broken code
- Running GRILL_ME second catches issues TDD alone cannot detect (architecture, edge cases, security, performance optimization)

## 🧭 MINI-GLOSSARY (ONBOARDING QUICK MAP)

- **Stage 0 (Ambiguity Gate & Pre-Flight Grill):** clarify requirement, challenge assumptions, and lock a Bulletproof Plan before touching code.
- **Stage 1 (AGENT_TDD):** implement with strict RED → GREEN → REFACTOR cycle and terminal proof.
- **Stage 2 (AGENT_GRILL_ME Audit):** audit finished code for logic risk, standards compliance, security, and performance.
- **State (TDD term):** lifecycle step during implementation (`RED`, `GREEN`, `REFACTOR`).
- **Phase (GRILL_ME term):** review step during audit (`OBSERVE`, `QUESTION`, `VALIDATE`, `SUGGEST`).
- **Bulletproof Plan:** agreed implementation plan that has passed Stage 0 interrogation.

---

## 🚨 CRITICAL DIRECTIVES (RIGOR RULES)

1. **ASSUME NOTHING:** Do not accept code at face value. Question the "why" behind every design choice, variable naming, logic branch, and dependency.
2. **NO SKIPPING EDGE CASES:** You MUST identify and articulate:
   - Null/undefined handling
   - Boundary conditions (empty arrays, zero values, max/min constraints)
   - Concurrency & race conditions
   - Error handling (what happens when things fail?)
   - Performance implications (N+1 queries, unnecessary iterations, memory bloat)
3. **CITE STANDARDS:** All criticisms must reference applicable standards (Laravel best practices, PHP 8.3 conventions, security patterns, SOLID principles, or project-specific guidelines).
4. **ALWAYS VERIFY:** Run tests, linters, or tools to prove your findings. Never assume test coverage is sufficient without proof.
5. **CONTEXT MATTERS:** Consider the code's audience, legacy constraints, team skill level, and project velocity before labeling something "wrong."

## 🔍 THE MANDATORY CODE REVIEW WORKFLOW

You must act as a State Machine. Explicitly announce your current phase using the brackets (e.g., `[PHASE: OBSERVE]`) before taking action. You cannot skip phases.

### [PHASE: OBSERVE] - Deep Code Inspection
- **Action:** Read the code thoroughly. Map all:
  - Variable types and their sources
  - Control flow (branches, loops, early returns)
  - External dependencies (functions, models, services, APIs)
  - Data transformations (input → processing → output)
  - Relationship to adjacent code (callers, callees, shared state)
- **Questions to Ask:** What does this code do? What does it assume about its inputs? What could go wrong?
- **Exit Condition:** You must produce a written summary of the code's purpose, flow, and obvious assumptions. Document any ambiguities.

### [PHASE: QUESTION] - Challenge Every Assumption
- **Action:** For each logical segment, articulate the "risky assumptions" and formulate skeptical questions:
  - **Type Safety:** Are types guaranteed at runtime? Could `null`/`undefined` appear unexpectedly?
  - **Edge Cases:** What if the input is empty, negative, huge, or malformed?
  - **Relationships:** Could N+1 queries occur here? Are relationships lazy-loaded when they shouldn't be?
  - **State:** Could concurrent requests corrupt shared state? Are locks/transactions needed?
  - **Error Handling:** What exceptions could be thrown? Are they caught and handled properly?
  - **Security:** Could this be exploited (injection, mass assignment, authorization bypass)?
  - **Naming:** Is the variable/function name honest and unambiguous?
  - **Performance:** Could this timeout or consume excessive memory?
- **Exit Condition:** You must produce a list of 3–10 specific, actionable questions. Each question must be grounded in code evidence (line numbers, variable names, method calls).

### [PHASE: VALIDATE] - Measure Against Standards
- **Action:** Compare the code against applicable standards:
  - Laravel best practices (query optimization, relationship usage, validation patterns)
  - PHP 8.3 conventions (type hints, constructor promotion, attributes)
  - SOLID principles (Single Responsibility, Open/Closed, Liskov, Interface Segregation, Dependency Inversion)
  - Security patterns (authorization, input validation, secrets management)
  - Project-specific guidelines (from AGENTS.md, CLAUDE.md, .github/AGENT_TDD.md)
- **Checks:** 
  - Run `vendor/bin/pint --test` to detect formatting violations
  - Run `php artisan test` to check test coverage and execution
  - Use static analysis tools if available (e.g., PHPStan, Psalm)
  - Verify migrations/models align with database schema
- **Exit Condition:** You must produce a checklist of standard violations (if any) with evidence and references to documentation.

### [PHASE: SUGGEST] - Recommend Improvements
- **Action:** For each finding from QUESTION and VALIDATE phases, offer 1–3 concrete alternatives:
  - **Refactoring:** Show before/after code snippets
  - **Testing:** Suggest test cases for uncovered scenarios
  - **Patterns:** Recommend Laravel/PHP patterns that fit the use case better
  - **Performance:** Propose optimizations with expected impact (e.g., "Cache this query to reduce DB calls by 80%")
  - **Security:** Demonstrate how to close vulnerabilities with specific code
- **Tone:** Be respectful. Acknowledge constraints, trade-offs, and that code isn't always "wrong"—just "could be better."
- **Exit Condition:** You must produce a prioritized list of suggestions, ranked by impact (critical security, then performance, then readability, then nice-to-have refactors).

## 📋 REVIEW OUTPUT TEMPLATE

When completing a full review, structure your report as:

```
# Code Review: [File/Feature Name]

## [PHASE: OBSERVE] Summary
[Brief description of what the code does, its flow, and obvious assumptions]

## [PHASE: QUESTION] Risky Assumptions
- Q1: [Question] → Evidence: [line numbers/code snippet]
- Q2: [Question] → Evidence: [line numbers/code snippet]
- ...

## [PHASE: VALIDATE] Standards Violations
- Violation 1: [Finding] → Reference: [standard/guideline]
- Violation 2: [Finding] → Reference: [standard/guideline]
- ...

## [PHASE: SUGGEST] Recommended Changes
### Critical
- [Specific refactoring with before/after code]

### Important
- [Pattern recommendation with example]

### Nice-to-Have
- [Readability improvement]
```

## 🚫 ANTI-PATTERNS (COMMON MISTAKES TO AVOID)

1. **Praise Without Scrutiny:** Never say "This looks good!" without evidence. Always find at least one question or suggestion.
2. **Shallow Reading:** Do not skim—read related models, migrations, tests, and controllers to understand the full context.
3. **Tool Worship:** Do not blindly trust linters or test coverage. Manually verify critical paths.
4. **Tone Deafness:** Do not shame the developer. Frame all feedback as collaborative problem-solving.
5. **Perfection Bias:** Do not demand refactors for stylistic preferences—focus on correctness, security, and performance.

## 🎯 WHEN TO APPLY GRILL_ME

This protocol applies to:
- ✅ Pull request reviews (before merge)
- ✅ New feature code (models, controllers, services)
- ✅ Migration scripts (data integrity, rollback safety)
- ✅ Test files (coverage adequacy, mocking strategy)
- ✅ API endpoints (authorization, input validation)
- ✅ Business logic refactors (performance impact, side effects)
- ✅ Complex queries (N+1 prevention, indexing strategy)

This protocol does NOT apply to:
- ❌ Code formatting (use Pint instead)
- ❌ Trivial one-line variable renames
- ❌ Documentation-only files (use Markdown best practices)
- ❌ Auto-generated code (migrations from scaffolding tools)

## 🔗 INTEGRATION WITH OTHER PROTOCOLS

**MANDATORY SEQUENCE:**
1. **STAGE 0 - Ambiguity Gate & Pre-Flight Grill:**
  - Ask and challenge architecture assumptions using [PHASE: QUESTION]
  - Resolve unclear requirements before any code/test edits
  - Freeze a Bulletproof Plan

2. **STAGE 1 - AGENT_TDD Execution:**
  - Execute full [STATE: RED] → [STATE: GREEN] → [STATE: REFACTOR] cycle
  - Confirm all tests pass: `php artisan test --compact`
  - Confirm Pint formatting: `vendor/bin/pint --dirty --format agent`
  - Example command: `php artisan test --compact tests/Unit/MyFeatureTest.php`

3. **STAGE 2 - AGENT_GRILL_ME Audit:**
  - Apply [PHASE: OBSERVE] → [PHASE: QUESTION] → [PHASE: VALIDATE] → [PHASE: SUGGEST]
  - Use the refactored code from TDD's [STATE: REFACTOR] as input
  - Output: Code review report with findings and suggestions

**Cross-Protocol Integration Points:**
- **With AGENTS.md conventions:** Check that reviewed code follows project-specific naming, directory structure, and dependency patterns.
- **With security audits:** Apply QUESTION and VALIDATE phases to any code handling authentication, authorization, or sensitive data.
- **With performance optimization:** Use SUGGEST phase to identify caching strategies, query optimization, and structural improvements.
- **With team onboarding:** Share OBSERVE phase summaries as living documentation of feature behavior.
