# Scaffolding Factory 🚀

[![PHP Version](https://img.shields.io/badge/php-%3E%3D%208.2-8892bf.svg)](https://php.net)
[![Composer Version](https://img.shields.io/badge/composer-%3E%3D%202.0-4479a1.svg)](https://getcomposer.org)
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

- **PHP 8.2+**: Necesario para ejecutar la herramienta CLI y los proyectos generados.
- **Composer**: Utilizado para la instalación global y la gestión de dependencias.
- **Docker y Docker Compose**: Esenciales para el entorno de desarrollo dockerizado.
- **Git**: Para la gestión del control de versiones durante la creación del proyecto.

---

## 🚀 Pasos para Empezar

Sigue estos pasos para crear tu primer proyecto:

### 1. Instalación
Instala la herramienta globalmente a través de Composer:
```bash
composer global require roldante05/scaffolding-factory
```
*Nota: Asegúrate de que el directorio bin de composer global esté en el PATH de tu sistema.*

<<<<<<< Updated upstream
### 2. Crea un Nuevo Proyecto
=======
### 2. Instalar Dependencias
Instala las dependencias de PHP requeridas utilizando Composer:
```bash
composer install
```

### 3. Hacer Accesible la CLI
La herramienta CLI está disponible en `bin/scaffold`. Tienes tres opciones para usarla:

- **Opción A: Ejecutar Localmente**
  Usa la ruta directa desde el repositorio clonado:
  ```bash
  php bin/scaffold new mi-proyecto-web
  ```

- **Opción B: Agregar el Directorio al PATH**
  Agrega el directorio `bin` a tu PATH para un acceso más fácil:
  ```bash
  export PATH="$PATH:$(pwd)/bin"
  # Añade la línea anterior a tu perfil de shell (ej. ~/.bashrc, ~/.zshrc) para acceso permanente
  ```

- **Opción C: Instalación Global vía Composer (Recomendado para Desarrollo)**
  Para poder usar el comando `scaffold` desde cualquier carpeta de tu sistema y que los cambios que hagas localmente se reflejen al instante (como `npm link`), configura un repositorio local de tipo `path` en tu configuración global de Composer:

  1. Abre tu `composer.json` global (usualmente ubicado en `~/.config/composer/composer.json` o `~/.composer/composer.json`) y agrega el repositorio apuntando a tu clon local:
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
     Composer creará un enlace simbólico automático a tu carpeta local. ¡Cualquier cambio que guardes en tu editor se reflejará al instante cuando corras `scaffold` de forma global!

### 4. Crear un Nuevo Proyecto
>>>>>>> Stashed changes
Ejecuta el comando `new` y proporciona un nombre para tu proyecto:
```bash
scaffold new mi-proyecto-web
```

### 3. Sigue el Asistente Interactivo
La CLI utiliza una interfaz TUI premium con **Laravel Prompts**. Usa las teclas de flecha para seleccionar tus preferencias:
- **Tipo de Proyecto**: Laravel o PHP Vanilla.
- **Kit de Inicio**: Elige tus preferencias de autenticación y stack.
- **Base de Datos**: Selecciona entre SQLite, MySQL, MariaDB o PostgreSQL.
- **Diseño**: Elige tu framework de CSS favorito.

### 4. Inicializa y Ejecuta
Una vez completado el scaffold, navega a la carpeta de tu proyecto y ejecuta el script de instalación.

> [!IMPORTANT]
> El script `scripts/install.sh` está diseñado para automatizar la configuración para **cualquier persona que clone el repositorio** (ej. desde GitHub). Instala las dependencias de Composer vía Docker, crea el archivo `.env` e inicia los contenedores.

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
- **URLs Limpias**: Configuración automatizada de `.htaccess` para rutas sin extensiones (ej. `/dashboard` en lugar de `dashboard.php`).
- **Wrapper de PDO**: Interacción segura con la base de datos preparada para MySQL o SQLite.

---

## 📄 Licencia

Licencia MIT. Consulta el archivo [LICENSE](LICENSE) para más información.

Desarrollado con ❤️ por [Dante Roldan](https://github.com/roldante05)