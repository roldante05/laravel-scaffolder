# Delta for project-scaffolding

## ADDED Requirements

### Requirement: Strict Type Enforcement
The core codebase MUST enforce strict typing to prevent runtime type errors and ensure predictable behavior.

#### Scenario: Strict Type Activation
- GIVEN a PHP file in `src/`
- WHEN the file is loaded
- THEN it MUST contain `declare(strict_types=1);` at the top of the file.

#### Scenario: Type Completeness
- GIVEN a method in a core class
- WHEN the method is defined
- THEN it MUST have explicit type declarations for all parameters and return types.

### Requirement: Explicit Error Handling
The system MUST NOT use the error suppression operator (`@`) for filesystem or system calls.

#### Scenario: Filesystem Operation Safety
- GIVEN a filesystem operation (e.g., `mkdir`, `file_put_contents`)
- WHEN the operation is performed
- THEN it MUST use explicit checks (e.g., `is_dir`, `is_writable`) or be wrapped in a `try-catch` block with proper exception handling.

### Requirement: Modern PHP 8.x Syntax
The codebase SHOULD leverage modern PHP 8.x features to reduce boilerplate and increase clarity.

#### Scenario: Method Overriding
- GIVEN a method in `LaravelBuilder` or `PhpVanillaBuilder` that implements a method from `BuilderInterface`
- WHEN the method is defined
- THEN it MUST be marked with the `#[Override]` attribute.

#### Scenario: Boilerplate Reduction
- GIVEN a class constructor
- WHEN properties are assigned from constructor arguments
- THEN the system SHOULD use constructor property promotion and `readonly` modifiers where the property is not intended to be changed after initialization.

### Requirement: Builder Decoupling
The `NewCommand` SHALL NOT be tightly coupled to concrete Builder implementations.

#### Scenario: Builder Resolution
- GIVEN the `NewCommand` needs to execute a project build
- WHEN a specific builder (Laravel or PHP Vanilla) is required
- THEN the builder MUST be resolved via a Factory or injected dependency rather than direct instantiation using the `new` keyword.

## MODIFIED Requirements

### Requirement: Project Scaffolding Core Logic
The internal implementation of project builders MUST ensure a robust and type-safe generation process.
(Previously: The builders focused on the logic of project creation without strict type enforcement or explicit error handling for all filesystem operations.)

#### Scenario: Robust Generation
- GIVEN a Builder executing the project creation process
- WHEN an unexpected filesystem error occurs
- THEN the system MUST handle the error via an exception and provide a meaningful error message instead of failing silently.

#### Scenario: Interface Compliance
- GIVEN a concrete Builder
- WHEN it implements `BuilderInterface`
- THEN it MUST adhere to the fully typed contract defined in the interface.
