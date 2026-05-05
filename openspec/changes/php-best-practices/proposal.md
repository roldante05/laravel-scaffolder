# Proposal: PHP Best Practices Alignment

## Intent

Improve code quality, type safety, and maintainability by applying modern PHP 8.x features and PSR standards. The goal is to eliminate "legacy" habits (like error suppression), enforce strict typing to prevent runtime errors, and reduce the coupling of the Builder classes to improve testability and adherence to the Single Responsibility Principle (SRP).

## Scope

### In Scope
- **Type Safety**: Addition of `declare(strict_types=1)` and missing parameter/return/property type declarations across core classes.
- **Error Handling**: Removal of error suppression operators (`@`) in favor of proper exception handling or checks.
- **Modern PHP 8.x Features**: Implementation of `#[Override]` attributes, constructor property promotion, and `readonly` properties where applicable.
- **Decoupling**: Refactoring direct instantiations of `Process` and Builders in `NewCommand` to improve testability.
- **Core Builders**: `LaravelBuilder`, `PhpVanillaBuilder`, and `BuilderInterface`.
- **Console**: `NewCommand`.

### Out of Scope
- Performance profiling or optimization of the scaffolding logic.
- Changing the core Builder pattern architecture (we are refining it, not replacing it).
- Modifying `StubProcessor.php` (already compliant).

## Capabilities

### New Capabilities
- None

### Modified Capabilities
- `project-scaffolding`: The internal implementation of how projects are built will be more robust, type-safe, and maintainable, though the external behavior remains the same.

## Approach

The implementation will follow a systematic path to avoid introducing regressions:

1.  **Type Enforcement**: Start by adding `declare(strict_types=1)` and filling in missing type hints (Return types $\to$ Parameter types $\to$ Property types).
2.  **Modernization**: Apply PHP 8 attributes (`#[Override]`) and property promotions to clean up boilerplate.
3.  **Error Sanitization**: Replace all `@mkdir` and `@file_put_contents` calls with explicit checks (e.g., `is_dir()`) and proper `try-catch` blocks or explicit error handling.
4.  **Structural Refactoring**: 
    - Introduce a `ProcessFactory` or similar wrapper for `Symfony\Component\Process\Process` to remove direct instantiation in builders.
    - Inject Builders into `NewCommand` or use a Factory to resolve them, reducing tight coupling.
5.  **Verification**: Run existing Pest tests after each step to ensure no functional regressions.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `src/Console/NewCommand.php` | Modified | Add types, refactor builder instantiation, PSR-12 alignment. |
| `src/Builders/BuilderInterface.php` | Modified | Add missing parameter type declarations. |
| `src/Builders/LaravelBuilder.php` | Modified | Add `strict_types`, remove `@`, add `#[Override]`, apply SRP improvements. |
| `src/Builders/PhpVanillaBuilder.php` | Modified | Add `strict_types`, remove `@`, add `#[Override]`. |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Runtime errors due to `strict_types` | Medium | Incremental application and exhaustive testing with Pest. |
| Breaking filesystem logic by removing `@` | Low | Using `is_dir()`/`is_writable()` checks before operations. |
| Regression in project generation | Low | Comprehensive test suite execution after every atomic change. |

## Rollback Plan

Since changes are focused on internal types and error handling:
1.  **Git Revert**: If a critical failure occurs, revert to the last stable commit.
2.  **Incremental Rollback**: If only one builder is affected, revert specifically the changes to that builder file.

## Dependencies

- PHP 8.1+ (for `#[Override]` and `readonly` properties).

## Success Criteria

- [ ] All core classes have `declare(strict_types=1)`.
- [ ] Zero occurrences of the `@` error suppression operator in the codebase.
- [ ] All methods implementing `BuilderInterface` use the `#[Override]` attribute.
- [ ] `NewCommand` no longer directly instantiates builders via `new`.
- [ ] Full test suite passes without errors.
