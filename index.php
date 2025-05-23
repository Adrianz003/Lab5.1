<?php
// Configuración (debería estar en otro archivo)
define('DB_SERVER', 'tcp:servidor5-1.database.windows.net,1433');
define('DB_DATABASE', 'sql5.1');
define('DB_USERNAME', 'adrianservidor');
define('DB_PASSWORD', 'adrian123456.');

// Funciones de validación
function validarDatos($datos) {
    $errores = [];
    
    if (empty(trim($datos['nombre']))) {
        $errores[] = 'El nombre es requerido';
    }
    
    if (empty(trim($datos['primer_apellido']))) {
        $errores[] = 'El primer apellido es requerido';
    }
    
    if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electrónico no es válido';
    }
    
    if (!preg_match('/^[0-9]{10,15}$/', $datos['telefono'])) {
        $errores[] = 'El teléfono debe contener solo números (10-15 dígitos)';
    }
    
    return $errores;
}

// Procesamiento del formulario
$mensaje = '';
$error = false;
$registros = [];

try {
    $conn = new PDO("sqlsrv:server=" . DB_SERVER . ";Database=" . DB_DATABASE, 
                    DB_USERNAME, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST['enviar'])) {
        $errores = validarDatos($_POST);
        
        if (empty($errores)) {
            $stmt = $conn->prepare("INSERT INTO usuarios 
                                  (nombre, primer_apellido, segundo_apellido, correo, telefono)
                                  VALUES (:nombre, :primer_apellido, :segundo_apellido, :correo, :telefono)");
            $stmt->execute([
                ':nombre' => trim($_POST['nombre']),
                ':primer_apellido' => trim($_POST['primer_apellido']),
                ':segundo_apellido' => trim($_POST['segundo_apellido']),
                ':correo' => $_POST['correo'],
                ':telefono' => preg_replace('/[^0-9]/', '', $_POST['telefono'])
            ]);
            
            $mensaje = 'Datos guardados correctamente';
        } else {
            $error = true;
            $mensaje = implode('<br>', $errores);
        }
    }

    // Obtener registros
    $stmt = $conn->query("SELECT * FROM usuarios ORDER BY id DESC");
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = true;
    $mensaje = 'Error de base de datos: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- [El mismo head que tu versión original] -->
</head>
<body>
    <div class="form-container">
        <h1>Registro de Usuario</h1>
        
        <?php if ($mensaje): ?>
        <div class="response <?= $error ? 'error' : '' ?>">
            <h3><?= $error ? 'Error' : 'Éxito' ?></h3>
            <p><?= htmlspecialchars($mensaje) ?></p>
        </div>
        <script>document.querySelector(".response").style.display = "block";</script>
        <?php endif; ?>
        
        <form method="post" action="">
            <!-- [Los mismos campos del formulario que tu versión original] -->
        </form>

        <h2>Usuarios Registrados</h2>
        <?php if (!empty($registros)): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Primer Apellido</th>
                <th>Segundo Apellido</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Fecha</th>
            </tr>
            <?php foreach ($registros as $fila): ?>
            <tr>
                <td><?= $fila['id'] ?></td>
                <td><?= htmlspecialchars($fila['nombre']) ?></td>
                <td><?= htmlspecialchars($fila['primer_apellido']) ?></td>
                <td><?= htmlspecialchars($fila['segundo_apellido']) ?></td>
                <td><?= htmlspecialchars($fila['correo']) ?></td>
                <td><?= htmlspecialchars($fila['telefono']) ?></td>
                <td><?= $fila['fecha_registro'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php else: ?>
        <p>No hay registros para mostrar</p>
        <?php endif; ?>
    </div>
</body>
</html>
