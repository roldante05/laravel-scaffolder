# laravel-scaffolder

Herramienta CLI para la creación de proyectos Laravel (Scaffolder)

Esta es una herramienta de línea de comandos que te ayuda a crear rápidamente proyectos Laravel personalizados mediante prompts interactivos para configurar opciones comunes.

## Características

- Configuración interactiva del proyecto con opciones para:
  - Configuración de Laravel Sail (Docker) con MySQL.
  - Inclusión de Livewire (con el starter kit Blaze).
  - Selección de herramienta de construcción de frontend (Vite o Webpack).
  - Elección de framework de pruebas (Pest o PHPUnit).
- Instalación automatizada de paquetes y servicios seleccionados.
- Genera un script `install.sh` personalizado para la configuración final del proyecto.
- Crea un proyecto Laravel listo para desarrollar con tu configuración preferida.

## Instalación

```bash
composer require roldante05/laravel-scaffolder
```

O clona el repositorio e instala las dependencias:

```bash
git clone https://github.com/roldante05/laravel-scaffolder.git
cd laravel-scaffolder
composer install
```

## Uso

Crea un nuevo proyecto Laravel con opciones personalizadas:

```bash
php bin/laravel-init init nombre-de-mi-proyecto
```

La herramienta te pedirá configurar:
1. Si deseas usar Laravel Sail con MySQL.
2. Si deseas incluir Livewire.
3. Elección de Frontend: Vite (recomendado) o Webpack (legado).
4. Framework de pruebas: Pest (recomendado) o PHPUnit.

 Después de confirmar tus elecciones, la herramienta:
1. Creará un nuevo proyecto Laravel.
2. Instalará los paquetes seleccionados (Sail, Livewire, framework de pruebas).
3. Generará un script `install.sh` personalizado.
4. Proporcionará instrucciones para completar la configuración.

## Estructura del Proyecto

- `src/` - Código fuente principal.
  - `Console/` - Comandos de consola.
    - `LaravelInitCommand.php` - Comando CLI principal para la generación de proyectos Laravel.
  - `Templates/` - Archivos de plantilla.
    - `install.sh.stub` - Plantilla para la generación del script de instalación.
- `bin/` - Scripts ejecutables.
  - `laravel-init` - Punto de entrada del comando CLI.

## Flujo de Trabajo de Ejemplo

```bash
# Crea un nuevo proyecto con Sail, Livewire, Vite y Pest
php bin/laravel-init init mi-app

# Sigue los prompts interactivos
# ✅ ¿Usar Laravel Sail con MySQL? [yes]:
# ✅ ¿Incluir Livewire? [yes]:
# ¿Qué usar para assets? [Vite (recomendado)]:
# ¿Qué framework de tests usar? [PEST (recomendado)]:

# Tras la confirmación, la herramienta crea el proyecto y genera el archivo install.sh
# Siguientes pasos:
cd mi-app
./install.sh
```

## Requisitos

- PHP 8.2 o superior.
- Composer.
- Docker (si se utiliza la opción de Laravel Sail).

## Licencia

MIT