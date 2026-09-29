# Contexto del Proyecto: Momentia (SaaS de Invitaciones Digitales)

## Propósito
Este archivo contiene las reglas base del proyecto. Las documentaciones detalladas están en `.docs/`. **Instrucción para la IA:** Lee los archivos de `.docs/` únicamente cuando el usuario solicite trabajar en ese dominio específico. No satures el contexto cargando toda la documentación si no es necesario.

## ⚠️ MANTENIMIENTO CONTINUO DE DOCUMENTACIÓN (Instrucción Obligatoria para la IA)
Tú (el agente IA) eres responsable de mantener esta documentación viva y sincronizada con el código. Si durante nuestra interacción:
1. Creas, modificas o eliminas una tabla o columna en la base de datos...
2. Creas un nuevo módulo interactivo...
3. Cambias la arquitectura o el flujo de la aplicación...
**DEBES actualizar de forma proactiva y autónoma los archivos correspondientes en el directorio `.docs/`** antes de dar por terminada la tarea. La documentación nunca debe quedar obsoleta respecto al código fuente.

## Stack Tecnológico
- **Backend:** PHP 8+ (Procedural, scripts de acción y renderizado).
- **Base de Datos:** MySQL (Acceso vía PDO con sentencias preparadas).
- **Frontend:** HTML5, CSS3 (Vanilla, uso intensivo de variables CSS, Flex/Grid), Vanilla JS.
- **Diseño:** Orientado a la elegancia, accesibilidad y ligereza (sin frameworks pesados).

## Reglas Generales de Arquitectura
- **Patrón Estructural:** Ausencia de frameworks MVC estrictos. Separación funcional mediante scripts de acción (`procesar_*.php`, `guardar_*.php`) y vistas orientadas al frontend (`dashboard.php`, `visualizar_tarjeta.php`).
- **Seguridad y Rendimiento:** Filtrado obligatorio de entradas (`$_GET`, `$_POST`). Todo query SQL debe ejecutarse con PDO paramétrico (`?`).
- **Escalabilidad:** El código debe ser modular. Reutilizar clases CSS globales (`btn-accion`, `modulo-titulo`) y evitar dependencias innecesarias de JS de terceros.

## Índice de Conocimiento Profundo (.docs/)
- 📄 **[Arquitectura y Flujos](.docs/arquitectura.md)**: Si necesitas modificar el flujo de autenticación, el wizard de creación o el motor de renderizado.
- 📄 **[Base de Datos](.docs/base_datos.md)**: Si necesitas modificar el esquema relacional o alterar consultas SQL.
- 📄 **[Sistema de Módulos](.docs/modulos.md)**: Si necesitas crear, editar o eliminar fragmentos interactivos de la tarjeta (RSVP, Muro, Regalos, etc.).
