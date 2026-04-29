# Scaffolding Factory 🚀

Una poderosa herramienta de línea de comandos diseñada para scaffoldear proyectos web modernos en segundos. Elige entre **Laravel 13** o un setup personalizado de **PHP Vanilla**, ambos totalmente dockerizados y listos para desarrollo.

## Características

- 🏗️ **Scaffoldado Dual**: Soporte para Laravel y PHP Vanilla.
- 🐳 **Integración con Docker**: Configuración automática con Laravel Sail (para Laravel) o un contenedor personalizado de Apache/PHP 8.3 (para Vanilla).
- 🔐 **Kits de Autenticación**:
    - **Laravel**: Breeze, Jetstream, o Kits de Inicio Oficiales (Livewire, React, Vue).
    - **Vanilla**: Kit de Inicio de Sesión opcional con PDO, incluyendo login, registro y gestión de sesiones.
- 🎨 **Frontend Moderno**: Elección entre **Tailwind CSS v4** o **Bootstrap 5**.
- 🛠️ **URLs Limpias**: Configuración automática de `.htaccess` para rutas sin extensión (ej: `/home` en lugar de `home.php`).
- ⚡ **Amigable para Desarrolladores**: Genera un script dinámico `install.sh` para que tu equipo pueda configurar el proyecto con un solo comando.

## Instalación

```bash
composer global require roldante05/scaffolding-factory
```

*O clona el repositorio y enlaza el binario.*

## Uso

Crea un nuevo proyecto ejecutando:

```bash
scaffold new mi-proyecto-asombroso
```

El asistente interactivo te guiará a través de las opciones de configuración.

### 1. Flujo de Laravel
- **Kit de Autenticación**: Breeze, Jetstream, Kits de Inicio Oficiales (Livewire, React, Vue), o Ninguno.
- **Stacks**: Depende del kit (Blade, Livewire, Inertia React/Vue, shadcn).
- **Base de Datos**: SQLite, MySQL, MariaDB, PostgreSQL, SQL Server.

### 2. Flujo de PHP Vanilla
- **Base de Datos**: MySQL, SQLite, o Ninguna.
- **Kit de Inicio de Sesión**: Opcional (solo si se selecciona DB). Incluye tabla de usuarios y lógica de autenticación.
- **Framework CSS**: Tailwind CSS o Bootstrap 5.

## Script de Instalación (`install.sh`)

Cada proyecto generado incluye un archivo `install.sh`. Los miembros de tu equipo solo necesitan ejecutar:

```bash
./install.sh
```

Este script hará:
1. Verificar la instalación de Docker.
2. Construir y iniciar los contenedores.
3. Instalar dependencias mediante Composer/NPM.
4. Configurar la base de datos y variables de entorno.
5. Proveer la URL de acceso local.

## Levantar proyectos en local

### Para proyectos Laravel:
1. Después de ejecutar `./install.sh`, el script proporcionará la URL de acceso (usualmente http://localhost)
2. Los contenedores de Laravel Sail se iniciarán automáticamente
3. Para parar los contenedores: `./vendor/bin/sail down`
4. Para volver a iniciar los contenedores: `./vendor/bin/sail up -d`
5. Para ejecutar comandos Artisan: `./vendor/bin/sail artisan [comando]`
6. Para acceder a la base de datos: Usa las credenciales proporcionadas en el archivo .env

### Para proyectos PHP Vanilla:
1. Después de ejecutar `./install.sh`, el script proporcionará la URL de acceso (usualmente http://localhost)
2. El contenedor personalizado de Apache/PHP 8.3 se iniciará automáticamente
3. Para parar el contenedor: `docker compose down`
4. Para volver a iniciar el contenedor: `docker compose up -d`
5. Los archivos del proyecto se sirven directamente desde el contenedor
6. Para ver logs: `docker compose logs -f`
7. Para acceder a la base de datos (si se configuró): Usa las credenciales en el archivo .env

## Requisitos

- PHP 8.3 o superior.
- Composer.
- Docker & Docker Compose.

## Licencia

MIT Licencia. Creado por [Dante Roldan](https://github.com/roldante05).