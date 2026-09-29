# Momentia — Invitaciones digitales

Aplicación web para crear y compartir invitaciones digitales (PHP + MySQL/MariaDB).

Esta guía te lleva **paso a paso** para tenerla funcionando en tu computadora, sin instalar PHP ni MySQL a mano. Todo corre dentro de **Docker**.

---

## 1. Qué necesitás antes de empezar (una sola vez)

| Programa | Para qué sirve | Dónde conseguirlo |
|---|---|---|
| **Docker Desktop** | Levanta la app y la base de datos sin instalar nada más | https://www.docker.com/products/docker-desktop |
| **Git** | Descarga el proyecto | https://git-scm.com/downloads |

Después de instalar **Docker Desktop**, ábrilo y esperá a que abajo a la izquierda diga **"Engine running"** (motor en marcha). Si está apagado, nada de lo que sigue funciona.

> En Windows puede pedirte reiniciar la computadora la primera vez. Es normal.

---

## 2. Descargar el proyecto (una sola vez)

Abrí una terminal (en Windows: buscá **PowerShell** en el menú Inicio) y escribí:

```bash
git clone https://github.com/Celi-hub/tarjetas-virtuales.git
cd tarjetas-virtuales
```

Verificá que en la carpeta exista el archivo **`tarjeta_virtual.sql`** (es la base de datos). Si no está, pedile a quien te pasó el proyecto que te confirme en qué rama está (`main`).

---

## 3. Levantar la aplicación y la base de datos

Todo se levanta con **un solo comando**, parado dentro de la carpeta del proyecto:

```bash
docker compose up -d --build
```

- La **primera vez** tarda varios minutos (descarga todo y crea la base de datos con los datos de ejemplo). Las siguientes veces tarda segundos.
- `-d` significa "en segundo plano": la terminal queda libre.

Para comprobar que está andando:

```bash
docker compose ps
```

Tienen que aparecer dos servicios, `web` y `db`, con estado **Up** (y `db` con **healthy**).

---

## 4. Dónde se abre

| Qué | Dirección |
|---|---|
| **La aplicación** | http://localhost:8080 |
| **Una tarjeta de ejemplo** | http://localhost:8080/visualizar_tarjeta.php?id=33 |
| **Crear una cuenta** | http://localhost:8080/registro.php |
| **Iniciar sesión** | http://localhost:8080/login.php |

Abrí esas direcciones en el navegador (Chrome, Edge, Firefox…).

Para entrar al panel (dashboard) primero creá una cuenta desde **Crear una cuenta**.

Para ver los distintos estilos, cambiá el número de `id=` en la dirección de la tarjeta por otras tarjetas existentes (por ejemplo 33, 44, 46, 50, 51).

---

## 5. La base de datos

La base de datos se crea **sola** la primera vez que se levanta, a partir del archivo `tarjeta_virtual.sql`. No tenés que hacer nada.

### Ver o editar la base de datos con una pantalla (phpMyAdmin)

```bash
docker compose --profile tools up -d
```

Se abre en http://localhost:8081

- **Servidor:** `db`
- **Usuario:** `root`
- **Contraseña:** `root_dev`

### Conectarse con otra herramienta (DBeaver, Workbench, etc.)

El puerto de la base **no** se expone hacia afuera por defecto. Si lo necesitás, usá phpMyAdmin (arriba) o entrá por consola:

```bash
docker compose exec db mysql -umomentia -pmomentia_dev tarjeta_virtual
```

Escribí `exit` para salir.

### Datos de conexión (solo para uso local)

| Dato | Valor |
|---|---|
| Base | `tarjeta_virtual` |
| Usuario | `momentia` |
| Contraseña | `momentia_dev` |
| Usuario administrador | `root` |
| Contraseña administrador | `root_dev` |

> Son claves **solo para tu computadora**. Nunca las uses en un servidor real.

---

## 6. Uso de todos los días

| Quiero… | Comando |
|---|---|
| **Encender** todo | `docker compose up -d` |
| **Apagar** todo (los datos se conservan) | `docker compose down` |
| **Ver qué está corriendo** | `docker compose ps` |
| **Ver mensajes / errores** de la app | `docker compose logs -f web` |
| **Ver mensajes / errores** de la base | `docker compose logs -f db` |
| **Reiniciar** solo la app | `docker compose restart web` |

Los cambios que hagas en los archivos del proyecto se ven **al recargar la página**: no hace falta reiniciar nada.

---

## 7. Empezar de cero con la base de datos

Si querés **borrar todo lo que cargaste** y volver a los datos originales:

```bash
docker compose down -v
docker compose up -d
```

> ⚠️ `-v` **borra la base de datos**. Todo lo que hayas creado (cuentas, tarjetas nuevas) se pierde y vuelve a los datos de ejemplo.

---

## 8. Si algo no anda

| Problema | Qué hacer |
|---|---|
| `docker: command not found` o "no se reconoce el comando" | Docker Desktop no está instalado o no está abierto. Ábrilo y esperá a "Engine running". |
| `Cannot connect to the Docker daemon` | Docker Desktop está apagado. Abrilo y probá de nuevo. |
| **"port is already allocated"** / el puerto 8080 está ocupado | Otro programa usa ese puerto (por ejemplo XAMPP). Apagalo, o cambiá el puerto: creá un archivo llamado `.env` con la línea `WEB_PORT=8090` y abrí http://localhost:8090 |
| La página muestra **"Error de conexión"** | La base todavía está arrancando. Esperá 30 segundos y recargá. Verificá con `docker compose ps` que `db` diga **healthy**. |
| La base quedó vacía o con errores | Hacé el reinicio de la sección 7 (`down -v` y `up -d`). |
| `docker compose up` falla por `tarjeta_virtual.sql` | Falta ese archivo en la carpeta del proyecto. Está en la rama `main`: ejecutá `git checkout main` y `git pull`. |
| Las fotos que subo no se guardan | Verificá que existan las carpetas `uploads/book`, `uploads/hitos` y `uploads/comprobantes`. Se crean solas al levantar, pero si las borraste, volvé a ejecutar `docker compose up -d --build`. |

---

## 9. Para quien quiera saber más

- **Estilos visuales (temas):** [`.docs/estilos.md`](.docs/estilos.md)
- **Entorno Docker en detalle:** [`.docs/entorno_docker.md`](.docs/entorno_docker.md)
- **Arquitectura y flujos:** [`.docs/arquitectura.md`](.docs/arquitectura.md)
- **Base de datos:** [`.docs/base_datos.md`](.docs/base_datos.md)
- **Módulos de la tarjeta:** [`.docs/modulos.md`](.docs/modulos.md)

### Estilos disponibles

`elegante` · `divertido` · `infantil` · `romántico` · `vintage` · `minimalista`

### Tecnología

PHP 8.2 (Apache) · MariaDB 10.4 · HTML/CSS/JS sin frameworks.
