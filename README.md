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

### 2. Create a New Project
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

### Vanilla PHP
- **MVC Ready**: Structured directories for a clean separation of concerns.
- **Clean URLs**: Automated `.htaccess` configuration for extension-less routing (e.g., `/dashboard` instead of `dashboard.php`).
- **PDO Wrapper**: Secure database interaction prepared for MySQL or SQLite.

---

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

Developed with ❤️ by [Dante Roldan](https://github.com/roldante05)