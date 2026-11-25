<?php
/**
 * registrar.php - Controlador central del CRUD.
 * Recibe $_POST['Accion'] y decide con switch qué hacer.
 * IMPORTANTE: la salida es SOLO json_encode (sin echo/print extra).
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/Modelo/Productos.php';

$accion = $_POST['Accion'] ?? '';

// Estructura base de toda respuesta JSON
$response = [
    'success' => false,
    'message' => 'Acción no válida.',
    'accion'  => $accion,
    'errors'  => [],
];

try {
    $producto = new Producto($_POST);

    switch ($accion) {

        case 'Guardar':
            if (!$producto->validar('Guardar')) {
                $response['message'] = 'Hay errores en el formulario.';
                $response['errors']  = $producto->errores;
                break;
            }
            if ($producto->codigoExiste()) {
                $response['message'] = 'Ya existe un producto con ese código.';
                $response['errors']  = ['Codigo' => 'Código duplicado.'];
                break;
            }
            $id = $producto->guardar();
            $response['success'] = true;
            $response['message'] = 'Producto creado correctamente.';
            $response['id']      = $id;
            break;

        case 'Modificar':
            if (!$producto->validar('Modificar')) {
                $response['message'] = 'Hay errores en el formulario.';
                $response['errors']  = $producto->errores;
                break;
            }
            if ($producto->codigoExiste()) {
                $response['message'] = 'Ya existe otro producto con ese código.';
                $response['errors']  = ['Codigo' => 'Código duplicado.'];
                break;
            }
            $producto->editar();
            // rowCount puede ser 0 si no cambió nada; igual es éxito
            $response['success'] = true;
            $response['message'] = 'Producto actualizado correctamente.';
            break;

        case 'Buscar':
            $termino = trim($_POST['termino'] ?? '');
            $response['success'] = true;
            $response['message'] = 'Búsqueda realizada.';
            $response['data']    = $producto->buscar($termino);
            break;

        case 'Listar':
            $response['success'] = true;
            $response['message'] = 'Listado obtenido.';
            $response['data']    = $producto->listar();
            break;

        default:
            // Se queda con el mensaje 'Acción no válida.'
            break;
    }
} catch (PDOException $e) {
    // No exponemos detalles internos al cliente
    $response['success'] = false;
    $response['message'] = 'Error de base de datos. Verifique la conexión.';
    $response['errors']  = ['db' => 'No se pudo completar la operación.'];
} catch (Throwable $e) {
    $response['success'] = false;
    $response['message'] = 'Error inesperado en el servidor.';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
