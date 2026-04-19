# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Installation
```bash
composer install
```

### Running the Tool
```bash
php bin/laravel-init init <project-name>
```

### Running Tests
```bash
composer test
# or
vendor/bin/pest
```

## Project Structure

- `src/` - Main source code
  - `Console/` - Console commands
    - `LaravelInitCommand.php` - Main CLI command for Laravel project generation
  - `Templates/` - Template files
    - `install.sh.stub` - Template for installation script generation
- `bin/` - Executable scripts
  - `laravel-init` - Entry point CLI command

## How It Works

The Laravel Scaffolder is a CLI tool that creates customized Laravel projects with interactive prompts for:
- Laravel Sail (Docker) configuration
- Livewire inclusion
- Frontend choice (Vite/Webpack)
- Testing framework (Pest/PHPUnit)

It generates a base Laravel project via Composer, then applies selected customizations including installing packages, configuring services, and generating a customized `install.sh` script for final setup.