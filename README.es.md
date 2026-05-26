# Scaffolding Factory 🚀

<p align="center">
  <img src="art/scaffolding-factory-banner.png" alt="Scaffolding Factory Banner" width="100%">
</p>

[![PHP Version](https://img.shields.io/badge/php-%3E%3D%208.2-8892bf.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

**Scaffolding Factory** es una potente herramienta de interfaz de línea de comandos (CLI) diseñada para crear proyectos web profesionales en segundos. Cierra la brecha entre frameworks complejos y configuraciones personalizadas ligeras, proporcionando una estructura base de alta calidad tanto para **Laravel** como para **PHP Vanilla**, totalmente dockerizada y lista para el desarrollo de nivel profesional.

## 🛠️ ¿Qué es lo que hace?

Esta herramienta automatiza el tedioso proceso de configuración de las aplicaciones web modernas:
- **Soporte de Arquitectura Dual**: Elige entre el robusto ecosistema de Laravel o una estructura de PHP Vanilla limpia y optimizada.
- **Flujo de Trabajo Docker-First**: Genera automáticamente configuraciones de `docker-compose.yml` (Laravel Sail o Apache/PHP 8.3 personalizado) para que tu entorno sea consistente en cualquier lugar.
- **Autenticación Instantánea**:
    - **Laravel**: Integración con Breeze, Jetstream o los Starter Kits oficiales.
    - **Vanilla PHP**: Kit de inicio de sesión opcional basado en sesiones con PDO, que incluye registro y gestión segura de sesiones.
- **Estilo Moderno**: Soporte nativo para **Tailwind CSS v4** o **Bootstrap 5**.
- **Automatización de Onboarding**: Genera un script `scripts/install.sh` que maneja la configuración del entorno para nuevos colaboradores (dependencias, variables de entorno y contenedores).

---

## 📋 Requisitos Previos

Antes de usar Scaffolding Factory, asegúrate de que tu sistema cumpla con los siguientes requisitos:

- **PHP 8.3+**: Necesario para ejecutar la herramienta CLI y los proyectos generados.
- **Composer**: Requerido para instalar las dependencias de PHP (usado durante la configuración del proyecto).
- **Docker y Docker Compose**: Esenciales para el entorno de desarrollo dockerizado.
- **Git**: Para clonar el repositorio y la gestión del control de versiones.

---

## 🚀 Pasos para Empezar

Sigue estos pasos para instalar la herramienta y crear tu primer proyecto:

### 1. Instalación

Puedes instalar Scaffolding Factory de tres maneras:

#### Opción A: Instalación Global (Recomendado para uso rápido)
Dado que esta herramienta actualmente no está disponible en Packagist, puedes registrar su repositorio de GitHub e instalarla globalmente a través de Composer:
```bash
composer global config repositories.scaffolding-factory vcs https://github.com/roldante05/scaffolding-factory
composer global require roldante05/scaffolding-factory:dev-main
```
*Nota: Asegúrate de que el directorio bin de Composer global (usualmente `~/.config/composer/vendor/bin` o `~/.composer/vendor/bin`) esté en el PATH de tu sistema.*

#### Opción B: Enlace de Repositorio Local (Recomendado para Desarrollo)
Si quieres contribuir al proyecto o probar cambios locales globalmente en tiempo real (similar a `npm link`), define un repositorio local de tipo `path` en tu configuración global de Composer:

1. Abre tu `composer.json` global (usualmente ubicado en `~/.config/composer/composer.json` o `~/.composer/composer.json`) y registra la ruta de tu clon local:
   ```json
   {
       "repositories": {
           "local-scaffolder": {
               "type": "path",
               "url": "/ruta/absoluta/a/tu/laravel-scaffolder",
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
2. Ejecuta la actualización global:
   ```bash
   composer global update roldante05/scaffolding-factory
   ```
   Composer creará un enlace simbólico automático a tu carpeta local. ¡Cualquier cambio que guardes en tu editor se reflejará al instante cuando corras el comando `scaffold` de forma global!

#### Opción C: Configuración Local Manual
Si prefieres no instalar el comando globalmente, puedes clonar el repositorio y ejecutarlo localmente:
1. Clona el repositorio:
   ```bash
   git clone https://github.com/roldante05/scaffolding-factory.git
   cd scaffolding-factory
   ```
2. Instala las dependencias locales:
   ```bash
   composer install
   ```
3. Úsalo directamente:
   ```bash
   php bin/scaffold new mi-proyecto-web
   ```

---

### 2. Crear un Nuevo Proyecto
Ejecuta el comando `new` y proporciona un nombre para tu proyecto:
```bash
scaffold new mi-proyecto-web
```
*(Si estás usando la Opción C, ejecuta `php bin/scaffold new mi-proyecto-web` en su lugar).*

### 5. Sigue el Asistente Interactivo
La CLI utiliza una interfaz TUI premium con **Laravel Prompts**. Usa las teclas de flecha para seleccionar tus preferencias:
- **Tipo de Proyecto**: Laravel o PHP Vanilla.
- **Kit de Inicio**: Elige tus preferencias de autenticación y stack.
- **Base de Datos**: Selecciona entre SQLite, MySQL, MariaDB o PostgreSQL.
- **Diseño**: Elige tu framework de CSS favorito.

### 4. Inicializa y Ejecuta
Una vez completado el scaffold, navega a la carpeta de tu proyecto y ejecuta el script de instalación:
```bash
cd mi-proyecto-web
bash scripts/install.sh
```
Este script construirá tu entorno Docker, instalará las dependencias y te proporcionará una URL local (usualmente `http://localhost`) donde tu aplicación estará funcionando.

---

## 📖 Flujos Disponibles

### Ecosistema Laravel
- **Kits Oficiales**: Breeze (Blade, Livewire, Inertia), Jetstream (Livewire, Inertia).
- **Base de Datos**: Soporte completo para los drivers SQL estándar.
- **Integración Sail**: Pre-configurado para una gestión sencilla de contenedores.

> [!NOTE]
> **SQL Server** está en las opciones de base de datos pero tiene limitaciones con Sail. SQL Server requiere la extensión PHP `pdo_sqlsrv`, que no está incluida en los contenedores Docker por defecto de Sail. Si seleccionas SQL Server, Sail no se instalará y necesitarás configurar tu propia conexión a la base de datos. Para desarrollo local con SQL Server, considera usar [Laravel Herd Pro](https://herd.laravel.com) o una instancia local de SQL Server.

### Usar un servidor de base de datos con Sail

Cuando seleccionás un Starter Kit, el instalador usa SQLite por defecto. Si más adelante querés cambiar a MySQL, MariaDB o PostgreSQL, agregá el servicio correspondiente en tu `compose.yaml` y actualizá el `.env`.

#### MySQL

Agregá este servicio bajo `services:` en `compose.yaml`:

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

Agregá el volumen bajo `volumes:`:

```yaml
volumes:
    sail-mysql:
        driver: local
```

Actualizá tu `.env`:

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
> Después de agregar el servicio, ejecutá `docker compose up -d` para iniciarlo y luego `sail artisan migrate` para crear las tablas.

### PHP Vanilla
- **Listo para MVC**: Directorios estructurados para una separación clara de responsabilidades.
### PHP Vanilla (Powered by ChePHP)
- **ORM ActiveRecord**: Query builder fluido con `find()`, `where()`, `save()`, `delete()` y `paginate()`. Inferencia automática del nombre de tabla desde la clase. Soporta MySQL y SQLite.
- **Sistema de Migraciones**: Migraciones basadas en archivos con seguimiento por batches y rollback. Escribí archivos PHP con SQL `up`/`down`, ejecutá `php che migrate` para aplicar los cambios.
- **CLI Tool (`che`)**: Comandos integrados tipo artisan — `php che migrate`, `php che rollback`, `php che serve`, `php che route:list`.
- **Rutas Limpias**: Las rutas viven en `app/routes.php` sin nada de HTML. Cada ruta apunta a un método de un controlador. Se terminó mezclar HTML con lógica de ruteo.
- **Configuración Centralizada**: Base de datos, app y autenticación en un solo archivo `config/config.php` con soporte de variables de entorno.
- **Sistema de Autenticación**: Autenticación por sesión con protección CSRF, hashing Argon2id y cookies seguras.
- **URLs Limpias**: Configuración automatizada de `.htaccess` para rutas sin extensiones (ej. `/dashboard` en lugar de `dashboard.php`).
- **Dockerizado**: Apache/PHP 8.3 con MySQL o SQLite, listo en segundos.

---

## 📄 Licencia

Licencia MIT. Consulta el archivo [LICENSE](LICENSE) para más información.

Desarrollado con ❤️ por [Dante Roldan](https://github.com/roldante05)