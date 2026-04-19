# laravel-scaffolder

Herramienta CLI para la creación de proyectos Laravel (Scaffolder)

Esta es una herramienta de línea de comandos diseñada para agilizar la creación de proyectos Laravel personalizados. A través de un menú interactivo, te permite configurar el entorno de desarrollo (Sail), el framework de frontend (Livewire/Volt), las herramientas de construcción (Vite/Tailwind v4) y el sistema de pruebas (Pest 3) de forma automática.

## Características

- **Configuración Moderna**: Adaptado a los estándares de Laravel 12.
- **Entorno Docker**: Configuración opcional de Laravel Sail con MySQL.
- **Frontend Stack**: Inclusión automática de Livewire y Volt.
- **Tailwind CSS v4**: Integración nativa "CSS-first" optimizada para Vite (sin archivos de configuración obsoletos).
- **Testing**: Soporte para Pest 3 (inicialización automática) o PHPUnit.
- **Automatización Total**: Genera un script `install.sh` que se encarga de:
  - Levantar contenedores.
  - Generar claves y links de storage.
  - Instalar dependencias PHP y NPM.
  - Configurar los estilos y herramientas de testing.

## Instalación

### Global (Recomendado)
Para usar el comando `laravel-init` desde cualquier lugar de tu sistema:

1. Registra el repositorio local (o utiliza Packagist si ya está publicado):
   ```bash
   composer global require roldante05/laravel-scaffolder
   ```

2. Asegúrate de tener el directorio de binarios de Composer en tu PATH (ej: `~/.config/composer/vendor/bin`).

### Local
```bash
git clone https://github.com/roldante05/laravel-scaffolder.git
cd laravel-scaffolder
composer install
```

## Uso

Para crear un nuevo proyecto, navega a la carpeta donde quieras alojarlo y ejecuta:

```bash
laravel-init init nombre-de-tu-proyecto
```

La herramienta te guiará con las siguientes opciones:
1. **¿Usar Laravel Sail con MySQL?**: Configura Docker para tu proyecto.
2. **¿Incluir Livewire (con Volt)?**: Instala los componentes reactivos modernos de Laravel.
3. **¿Qué usar para assets?**: Vite (recomendado para Tailwind v4) o Webpack (legado).
4. **¿Qué framework de tests usar?**: Pest 3 (recomendado) o PHPUnit.

### Finalización del Setup
Una vez que el comando termina, entrarás en la carpeta de tu nuevo proyecto y ejecutarás el script de instalación final:

```bash
cd nombre-de-tu-proyecto
./install.sh
```

Este script detectará si usas Sail y configurará todo el entorno para que solo tengas que empezar a programar.

## Estructura del Proyecto

- `src/` - Lógica principal del comando Symfony Console.
- `src/Templates/` - Plantilla del script `install.sh.stub` que se personaliza según tus respuestas.
- `bin/` - Punto de entrada del ejecutable CLI.
- `tests/` - Suite de pruebas con Pest para asegurar la calidad de la herramienta.

## Requisitos

- PHP 8.2 o superior.
- Composer.
- Docker (si se utiliza la opción de Laravel Sail).
- Extensión `php-xml` habilitada (para el correcto funcionamiento de Pest/PHPUnit).

## Licencia

MIT