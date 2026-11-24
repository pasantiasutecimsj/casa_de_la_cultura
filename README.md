# Plantilla Laravel IMSJ

Base para arrancar proyectos nuevos sin configurar todo desde 0.

Ya trae login configurado usando la bdd `registro_usuarios` (usada para centralizar la autenticación de los programas), acceso restringido por proyecto (por clave del mismo) y checkeo de roles y estructura y estilo inicial de frontend.

## Tecnologías

- PHP 8.2 o superior (El de Xampp mismo)
- MariaDB (El de Xampp mismo)
- Laravel 12
- Inertia
- Vue 3
- Tailwind CSS

## CONFIGURACIÓN AUTOMÁTICA

Esta es la forma fácil de configurar el nuevo proyecto. Clonamos la plantilla y corremos el archivo
`CONFIGURAR-PROYECTO-AUTOMATICO.bat`

Usa el nombre de la carpeta del nuevo proyecto para configurarlo:

1. Hace una copia de seguridad del `.env` si ya existe.
2. Usa el nombre de la carpeta raíz para `APP_URL`, `DB_DATABASE` (nombre de la BDD), y la clave del proyecto (identificador del proyecto en la BDD registro_usuarios).
3. Reemplaza `!!!!NOMBRE_PROYECTO!!!!` en `public/.htaccess` y `vite.config.js` por el nombre de la carpeta raíz.
4. Instala las dependencias de Composer y npm (composer install y npm install).
5. Genera `APP_KEY` para el .env si hace falta.
6. Crea la BDD propia del proyecto y ejecuta las migraciones básicas iniciales de Laravel.
7. Crea el enlace de `storage` (php artisan storage:link), limpia cachés, ejecuta pruebas de sanidad y compila el frontend inicial (npm run build).
8. En la BDD `registro_usuarios`, crea el proyecto y un rol básico `Administrador` (clave `Admin`) y asigna ambos al usuario `admin.computos@imsj`.

Si termina sin errores y solicita presionar cualquier tecla para finalizar, ya está terminada la configuración inicial.

## CONFIGURACIÓN MANUAL

Si no usamos el `.bat` por algún motivo, hacemos lo siguiente:

### 1. Preparamos el `.env`

Configuramos en el .env.example:

```dotenv
APP_NAME="IMSJ"
APP_URL=http://localhost/!!!NOMBRE_PROYECTO!!!

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=!!!NOMBRE_PROYECTO!!!
DB_USERNAME=root
DB_PASSWORD=

DB_REGISTRO_HOST=127.0.0.1
DB_REGISTRO_PORT=3306
DB_REGISTRO_DATABASE=registro_usuarios
DB_REGISTRO_USERNAME=root
DB_REGISTRO_PASSWORD=

REGISTRO_USUARIOS_PROYECTO_CLAVE=!!!NOMBRE_PROYECTO!!!
```

Cambiando !!!NOMBRE_PROYECTO!!! por el nombre de la carpeta raíz del proyecto.

Si se necesita configurar un valor distinto, `DB_DATABASE` es el nombre de la BDD propia del programa. `DB_REGISTRO_DATABASE` es la BDD central de usuarios y debería ser siempre registro_usuarios.
La clave `REGISTRO_USUARIOS_PROYECTO_CLAVE` tiene que coincidir con la clave registrada en la tabla `proyectos` de la BDD de registro_usuarios.

Luego, ejecutamos en una consola en la raíz del proyecto:

```bash
cp .env.example .env
```

### 2. Ajustamos las rutas locales

En estos dos archivos reemplazamos `!!!!NOMBRE_PROYECTO!!!!` por el nombre de la carpeta raíz del proyecto:

- `public/.htaccess`
- `vite.config.js`

### 3. Instalamos las dependencias

```bash
composer install
npm install -d -force
php artisan key:generate
```

### 4. Creamos la BDD

Creamos en el MySQL del Xampp una nueva BDD con el mismo valor que pusimos en `DB_DATABASE`. El panel de administrador se abre desde el programa del Xampp haciendo click en el botón "Admin"

Después ejecutamos:

```bash
php artisan migrate
php artisan storage:link
php artisan optimize:clear
```

Estas migraciones crean las tablas básicas de Laravel. Nunca deberían modificar la BDD `registro_usuarios` si todo está bien configurado.

### 5. Damos acceso desde `registro_usuarios`

En la BDD de registro_usuarios:

1. Creamos un proyecto en la tabla proyectos usando `REGISTRO_USUARIOS_PROYECTO_CLAVE` como clave.
2. Creamos para ese proyecto el rol `Administrador`, con clave `Admin`.
3. Relacionamos el usuario `admin.computos@imsj` con el proyecto en `proyecto_usuario`.
4. Relacionamos el usuario `admin.computos@imsj` con el rol en `rol_usuario`.

### 6. Compilamos

Si estamos en producción, utilizamos el comando:
```bash
npm run build
```

Sin embargo, en el entorno de desarrollo podemos utilizar:

```bash
npm run dev
```

Para no estar corriendo npm run build cada vez que se quieran ver reflejados los cambios visuales a las vistas. Este comando hará que todos los cambios a las vistas se refresquen automáticamente sin necesidad de correr nada.

## LOGIN Y PERMISOS DE USUARIO

La autenticación del proyecto usa el modelo `App\Models\RegistroUsuarios\User` y consulta directamente la BDD `registro_usuarios`.

Para exigir que el usuario pertenezca al proyecto:

```php
Route::middleware(['auth', 'project.access'])->group(function () {
    Route::get('/reportes', ReportController::class);
});
```

Para exigir alguno o varios roles de dicho proyecto, usamos la clave de dichos roles:

```php
Route::middleware(['auth', 'project.access:admin,editor'])->group(function () {
    Route::get('/administracion', AdminController::class);
});
```

El middleware comparte la clave del proyecto actual en:

```php
$request->attributes->get('current_project');
```

Inertia comparte el usuario logeado y sus roles en `page.props.auth`, junto con el proyecto actual.

## CONSIDERACIONES IMPORTANTES
- Ninguna MIGRACIÓN o SEEDER debería NUNCA alterar las BDD `registro_usuarios`, `registro_personas` ni `registro_ciudadanos`. Esto es vital ya que esa BDD es compartida por todos los proyectos.
- Así mismo, nunca un campo de ninguna tabla del proyecto debería referenciar como Clave foránea los campos de `registro_usuarios`, `registro_personas` o `registro_ciudadanos`. Cuando se necesite referenciar algo, simplemente guardamos un identificador sin referenciar en la BDD propia del proyecto y se usa para buscar de ser necesario. Esto es vital para no causar ningún problema en cascada en producción que afecte a esas BDD compartidas, ya sea por alguna migración, eliminación de datos, etc.
