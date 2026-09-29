# Entorno de Desarrollo con Docker

Levanta app + base de datos idénticas a XAMPP (PHP 8.2 + Apache, MariaDB 10.4).

```
docker compose up -d --build            # http://localhost:8080
docker compose --profile tools up -d    # + phpMyAdmin en http://localhost:8081
docker compose down                     # apaga, conserva datos (volumen db_data)
docker compose down -v                  # apaga y borra la BD (se reimporta al volver a levantar)
```

- **Archivos**: `Dockerfile`, `docker-compose.yml`, `.dockerignore`.
- **BD**: al crearse el volumen, MariaDB importa `tarjeta_virtual.sql` (dump oficial, en la raíz). Para regenerar los datos: `down -v` y `up`.
- **Código en vivo**: el compose monta la carpeta del proyecto en `/var/www/html`; los cambios se ven al recargar.
- **Conexión**: `conexion.php` usa `DB_HOST/DB_NAME/DB_USER/DB_PASS` si `DB_HOST` está definida (Docker, CI, hosting con variables).
  `APP_ENV=development` habilita el detalle de errores de conexión. Sin `DB_HOST` conserva el comportamiento anterior (XAMPP / InfinityFree).
- **Credenciales por defecto (solo local)**: usuario `momentia`, clave `momentia_dev`, root `root_dev`; se sobrescriben con un `.env` junto al compose.
- El dump contiene datos de la base real (incluye la tabla `clientes`): tratarlo como sensible.
- La carpeta `tarjeta_virtual/` (archivos crudos `.frm/.ibd` de XAMPP) no se usa: solo sirve como respaldo.
