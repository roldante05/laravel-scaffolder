# Design: Refactor Builders for SRP and Type Safety

## Technical Approach
Implement an **Interaction-DTO-Builder** pattern. The `NewCommand` will delegate user input gathering to an `InteractionHandler`, which returns a typed `ProjectOptions` DTO. This DTO is then passed to the `Builder`, which handles ONLY the construction logic (files and processes).

## Architecture Decisions

### Decision: Use Readonly Classes for DTOs
**Choice**: PHP 8.2+ `readonly class`.
**Alternatives considered**: Plain arrays, classes with getters/setters.
**Rationale**: Ensures immutability of configuration once gathered and provides full IDE completion/type safety.

### Decision: Separate Interaction Handlers
**Choice**: Dedicated handler classes using `Laravel\Prompts`.
**Alternatives considered**: Keeping prompts in the Command, keeping them in the Builder.
**Rationale**: Keeps `NewCommand` slim and `Builders` purely logic-focused. Allows for easier testing of construction logic without console mocks.

## Data Flow
```
NewCommand ──→ InteractionHandler ──→ [DTO] ──→ Builder ──→ [FileSystem/Process]
```

## File Changes

| File | Action | Description |
|------|--------|-------------|
| `src/DTOs/ProjectOptions.php` | Create | Base abstract DTO for all projects. |
| `src/DTOs/LaravelOptions.php` | Create | DTO for Laravel specific settings. |
| `src/DTOs/PhpVanillaOptions.php` | Create | DTO for PHP Vanilla settings. |
| `src/Interactions/InteractionHandlerInterface.php` | Create | Contract for input gathering. |
| `src/Interactions/LaravelInteractionHandler.php` | Create | Concrete handler for Laravel prompts. |
| `src/Interactions/PhpVanillaInteractionHandler.php` | Create | Concrete handler for PHP Vanilla prompts. |
| `src/Builders/BuilderInterface.php` | Modify | Update to accept `ProjectOptions` DTO. |
| `src/Builders/LaravelBuilder.php` | Modify | Remove prompt logic; use `LaravelOptions` DTO. |
| `src/Builders/PhpVanillaBuilder.php` | Modify | Remove prompt logic; use `PhpVanillaOptions` DTO. |
| `src/Console/NewCommand.php` | Modify | Update orchestration flow. |

## Interfaces / Contracts

```php
namespace Roldante05\ScaffoldingFactory\Builders;
use Roldante05\ScaffoldingFactory\DTOs\ProjectOptions;

interface BuilderInterface {
    public function build(string $projectName, ProjectOptions $options, OutputInterface $output): int;
}
```

```php
namespace Roldante05\ScaffoldingFactory\Interactions;
use Roldante05\ScaffoldingFactory\DTOs\ProjectOptions;

interface InteractionHandlerInterface {
    public function handle(InputInterface $input, OutputInterface $output): ProjectOptions;
}
```

## Testing Strategy

| Layer | What to Test | Approach |
|-------|-------------|----------|
| Unit | DTO instantiation | Assert default values and type constraints. |
| Unit | Builder Logic | Mock FileSystem/Process and pass pre-filled DTOs. |
| Integration | NewCommand flow | Use `CommandTester` to verify handler-to-builder delegation. |

## Migration / Rollout
No migration required. This is a internal structural refactor.

## Open Questions
- None.
