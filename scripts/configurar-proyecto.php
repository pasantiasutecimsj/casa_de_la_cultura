<?php

declare(strict_types=1);

const APP_NAME = 'IMSJ';
const ADMIN_EMAIL = 'admin.computos@imsj';
const ADMIN_ROLE_KEY = 'Admin';
const ADMIN_ROLE_NAME = 'Administrador';
const PROJECT_ROOT = __DIR__.'/..';

$arguments = array_slice($argv, 1);
$unknownArguments = array_diff($arguments, ['--dry-run']);

if ($unknownArguments !== []) {
    fail('Opciones desconocidas: '.implode(', ', $unknownArguments));
}

$dryRun = in_array('--dry-run', $arguments, true);
$root = realpath(PROJECT_ROOT) ?: PROJECT_ROOT;
$folder = basename(str_replace('\\', '/', $root));
$databaseName = $folder;
$projectKey = $folder;
$routeSegment = rawurlencode($folder);
$packageName = slug($folder);
$appUrl = "http://localhost/{$routeSegment}";
$envPath = $root.'/.env';
$sourceEnv = readFileOrFail(is_file($envPath) ? $envPath : $root.'/.env.example');
$registryDatabase = environmentValue($sourceEnv, 'DB_REGISTRO_DATABASE', 'registro_usuarios');
$composerBinary = getenv('SETUP_COMPOSER_BIN') ?: 'composer';
$npmBinary = getenv('SETUP_NPM_BIN') ?: 'npm';

validateDatabaseName($databaseName);
validateDatabaseName($registryDatabase);
ensureSeparateDatabases($databaseName, $registryDatabase);

section('Configuración automática');
line('Aplicación: '.APP_NAME);
line("Carpeta:    {$folder}");
line("URL:        {$appUrl}");
line("Base:       {$databaseName}");
line("Proyecto:   {$projectKey}");
line("Usuarios:   {$registryDatabase}");
line('Admin:      '.ADMIN_EMAIL);
line("Apache:     /{$routeSegment}/");
line("Vite:       /{$routeSegment}/build/");

if ($dryRun) {
    try {
        run([$composerBinary, '--version'], $root);
        run([$npmBinary, '--version'], $root);
    } catch (Throwable $exception) {
        fail($exception->getMessage());
    }

    line('');
    line('Simulación terminada: no se modificó nada.');
    exit(0);
}

try {
    backupEnvironment($envPath, $root);

    $configuredEnv = configureEnvironment($sourceEnv, [
        'APP_NAME' => APP_NAME,
        'APP_URL' => $appUrl,
        'DB_CONNECTION' => 'mysql',
        'DB_DATABASE' => $databaseName,
        'REGISTRO_USUARIOS_PROYECTO_CLAVE' => $projectKey,
    ]);

    writeFile($envPath, $configuredEnv);
    replaceRoutePlaceholder($root.'/public/.htaccess', $routeSegment);
    replaceRoutePlaceholder($root.'/vite.config.js', $routeSegment);
    updatePackageName($root.'/composer.json', "imsj/{$packageName}");
    updatePackageName($root.'/package.json', $packageName);

    run([$composerBinary, 'update', '--no-scripts', '--lock', '--no-install', '--no-interaction'], $root);
    run([$composerBinary, 'install', '--no-interaction', '--prefer-dist'], $root);
    run([$npmBinary, 'install'], $root);

    if (environmentValue($configuredEnv, 'APP_KEY') === '') {
        run([PHP_BINARY, 'artisan', 'key:generate', '--force'], $root);
    }

    createDatabase(
        environmentValue($configuredEnv, 'DB_HOST', '127.0.0.1'),
        environmentValue($configuredEnv, 'DB_PORT', '3306'),
        $databaseName,
        environmentValue($configuredEnv, 'DB_USERNAME', 'root'),
        environmentValue($configuredEnv, 'DB_PASSWORD'),
    );
    run([PHP_BINARY, 'artisan', 'migrate', '--force'], $root);
    createStorageLink($root);
    run([PHP_BINARY, 'artisan', 'optimize:clear'], $root);
    run([PHP_BINARY, 'artisan', 'test', '--compact'], $root);
    run([$npmBinary, 'run', 'build'], $root);

    ensureRoutesAreConfigured($root);
    provisionRegistryProject(
        environmentValue($configuredEnv, 'DB_REGISTRO_HOST', '127.0.0.1'),
        environmentValue($configuredEnv, 'DB_REGISTRO_PORT', '3306'),
        $registryDatabase,
        environmentValue($configuredEnv, 'DB_REGISTRO_USERNAME', 'root'),
        environmentValue($configuredEnv, 'DB_REGISTRO_PASSWORD'),
        $projectKey,
        $folder,
    );

    section('Proyecto listo');
    line("URL: {$appUrl}");
    line('Desarrollo: composer dev');
} catch (Throwable $exception) {
    fail($exception->getMessage());
}

function slug(string $value): string
{
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
    $slug = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $ascii) ?: '');

    return trim($slug, '-') ?: 'proyecto';
}

function validateDatabaseName(string $name): void
{
    if (strlen($name) > 64 || preg_match('/^[A-Za-z0-9_-]+$/', $name) !== 1) {
        fail('El nombre de la carpeta no es válido como base de datos. Use solo letras, números, guiones y guiones bajos.');
    }
}

function ensureSeparateDatabases(string $database, string $registryDatabase): void
{
    if (strcasecmp($database, $registryDatabase) === 0) {
        fail("La base propia y el registro de usuarios no pueden ser la misma (`{$database}`).");
    }
}

function environmentValue(string $contents, string $key, string $default = ''): string
{
    if (preg_match('/^'.preg_quote($key, '/').'=(.*)$/m', $contents, $matches) !== 1) {
        return $default;
    }

    $value = trim($matches[1]);

    if (strlen($value) >= 2 && in_array($value[0], ['"', "'"], true) && str_ends_with($value, $value[0])) {
        $value = substr($value, 1, -1);
    }

    return str_replace(['\\"', '\\$', '\\\\'], ['"', '$', '\\'], $value);
}

/** @param array<string, string> $values */
function configureEnvironment(string $contents, array $values): string
{
    foreach ($values as $key => $value) {
        $line = $key.'='.quoteEnvironmentValue($value);
        $pattern = '/^'.preg_quote($key, '/').'=.*$/m';

        if (preg_match($pattern, $contents) === 1) {
            $contents = preg_replace_callback($pattern, static fn (): string => $line, $contents, 1) ?? $contents;
        } else {
            $contents = rtrim($contents)."\n{$line}\n";
        }
    }

    return $contents;
}

function quoteEnvironmentValue(string $value): string
{
    $escaped = str_replace(['\\', '"', '$'], ['\\\\', '\\"', '\\$'], $value);

    return '"'.$escaped.'"';
}

function backupEnvironment(string $envPath, string $root): void
{
    if (! is_file($envPath)) {
        return;
    }

    $directory = $root.'/storage/app/private/setup-backups';

    if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
        throw new RuntimeException('No se pudo crear el directorio de respaldos.');
    }

    $backup = $directory.'/.env.'.date('Ymd-His').'.bak';

    if (! copy($envPath, $backup)) {
        throw new RuntimeException('No se pudo respaldar el archivo .env actual.');
    }

    line("Respaldo: {$backup}");
}

function replaceRoutePlaceholder(string $path, string $routeSegment): void
{
    $contents = readFileOrFail($path);

    if (! str_contains($contents, '!!!!NOMBRE_PROYECTO!!!!')) {
        line('Sin cambios: '.relativePath($path));

        return;
    }

    writeFile($path, str_replace('!!!!NOMBRE_PROYECTO!!!!', $routeSegment, $contents));
}

function updatePackageName(string $path, string $name): void
{
    $json = json_decode(readFileOrFail($path), true, 512, JSON_THROW_ON_ERROR);
    $json['name'] = $name;
    $contents = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

    writeFile($path, $contents."\n");
}

function createDatabase(string $host, string $port, string $database, string $username, string $password): void
{
    section('Base de datos');

    if (! extension_loaded('pdo_mysql')) {
        throw new RuntimeException('PHP no tiene habilitada la extensión pdo_mysql.');
    }

    try {
        $pdo = new PDO(
            "mysql:host={$host};port={$port};charset=utf8mb4",
            $username,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
        );
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    } catch (PDOException $exception) {
        try {
            new PDO(
                "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
                $username,
                $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
            );
        } catch (PDOException) {
            throw new RuntimeException('No se pudo crear o abrir la base de datos: '.$exception->getMessage());
        }
    }

    line("Base `{$database}` disponible.");
}

function provisionRegistryProject(
    string $host,
    string $port,
    string $database,
    string $username,
    string $password,
    string $projectKey,
    string $projectName,
): void {
    section('Registro de usuarios');

    if (! extension_loaded('pdo_mysql')) {
        throw new RuntimeException('PHP no tiene habilitada la extensión pdo_mysql.');
    }

    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ],
    );

    $pdo->beginTransaction();

    try {
        $userStatement = $pdo->prepare(
            'SELECT id, activo FROM users WHERE email = :email LIMIT 1 FOR UPDATE'
        );
        $userStatement->execute(['email' => ADMIN_EMAIL]);
        $user = $userStatement->fetch(PDO::FETCH_ASSOC);

        if ($user === false) {
            throw new RuntimeException('No existe el usuario administrador '.ADMIN_EMAIL.'.');
        }

        if (! (bool) $user['activo']) {
            throw new RuntimeException('El usuario administrador '.ADMIN_EMAIL.' está inactivo.');
        }

        $projectStatement = $pdo->prepare(
            'SELECT id, activo FROM proyectos WHERE clave = :clave LIMIT 1 FOR UPDATE'
        );
        $projectStatement->execute(['clave' => $projectKey]);
        $project = $projectStatement->fetch(PDO::FETCH_ASSOC);
        $projectCreated = $project === false;

        if ($projectCreated) {
            $insertProject = $pdo->prepare(
                'INSERT INTO proyectos (nombre, clave, activo, created_at, updated_at)
                 VALUES (:nombre, :clave, 1, NOW(), NOW())'
            );
            $insertProject->execute([
                'nombre' => $projectName,
                'clave' => $projectKey,
            ]);
            $projectId = (int) $pdo->lastInsertId();
        } else {
            if (! (bool) $project['activo']) {
                throw new RuntimeException("El proyecto `{$projectKey}` existe pero está inactivo.");
            }

            $projectId = (int) $project['id'];
        }

        $roleStatement = $pdo->prepare(
            "SELECT id, clave FROM roles
             WHERE proyecto_id = :proyecto_id AND clave IN ('Admin', 'administrador')
             ORDER BY clave = 'Admin' DESC
             LIMIT 1 FOR UPDATE"
        );
        $roleStatement->execute(['proyecto_id' => $projectId]);
        $role = $roleStatement->fetch(PDO::FETCH_ASSOC);
        $roleCreated = $role === false;

        if ($roleCreated) {
            $insertRole = $pdo->prepare(
                'INSERT INTO roles
                    (proyecto_id, nombre, clave, descripcion, por_defecto, created_at, updated_at)
                 VALUES
                    (:proyecto_id, :nombre, :clave, :descripcion, 0, NOW(), NOW())'
            );
            $insertRole->execute([
                'proyecto_id' => $projectId,
                'nombre' => ADMIN_ROLE_NAME,
                'clave' => ADMIN_ROLE_KEY,
                'descripcion' => 'Administración del proyecto.',
            ]);
            $roleId = (int) $pdo->lastInsertId();
            $roleKey = ADMIN_ROLE_KEY;
        } else {
            $roleId = (int) $role['id'];
            $roleKey = (string) $role['clave'];
        }

        $projectAssignment = $pdo->prepare(
            'SELECT id FROM proyecto_usuario
             WHERE proyecto_id = :proyecto_id AND usuario_id = :usuario_id
             LIMIT 1 FOR UPDATE'
        );
        $projectAssignment->execute([
            'proyecto_id' => $projectId,
            'usuario_id' => $user['id'],
        ]);
        $projectAssigned = $projectAssignment->fetchColumn() !== false;

        if (! $projectAssigned) {
            $insertProjectAssignment = $pdo->prepare(
                'INSERT INTO proyecto_usuario (proyecto_id, usuario_id, created_at, updated_at)
                 VALUES (:proyecto_id, :usuario_id, NOW(), NOW())'
            );
            $insertProjectAssignment->execute([
                'proyecto_id' => $projectId,
                'usuario_id' => $user['id'],
            ]);
        }

        $roleAssignment = $pdo->prepare(
            'SELECT id FROM rol_usuario
             WHERE rol_id = :rol_id AND usuario_id = :usuario_id
             LIMIT 1 FOR UPDATE'
        );
        $roleAssignment->execute([
            'rol_id' => $roleId,
            'usuario_id' => $user['id'],
        ]);
        $roleAssigned = $roleAssignment->fetchColumn() !== false;

        if (! $roleAssigned) {
            $insertRoleAssignment = $pdo->prepare(
                'INSERT INTO rol_usuario (rol_id, usuario_id, created_at, updated_at)
                 VALUES (:rol_id, :usuario_id, NOW(), NOW())'
            );
            $insertRoleAssignment->execute([
                'rol_id' => $roleId,
                'usuario_id' => $user['id'],
            ]);
        }

        $pdo->commit();

        line("Proyecto `{$projectKey}` ".($projectCreated ? 'creado.' : 'ya existente.'));
        line("Rol `{$roleKey}` ".($roleCreated ? 'creado.' : 'ya existente.'));
        line('Administrador '.($projectAssigned && $roleAssigned ? 'ya asignado.' : 'asignado.'));
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}

function createStorageLink(string $root): void
{
    if (! file_exists($root.'/public/storage') && ! is_link($root.'/public/storage')) {
        run([PHP_BINARY, 'artisan', 'storage:link'], $root, false);
    }
}

/** @param list<string> $command */
function run(array $command, string $workingDirectory, bool $required = true): void
{
    section('$ '.implode(' ', $command));
    $processCommand = $command;

    if (PHP_OS_FAMILY === 'Windows' && preg_match('/\.(bat|cmd)$/i', $command[0]) === 1) {
        $processCommand = 'call '.windowsCommand($command);
    }

    $process = proc_open($processCommand, [
        0 => ['file', 'php://stdin', 'r'],
        1 => ['file', 'php://stdout', 'w'],
        2 => ['file', 'php://stderr', 'w'],
    ], $pipes, $workingDirectory);

    if (! is_resource($process)) {
        throw new RuntimeException('No se pudo iniciar el comando: '.implode(' ', $command));
    }

    $exitCode = proc_close($process);

    if ($exitCode !== 0 && $required) {
        throw new RuntimeException("El comando terminó con código {$exitCode}: ".implode(' ', $command));
    }

    if ($exitCode !== 0) {
        line('Aviso: no se pudo crear el enlace de storage.');
    }
}

/** @param list<string> $command */
function windowsCommand(array $command): string
{
    return implode(' ', array_map(
        static fn (string $argument): string => '"'.str_replace('"', '""', $argument).'"',
        $command,
    ));
}

function ensureRoutesAreConfigured(string $root): void
{
    foreach (['public/.htaccess', 'vite.config.js'] as $relativePath) {
        if (str_contains(readFileOrFail($root.'/'.$relativePath), '!!!!NOMBRE_PROYECTO!!!!')) {
            throw new RuntimeException("No se configuró {$relativePath}.");
        }
    }
}

function readFileOrFail(string $path): string
{
    $contents = @file_get_contents($path);

    if ($contents === false) {
        fail('No se pudo leer '.relativePath($path).'.');
    }

    return $contents;
}

function writeFile(string $path, string $contents): void
{
    if (file_put_contents($path, $contents, LOCK_EX) === false) {
        throw new RuntimeException('No se pudo escribir '.relativePath($path).'.');
    }

    line('Configurado: '.relativePath($path));
}

function relativePath(string $path): string
{
    $root = str_replace('\\', '/', realpath(PROJECT_ROOT) ?: PROJECT_ROOT);
    $path = str_replace('\\', '/', $path);

    return ltrim(str_replace($root, '', $path), '/');
}

function section(string $message): void
{
    line('');
    line("== {$message} ==");
}

function line(string $message): void
{
    echo $message.PHP_EOL;
}

function fail(string $message): never
{
    fwrite(STDERR, PHP_EOL."ERROR: {$message}".PHP_EOL);
    exit(1);
}
