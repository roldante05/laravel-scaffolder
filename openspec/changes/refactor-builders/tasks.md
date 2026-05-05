# Tasks: Refactor Builders for SRP and Type Safety

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | 600 - 800 |
| 400-line budget risk | High |
| Chained PRs recommended | Yes |
| Suggested split | PR 1 (Types) → PR 2 (Vanilla) → PR 3 (Laravel) → PR 4 (Wiring) |
| Delivery strategy | ask-on-risk |
| Chain strategy | pending |

Decision needed before apply: No
Chained PRs recommended: Yes
Chain strategy: feature-branch-chain
400-line budget risk: High

### Suggested Work Units

| Unit | Goal | Likely PR | Notes |
|------|------|-----------|-------|
| 1 | Infrastructure: DTOs and Interaction Interfaces | PR 1 | Base types; strictly new files. |
| 2 | PHP Vanilla Refactor: Handler + Builder implementation | PR 2 | Complete standalone project type refactored. |
| 3 | Laravel Refactor: Handler + Builder implementation | PR 3 | Most complex part; will remove ~600 lines from LaravelBuilder. |
| 4 | Command Wiring: Update NewCommand and final cleanup | PR 4 | Orchestration and removal of legacy interfaces. |

## Phase 1: Foundation (Infrastructure)

- [x] 1.1 Create `src/DTOs/ProjectOptions.php` (abstract) and `src/DTOs/LaravelOptions.php`, `src/DTOs/PhpVanillaOptions.php` (readonly classes).
- [x] 1.2 Create `src/Interactions/InteractionHandlerInterface.php` with `handle()` method.
- [x] 1.3 Update `src/Builders/BuilderInterface.php` to accept `ProjectOptions` DTO instead of array and remove `$questionHelper`.

## Phase 2: PHP Vanilla Refactor

- [x] 2.1 Implement `src/Interactions/PhpVanillaInteractionHandler.php` (extract prompts from builder).
- [x] 2.2 Refactor `src/Builders/PhpVanillaBuilder.php` to use `PhpVanillaOptions` DTO and remove interaction logic.
- [x] 2.3 Verify PHP Vanilla creation flow with pre-filled DTO.

## Phase 3: Laravel Refactor

- [x] 3.1 Implement `src/Interactions/LaravelInteractionHandler.php` (extract prompts from builder).
- [x] 3.2 Refactor `src/Builders/LaravelBuilder.php` to use `LaravelOptions` DTO and remove interaction logic.
- [x] 3.3 Verify Laravel creation flow with pre-filled DTO.

## Phase 4: Command Integration & Cleanup

- [x] 4.1 Update `src/Console/NewCommand.php` to use `InteractionHandlers` and pass DTOs to Builders.
- [x] 4.2 Remove any legacy logic or helper parameters from `NewCommand`.
- [x] 4.3 Run `composer test` and ensure full regression coverage.
