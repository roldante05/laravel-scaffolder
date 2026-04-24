# Scaffolding Factory 🚀

A powerful CLI tool designed to scaffold modern web projects in seconds. Choose between **Laravel 13** or a custom **PHP Vanilla** setup, both fully dockerized and ready for development.

## Features

- 🏗️ **Dual Scaffolding**: Support for Laravel and PHP Vanilla.
- 🐳 **Docker Integration**: Automated setup with Laravel Sail (for Laravel) or a custom Apache/PHP 8.3 container (for Vanilla).
- 🔐 **Authentication Kits**:
    - **Laravel**: Breeze, Jetstream, or official Starter Kits (Livewire, React, Vue).
    - **Vanilla**: Optional Login Kit with PDO, including login, registration, and session management.
- 🎨 **Modern Frontend**: Choice between **Tailwind CSS v4** or **Bootstrap 5**.
- 🛠️ **Clean URLs**: Automatic `.htaccess` configuration for extension-less routing (e.g., `/home` instead of `home.php`).
- ⚡ **Developer Friendly**: Generates a dynamic `install.sh` script so your team can set up the project with a single command.

## Installation

```bash
composer global require roldante05/scaffolding-factory
```

*Or clone the repository and link the binary.*

## Usage

Create a new project by running:

```bash
scaffold new my-awesome-project
```

The interactive wizard will guide you through the configuration options.

### 1. Laravel Flow
- **Auth Kit**: Breeze, Jetstream, Starter Kits (Livewire, React, Vue), or None.
- **Stacks**: Depends on the kit (Blade, Livewire, Inertia React/Vue, shadcn).
- **Database**: SQLite, MySQL, MariaDB, PostgreSQL, SQL Server.

### 2. PHP Vanilla Flow
- **Database**: MySQL, SQLite, or None.
- **Login Kit**: Optional (only if DB is selected). Includes user table and auth logic.
- **CSS Framework**: Tailwind CSS or Bootstrap 5.

## Installation Script (`install.sh`)

Every generated project includes an `install.sh` file. Your team members only need to run:

```bash
./install.sh
```

This script will:
1. Verify Docker installation.
2. Build and start the containers.
3. Install dependencies via Composer/NPM.
4. Set up the database and environment variables.
5. Provide the local access URL.

## Requirements

- PHP 8.3 or higher.
- Composer.
- Docker & Docker Compose.

## License

MIT License. Created by [Dante Roldan](https://github.com/roldante05).