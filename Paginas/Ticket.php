<?php
session_start();
include '../php/Conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    echo "Usuario no autenticado.";
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

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
$total = 0;  // Asegurarnos de inicializar $total

if ($result->num_rows > 0) {
    while ($producto = $result->fetch_assoc()) {
        foreach ($_SESSION['carrito'] as $item) {
            if ($item['producto_id'] == $producto['ID']) {
                $producto['cantidad'] = $item['cantidad'];
                $productos_en_carrito[] = $producto;
                $total += $producto['Precio'] * $item['cantidad'];
            }
        }
    }
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Metadatos para la página -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Link para el archivo CSS que contiene los estilos para esta página -->
    <link rel="stylesheet" href="../CSS/usuarios/Ticket.css">
    <title>Detalles de la Compra</title>
</head>
<body>
    <main class="falso_main">
        <h2>Detalles de la Compra</h2>
        
        <!-- Formulario para confirmar la compra -->
        <form action="ConfirmarCompra.php" method="POST">
            <!-- Campo oculto para enviar el total de la compra -->
            <input type="hidden" name="total" value="<?php echo $total; ?>">
            
            <label for="metodo_pago">Método de Pago:</label>
            <!-- Select para elegir el método de pago -->
            <select name="metodo_pago" id="metodo_pago" required onchange="toggleTarjetaFields()">
                <option value="tarjeta">Tarjeta de Crédito/Débito</option>
                <option value="efectivo">Pago en Efectivo</option>
            </select>

            <!-- Contenedor de los campos relacionados con la tarjeta -->
            <div id="tarjeta-fields" class="card-container">
                <label for="numero_tarjeta">Ingrese los números de su tarjeta</label>
                <input type="text" class="card-input" name="numero_tarjeta" id="numero_tarjeta" placeholder="Número de Tarjeta">
                
                <label for="fecha_expiracion">Fecha de Expiración (MM/AA)</label>
                <input type="text" class="card-input" name="fecha_expiracion" id="fecha_expiracion" placeholder="Fecha de Expiración (MM/AA)">
                
                <label for="cvv">CVV</label>
                <input type="text" class="card-input" name="cvv" id="cvv" placeholder="CVV">

                <!-- Imagen de una tarjeta (ficticia) -->
                <img src="../Img/mastercard.png" alt="Imagen de tarjeta" class="card-image">
            </div>

            <!-- Campo para ingresar la dirección de envío -->
            <label for="direccion">Dirección de Envío:</label>
            <input type="text" id="direccion" name="direccion" required>
            
            <!-- Mostrar el total de la compra -->
            <h3>Total: $<?php echo number_format($total, 2); ?></h3>

            <button type="submit" class="btn">Confirmar Compra</button>
            <!-- Enlace para volver al inicio -->
        <a href="../index.php" class="btn">Volver al inicio</a>
        </form>
        
        
    </main>

    <!-- Script para gestionar la visibilidad de los campos de la tarjeta -->
    <script src="../JS/Ticket.js"></script>
</body>
</html>
