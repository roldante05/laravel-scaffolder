# Proposal: Refactor Builders for SRP and Type Safety

## Intent
Address technical debt in the `Builder` layer where UI interaction, project construction, and external process management are tightly coupled. This refactor will improve testability, maintainability, and type safety across the scaffolding engine.

## Scope

### In Scope
- Define `ProjectOptionsDto` (or equivalent) for type-safe configuration.
- Extract interaction logic (Prompts) from Builders into `InteractionHandlers`.
- Redesign `BuilderInterface` to be strictly logic-focused.
- Refactor `LaravelBuilder` and `PhpVanillaBuilder` to use DTOs.
- Update `NewCommand` to orchestrate the new DTO-based flow.

### Out of Scope
- Adding new project types.
- Modifying the underlying `.stub` template logic (StubProcessor).
- Changing the CLI command signatures.

## Capabilities

### New Capabilities
- None

### Modified Capabilities
- None

## Approach
Implement an **Interaction-DTO-Builder** architecture:
1. **InteractionHandler**: Asks questions using `Laravel\Prompts` and returns a populated DTO.
2. **DTO**: A simple class (or readonly class) holding all configuration options.
3. **Builder**: Receives the DTO and executes the construction (files, processes).

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `src/Builders/BuilderInterface.php` | Modified | Redefine contract to accept DTOs and remove UI helper. |
| `src/Builders/LaravelBuilder.php` | Modified | Split into Interaction and Logic; add DTO support. |
| `src/Builders/PhpVanillaBuilder.php` | Modified | Split into Interaction and Logic; add DTO support. |
| `src/Console/NewCommand.php` | Modified | Update flow to use Handlers and pass DTOs. |
| `src/Builders/DTOs/` | New | Create DTO classes for Laravel and PHP Vanilla. |
| `src/Interactions/` | New | Create Interaction Handlers for each project type. |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Regressions in stub variables | Medium | Maintain a mapping layer between DTO properties and legacy stub tags. |
| Non-interactive mode failure | Low | Ensure Handlers/Builders can handle default DTOs for CLI flags. |

## Rollback Plan
Revert to the previous git commit. The change is strictly structural and doesn't affect external state or databases.

## Dependencies
- PHP 8.3 features (readonly, typed properties).

## Success Criteria
- [ ] Builders have NO references to `Laravel\Prompts` or `Symfony\Console\Question`.
- [ ] `LaravelBuilder` size is reduced by at least 30%.
- [ ] All existing tests pass with the new architecture.
- [ ] New project creation works identically from the user's perspective.
