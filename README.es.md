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
- **Despliegue Automatizado**: Genera un script inteligente `install.sh` que maneja la instalación de dependencias, la configuración del entorno y el inicio de los contenedores de un solo tirón.

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

### 2. Crea un Nuevo Proyecto
Ejecuta el comando `new` y proporciona un nombre para tu proyecto:
```bash
scaffold new mi-proyecto-asombroso
```

### 3. Sigue el Asistente Interactivo
La CLI utiliza una interfaz TUI premium con **Laravel Prompts**. Usa las teclas de flecha para seleccionar tus preferencias:
- **Tipo de Proyecto**: Laravel o PHP Vanilla.
- **Kit de Inicio**: Elige tus preferencias de autenticación y stack.
- **Base de Datos**: Selecciona entre SQLite, MySQL, MariaDB o PostgreSQL.
- **Diseño**: Elige tu framework de CSS favorito.

### 4. Inicializa y Ejecuta
Una vez completado el scaffold, navega a la carpeta de tu proyecto y ejecuta el script de instalación:
```bash
cd mi-proyecto-asombroso
./install.sh
```
Este script construirá tu entorno Docker, instalará las dependencias y te proporcionará una URL local (usualmente `http://localhost`) donde tu aplicación estará funcionando.

---

## 📖 Flujos Disponibles

### Ecosistema Laravel
- **Kits Oficiales**: Breeze (Blade, Livewire, Inertia), Jetstream (Livewire, Inertia).
- **Base de Datos**: Soporte completo para los drivers SQL estándar.
- **Integración Sail**: Pre-configurado para una gestión sencilla de contenedores.

### PHP Vanilla
- **Listo para MVC**: Directorios estructurados para una separación clara de responsabilidades.
- **URLs Limpias**: Configuración automatizada de `.htaccess` para rutas sin extensiones (ej. `/dashboard` en lugar de `dashboard.php`).
- **Wrapper de PDO**: Interacción segura con la base de datos preparada para MySQL o SQLite.

---

## 📄 Licencia

Licencia MIT. Consulta el archivo [LICENSE](LICENSE) para más información.

Desarrollado con ❤️ por [Dante Roldan](https://github.com/roldante05)