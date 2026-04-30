# AGENT_TDD.md: Strict Test-Driven Development Protocol

**Objective:** This document enforces a mandatory, non-negotiable Test-Driven Development (TDD) cycle for all AI Agents operating within this repository. 

## ⚙️ PROTOCOL EXECUTION ORDER

**Critical Sequencing Rule:** Agents must follow this master order in the development workflow:

1. **[STAGE 0] Ambiguity Gate & Pre-Flight Grill (AGENT_GRILL_ME / QUESTION-first)**
   - When: Before writing tests or implementation
   - Workflow: [PHASE: QUESTION] to challenge request logic and assumptions
   - Output: Clarified scope + Bulletproof Plan

2. **[STAGE 1 - CURRENT] AGENT_TDD** (`docs/AGENT_TDD.md`) — YOU ARE HERE
   - When: After Stage 0 is approved
   - Workflow: [STATE: RED] → [STATE: GREEN] → [STATE: REFACTOR]
   - Output: Tested, working code with passing test suite
   - Exit: All tests pass + Pint formatting applied

3. **[STAGE 2] AGENT_GRILL_ME Post-Implementation Audit** (`docs/AGENT_GRILL_ME.md`)
   - When: After TDD REFACTOR phase completes
   - Workflow: [PHASE: OBSERVE] → [PHASE: QUESTION] → [PHASE: VALIDATE] → [PHASE: SUGGEST]
   - Input: Your refactored code from [STATE: REFACTOR]
   - Output: Code review report with improvement suggestions

**Do not skip to GRILL_ME until REFACTOR phase is complete and all tests pass.**

---

## 🚨 CRITICAL DIRECTIVES (ANTI-CHEAT RULES)
1. **NO SIMULTANEOUS EDITING:** You are strictly PROHIBITED from modifying test files and application implementation files in the same prompt, turn, or atomic step. 
2. **NO HALLUCINATIONS:** You must execute the test runner (e.g., Pest, PHPUnit, etc.) in the terminal and parse the actual output. Never predict or assume test results.
3. **MINIMALIST GREEN:** In the Green phase, write the absolute "dumbest", most minimal code required to pass the test. Do not over-engineer, future-proof, or vibe-code extra features at this stage.
4. **IMPORT & DRIVER VERIFICATION:** You are strictly required to verify the existence of all imported classes (especially third-party packages like Filament) by checking the `vendor/` directory or using `php artisan tinker`. Additionally, all raw SQL expressions (e.g., `DB::raw`) MUST be verified for driver compatibility (SQLite vs PostgreSQL) using `DB::getDriverName()`. "Class not found" or "SQL syntax error" leaks are considered a failure of the TDD protocol.

## 🔄 THE MANDATORY STATE MACHINE WORKFLOW

You must act as a State Machine. Explicitly announce your current phase using the brackets (e.g., `[STATE: RED]`) before taking action. You cannot skip states.

### [STATE: RED] - Proof of Failure
- **Action:** Write or modify the test file defining the exact desired behavior.
- **Execution:** Run the specific test via the terminal.
- **Exit Condition:** You MUST output the failing terminal trace (e.g., AssertionError, missing class). Do NOT write implementation code. Halt and acknowledge the failure.

### [STATE: GREEN] - The Minimum Path
- **Action:** Write the minimum application logic in the target file to satisfy ONLY the failing test from the RED state.
- **Execution:** Run the specific test again via the terminal.
- **Exit Condition:** The terminal must output a PASS. If it fails, stay in the GREEN state and fix it until it passes.

### [STATE: REFACTOR] - Structural Cleanup
- **Action:** Optimize the code for readability, performance, and architecture without altering the defined behavior. Create mocks/factories if necessary.
- **Execution:** Run the *entire* test suite to ensure no regressions.
- **Exit Condition:** All tests return PASS.

## 🔗 NEXT STEP: CODE REVIEW VIA AGENT_GRILL_ME

When [STATE: REFACTOR] completes with all tests passing:

1. Verify `php artisan test --compact` shows all GREEN ✅
2. Verify `vendor/bin/pint --dirty --format agent` shows no violations
3. **Transition to AGENT_GRILL_ME** (`docs/AGENT_GRILL_ME.md`)
   - Apply [PHASE: OBSERVE] → [PHASE: QUESTION] → [PHASE: VALIDATE] → [PHASE: SUGGEST]
   - Review the refactored code for safety, performance, edge cases, security
   - Generate code review report

**You must complete this TDD cycle before code is considered "done" for merge.**
