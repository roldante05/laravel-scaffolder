# CLAUDE.md

This file provides guidance for the Scaffolding Factory project.

## Development Commands

### Installation
```bash
composer install
```

### Running the Tool
```bash
# General usage
php bin/scaffold new <project-name>
```

### Running Tests
```bash
composer test
# or
vendor/bin/pest
```

## Project Structure

- `src/` - Main source code
  - `Builders/` - Logic for different project types (Laravel, PHP Vanilla)
  - `Console/` - Console commands (NewCommand.php)
  - `Helpers/` - Utility classes (StubProcessor.php)
  - `Templates/` - Stub templates for code generation
- `bin/` - Executable scripts
  - `scaffold` - Entry point CLI command

## How It Works

Scaffolding Factory is a multi-purpose CLI tool that scaffolds:
- **Laravel Projects**: Uses the latest Laravel version, supports Breeze, Jetstream, and modern Starter Kits with full Sail (Docker) integration.
- **PHP Vanilla Projects**: Creates a custom structure with Docker (Apache/PHP 8.3), optional PDO-based Login Kit, and clean URL routing.

The tool uses an interactive prompt system to collect user preferences and then builds the project by running shell commands and processing template stubs.