<?php
session_start();
include '../php/Conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    echo "Usuario no autenticado.";
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$total = isset($_POST['total']) ? (float)$_POST['total'] : 0;
$metodo_pago = $_POST['metodo_pago'];
$direccion = $_POST['direccion'];

if (!isset($_SESSION['carrito']) || count($_SESSION['carrito']) === 0) {
    echo "Tu carrito está vacío.";
    exit;
}

$productos_ids = [];
foreach ($_SESSION['carrito'] as $item) {
    $productos_ids[] = $item['producto_id'];
}

$ids_para_consulta = implode(",", array_map('intval', $productos_ids));

$sql = "SELECT ID, Nombre_producto, Precio FROM productos WHERE ID IN ($ids_para_consulta)";
$result = $conexion->query($sql);

$productos_en_carrito = [];
if ($result->num_rows > 0) {
    while ($producto = $result->fetch_assoc()) {
        foreach ($_SESSION['carrito'] as $item) {
            if ($item['producto_id'] == $producto['ID']) {
                $producto['cantidad'] = $item['cantidad'];
                $productos_en_carrito[] = $producto;
            }
        }
    }
}

// Insertar la nueva venta en la tabla ventas
$sql = "INSERT INTO ventas (Total, Usuario_id) VALUES (?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('di', $total, $usuario_id);
$stmt->execute();
$venta_id = $stmt->insert_id;

// Insertar los detalles de la compra en la tabla detalle_venta, incluyendo el campo "Envio"
$sql = "INSERT INTO detalle_venta (Venta_id, Producto_id, Cantidad, Subtotal, Envio) VALUES (?, ?, ?, ?, ?)";
$stmt = $conexion->prepare($sql);

foreach ($productos_en_carrito as $producto) {
    $subtotal = $producto['Precio'] * $producto['cantidad'];
    $stmt->bind_param('iiids', $venta_id, $producto['ID'], $producto['cantidad'], $subtotal, $direccion);
    $stmt->execute();
}

$conexion->close();

// Vaciar el carrito
unset($_SESSION['carrito']);

echo "Compra confirmada. ¡Gracias por tu compra!";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/usuarios/Ticket.css">
    <title>Ticket de Compra</title>
</head>
<body>
    <main>
        <h2>Compra confirmada. ¡Gracias por tu compra!</h2>
        <a href="../index.php" class="btn">Volver al inicio</a>
    </main>
</body>
</html>
