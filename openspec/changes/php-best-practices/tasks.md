# Tasks: PHP Best Practices Alignment

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | 300-500 |
| 400-line budget risk | Medium |
| Chained PRs recommended | Yes |
| Suggested split | PR 1 (Types & Modernization) $\to$ PR 2 (Error Handling) $\to$ PR 3 (Decoupling & SRP) |
| Delivery strategy | ask-on-risk |
| Chain strategy | pending |

Decision needed before apply: Yes
Chained PRs recommended: Yes
Chain strategy: pending
400-line budget risk: Medium

### Suggested Work Units

| Unit | Goal | Likely PR | Notes |
|------|------|-----------|-------|
| 1 | Type Safety & PHP 8 Modernization | PR 1 | `strict_types`, type hints, `#[Override]`, property promotion. |
| 2 | Error Handling Sanitization | PR 2 | Replace `@` with explicit checks and exceptions. |
| 3 | Architectural Decoupling | PR 3 | BuilderFactory, DI in `NewCommand`, SRP refactoring. |

## Phase 1: Type Safety & Modernization (Foundation)

- [ ] 1.1 Add `declare(strict_types=1);` to `src/Builders/BuilderInterface.php`, `src/Builders/LaravelBuilder.php`, `src/Builders/PhpVanillaBuilder.php`, and `src/Console/NewCommand.php`.
- [ ] 1.2 Update `src/Builders/BuilderInterface.php` with explicit parameter and return type declarations.
- [ ] 1.3 Update `src/Builders/LaravelBuilder.php` and `src/Builders/PhpVanillaBuilder.php` with missing type hints and `#[Override]` attributes.
- [ ] 1.4 Refactor constructors in `LaravelBuilder` and `PhpVanillaBuilder` to use constructor property promotion and `readonly` properties.
- [ ] 1.5 Update `src/Console/NewCommand.php` with missing type declarations for parameters and return types.

## Phase 2: Error Handling Sanitization

- [ ] 2.1 Identify and replace all `@mkdir` calls in `LaravelBuilder.php` and `PhpVanillaBuilder.php` with `is_dir()` checks and explicit exception handling.
- [ ] 2.2 Identify and replace all `@file_put_contents` calls in builders with writable checks and try-catch blocks.
- [ ] 2.3 implement a consistent error handling strategy for filesystem failures across all builders to ensure meaningful error messages.

## Phase 3: Architectural Decoupling & SRP

- [ ] 3.1 Create `src/Builders/BuilderFactory.php` to handle the logic of resolving the correct builder based on user input.
- [ ] 3.2 Refactor `src/Console/NewCommand.php` to inject `BuilderFactory` and remove direct `new LaravelBuilder()` / `new PhpVanillaBuilder()` calls.
- [ ] 3.3 Refactor `LaravelBuilder` and `PhpVanillaBuilder` to extract non-core builder logic into helper methods or services to improve SRP.
- [ ] 3.4 (Optional) Create a `ProcessFactory` wrapper for `Symfony\Component\Process\Process` and inject it into builders to remove direct instantiation.

## Phase 4: Testing & Verification

- [ ] 4.1 Run existing Pest tests after Phase 1 to verify that `strict_types` didn't introduce runtime crashes.
- [ ] 4.2 Run existing Pest tests after Phase 2 to verify filesystem operations still work without `@`.
- [ ] 4.3 Write and run tests for `BuilderFactory` to verify correct builder resolution.
- [ ] 4.4 Run full test suite to ensure no regressions in project scaffolding for both Laravel and PHP Vanilla.
