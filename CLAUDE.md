# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

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

## Code Architecture

### Builder Pattern
The application uses the Builder pattern to create different types of projects:
- **BuilderInterface** (`src/Builders/BuilderInterface.php`): Defines the contract for all builders
- **PhpVanillaBuilder** (`src/Builders/PhpVanillaBuilder.php`): Creates PHP Vanilla projects with customizable features
- **LaravelBuilder** (`src/Builders/LaravelBuilder.php`): Creates Laravel projects with various starter kits

### Console Command
- **NewCommand** (`src/Console/NewCommand.php`): Handles the interactive CLI flow for project creation
  - Prompts user to select project type (Laravel/PHP Vanilla)
  - Delegates to appropriate builder for options gathering
  - Executes the selected builder to create the project

### Template System
- **StubProcessor** (`src/Helpers/StubProcessor.php`): Processes template files (.stub) with variable replacement and conditional blocks
- **Templates** (`src/Templates/`): Contains stub files for different project types
  - `php-vanilla/`: Templates for PHP Vanilla projects
  - Variables: `{{VARIABLE_NAME}}` replaced with actual values
  - Conditionals: `{{TAG}}...{{/TAG}}` shown if true, `{{!TAG}}...{{/!TAG}}` shown if false

### Project Structure
- `src/` - Main source code organized by functionality
- `bin/` - Executable scripts
  - `scaffold` - Entry point CLI command
- `tests/` - Unit tests using Pest

## Key Features Implemented

### PHP Vanilla Projects
- Customizable database options (MySQL, SQLite, None)
- Optional Login Kit with user authentication
- Choice of CSS framework (Tailwind CSS v4 or Bootstrap 5)
- Docker-based development environment
- Clean URL routing via .htaccess
- Automated installation script

### Laravel Projects
- Various authentication kits (Breeze, Jetstream, Official Starter Kits)
- Multiple database options
- Modern frontend stacks based on selected kit
- Laravel Sail for Docker integration