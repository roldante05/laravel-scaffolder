# Skill Registry

## Compact Rules

### php-best-practices
- `type-strict-mode`: Declare strict types in every file.
- `type-return-types`: Always declare return types.
- `type-parameter-types`: Type all parameters.
- `modern-constructor-promotion`: Use constructor property promotion.
- `modern-match-expression`: Use match over switch.
- `solid-srp`: Single Responsibility: one reason to change.
- `solid-isp`: Interface Segregation: small, focused interfaces.
- `solid-dip`: Dependency Inversion: depend on abstractions.

## User Skills

| Skill | Trigger | Description |
|-------|---------|-------------|
| php-best-practices | PHP code review, audit, best practices | PHP 8.x modern patterns and SOLID. |
| chained-pr | PR > 400 lines | Manage large changes via chained PRs. |
| work-unit-commits | commit planning | Structured deliverable commits. |
| judgment-day | review, audit | Adversarial review protocol. |

## Project Conventions
- **Source**: CLAUDE.md
- **Commands**: `composer install`, `composer test`, `php bin/scaffold new <name>`.
- **Architecture**: Builder pattern for project generation.
- **Testing**: Pest is the primary testing tool.
