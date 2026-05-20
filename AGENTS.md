# AGENTS.md - Essential Context for OpenCode Agents

## Development Commands

- **Run tests**: `composer test` (runs Pest)
- **Run static analysis**: `composer phpstan` (if added; currently not in composer.json but may be used)
- **Execute the CLI**: `php bin/scaffold` or `./bin/scaffold` (after ensuring executable)
- **Install dependencies**: `composer install`

## Project Structure

- **Source code**: `src/` directory
  - `src/Console/` - Console commands (e.g., `NewCommand.php`)
  - `src/Builders/` - Builders for Laravel and Vanilla PHP projects
  - `src/Templates/` - Stub files for generated projects
  - `src/DTOs/` - Data transfer objects for options
- **Templates**: Stub files for generated projects live in `src/Templates/`
  - Laravel templates: `src/Templates/laravel/`
  - Vanilla PHP templates: `src/Templates/php-vanilla/`

## Important Notes

- **PHP Version**: Requires PHP 8.3+ (check with `php -v`)
- **Binary Location**: The executable is at `bin/scaffold` (symlinked via Composer's bin-dir)
- **Testing Framework**: Uses Pest (see `composer.json` → `scripts.test`)
- **Generated Projects**: When testing the CLI, remember that generated projects require:
  - Running `bash scripts/install.sh` inside the new project directory
  - Docker and Docker Compose for the containerized environment

## Common Tasks

- **Creating a test project**: 
  ```bash
  php bin/scaffold new test-project
  cd test-project
  bash scripts/install.sh
  ```
- **Running tests after changes**: Always run `composer test` before committing
- **Updating dependencies**: `composer update`
