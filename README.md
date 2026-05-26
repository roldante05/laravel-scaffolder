# Scaffolding Factory 🚀

[![PHP Version](https://img.shields.io/badge/php-%3E%3D%208.2-8892bf.svg)](https://php.net)
[![Composer Version](https://img.shields.io/badge/composer-%3E%3D%202.0-4479a1.svg)](https://getcomposer.org)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

**Scaffolding Factory** is a powerful Command Line Interface (CLI) tool designed to scaffold professional web projects in seconds. It bridges the gap between complex full-stack frameworks and lightweight custom setups by providing high-quality boilerplate for both **Laravel** and **Vanilla PHP**, fully containerized and ready for production-grade development.

## 🛠️ What it does

This tool automates the tedious setup process of modern web applications:
- **Dual Architecture Support**: Choose between the robust Laravel ecosystem or a clean, optimized Vanilla PHP structure.
- **Docker-First Workflow**: Automatically generates `docker-compose.yml` configurations (Laravel Sail or Custom Apache/PHP 8.3) so your environment is consistent everywhere.
- **Instant Authentication**:
    - **Laravel**: Integration with Breeze, Jetstream, or Official Starter Kits.
    - **Vanilla PHP**: Optional Session-based Login Kit with PDO, including registration and secure session management.
- **Modern Styling**: Out-of-the-box support for **Tailwind CSS v4** or **Bootstrap 5**.
- **Onboarding Automation**: Generates a `scripts/install.sh` script that handles environment setup for new collaborators (dependencies, environment variables, and containers).

---

## 📋 Prerequisites

Before using Scaffolding Factory, ensure your system meets the following requirements:

- **PHP 8.2+**: Required to run the CLI tool and the generated projects.
- **Composer**: Used for global installation and dependency management.
- **Docker & Docker Compose**: Essential for the containerized development environment.
- **Git**: For version control management during project creation.

---

## 🚀 Getting Started

Follow these steps to create your first project:

### 1. Installation
Install the tool globally via Composer:
```bash
composer global require roldante05/scaffolding-factory
```
*Note: Make sure your global composer vendor bin directory is in your system's PATH.*

<<<<<<< Updated upstream
### 2. Create a New Project
=======
### 2. Install Dependencies
Install the required PHP dependencies using Composer:
```bash
composer install
```

### 3. Make the CLI Accessible
The CLI tool is available at `bin/scaffold`. You can make it accessible in three ways:

- **Option A: Run It Locally**
  Use the direct path from the cloned repository:
  ```bash
  php bin/scaffold new my-web-project
  ```

- **Option B: Add the Directory to Your PATH**
  Add the `bin` directory to your shell PATH:
  ```bash
  export PATH="$PATH:$(pwd)/bin"
  # Add above line to your shell profile (e.g., ~/.bashrc, ~/.zshrc) for permanent access
  ```

- **Option C: Global Installation via Composer (Recommended for Development)**
  To run the `scaffold` command from any directory on your machine while keeping live updates of your local code (similar to `npm link`), configure a local `path` repository in your global Composer config:

  1. Open your global `composer.json` (usually at `~/.config/composer/composer.json` or `~/.composer/composer.json`) and register your local clone:
     ```json
     {
         "repositories": {
             "local-scaffolder": {
                 "type": "path",
                 "url": "/absolute/path/to/your/laravel-scaffolder",
                 "options": {
                     "symlink": true
                 }
             }
         },
         "require": {
             "roldante05/scaffolding-factory": "dev-main"
         }
     }
     ```
  2. Run the update command globally:
     ```bash
     composer global update roldante05/scaffolding-factory
     ```
     Composer will automatically symlink your local folder, so any change you save in your IDE is reflected instantly when running `scaffold` globally!

### 4. Create a New Project
>>>>>>> Stashed changes
Run the `new` command and provide a name for your project:
```bash
scaffold new my-web-project
```

### 3. Follow the Interactive Wizard
The CLI uses a premium TUI (Terminal User Interface) with **Laravel Prompts**. Use the arrow keys to select your preferences:
- **Project Type**: Laravel or PHP Vanilla.
- **Starter Kit**: Choose your authentication and stack preferences.
- **Database**: Select from SQLite, MySQL, MariaDB, or PostgreSQL.
- **Design**: Pick your favorite CSS framework.

### 4. Initialize and Run
Once the scaffolding is complete, navigate to your project folder and run the installation script.

> [!IMPORTANT]
> The `scripts/install.sh` script is designed to automate the setup for **anyone who clones the repository** (e.g., from GitHub). It installs Composer dependencies via Docker, creates the `.env` file, and starts the containers.

```bash
cd my-web-project
bash scripts/install.sh
```
This script will build your Docker environment, install dependencies, and provide you with a local URL (usually `http://localhost`) where your app is running.

---

## 📖 Available Flows

### Laravel Ecosystem
- **Official Kits**: Breeze (Blade, Livewire, Inertia), Jetstream (Livewire, Inertia).
- **Database**: Full support for standard SQL drivers.
- **Sail Integration**: Pre-configured for easy container management.

> [!NOTE]
> **SQL Server** is listed in the database options but has limitations with Sail. SQL Server requires the `pdo_sqlsrv` PHP extension, which is not included in the default Sail Docker containers. If you select SQL Server, Sail will not be installed and you will need to configure your own database connection. For local development with SQL Server, consider using [Laravel Herd Pro](https://herd.laravel.com) or a local SQL Server instance.

### Using a Database Server with Sail

When you select a Starter Kit, the installer defaults to SQLite. If you later want to switch to MySQL, MariaDB, or PostgreSQL, add the corresponding service to your `compose.yaml` and update the `.env`.

#### MySQL

Add this service under `services:` in `compose.yaml`:

```yaml
mysql:
    image: 'mysql/mysql-server:8.0'
    ports:
        - '${FORWARD_DB_PORT:-3306}:3306'
    environment:
        MYSQL_ROOT_PASSWORD: '${DB_PASSWORD}'
        MYSQL_ROOT_HOST: '%'
        MYSQL_DATABASE: '${DB_DATABASE}'
        MYSQL_USER: '${DB_USERNAME}'
        MYSQL_PASSWORD: '${DB_PASSWORD}'
        MYSQL_ALLOW_EMPTY_PASSWORD: 'yes'
    volumes:
        - 'sail-mysql:/var/lib/mysql'
    networks:
        - sail
```

Add the volume under `volumes:`:

```yaml
volumes:
    sail-mysql:
        driver: local
```

Update your `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

#### MariaDB

```yaml
mariadb:
    image: 'mariadb:10'
    ports:
        - '${FORWARD_DB_PORT:-3306}:3306'
    environment:
        MARIADB_ROOT_PASSWORD: '${DB_PASSWORD}'
        MARIADB_DATABASE: '${DB_DATABASE}'
        MARIADB_USER: '${DB_USERNAME}'
        MARIADB_PASSWORD: '${DB_PASSWORD}'
    volumes:
        - 'sail-mariadb:/var/lib/mysql'
    networks:
        - sail
```

```yaml
volumes:
    sail-mariadb:
        driver: local
```

```env
DB_CONNECTION=mariadb
DB_HOST=mariadb
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

#### PostgreSQL

```yaml
pgsql:
    image: 'postgres:16'
    ports:
        - '${FORWARD_DB_PORT:-5432}:5432'
    environment:
        POSTGRES_USER: '${DB_USERNAME}'
        POSTGRES_PASSWORD: '${DB_PASSWORD}'
        POSTGRES_DB: '${DB_DATABASE}'
    volumes:
        - 'sail-pgsql:/var/lib/postgresql/data'
    networks:
        - sail
```

```yaml
volumes:
    sail-pgsql:
        driver: local
```

```env
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

> [!NOTE]
> After adding the service, run `docker compose up -d` to start it, then `sail artisan migrate` to create the tables.

### Vanilla PHP
- **MVC Ready**: Structured directories for a clean separation of concerns.
- **Clean URLs**: Automated `.htaccess` configuration for extension-less routing (e.g., `/dashboard` instead of `dashboard.php`).
- **PDO Wrapper**: Secure database interaction prepared for MySQL or SQLite.

---

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

Developed with ❤️ by [Dante Roldan](https://github.com/roldante05)