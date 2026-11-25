<?php
require_once __DIR__ . '/conexion.php';

/**
 * Clase Producto
 * Contiene las propiedades del producto y los métodos
 * guardar(), editar(), buscar(), listar() y validar().
 */
class Producto
{
    public ?int $id = null;
    public string $codigo = '';
    public string $producto = '';
    public $precio = null;
    public $cantidad = null;

    /** Errores de validación: campo => mensaje */
    public array $errores = [];

    private DB $db;

    public function __construct(array $datos = [])
    {
        $this->db = DB::getInstancia();
        $this->id       = isset($datos['id']) && $datos['id'] !== '' ? (int) $datos['id'] : null;
        $this->codigo   = trim($datos['Codigo'] ?? '');
        $this->producto = trim($datos['Producto'] ?? '');
        $this->precio   = $datos['Precio'] ?? null;
        $this->cantidad = $datos['Cantidad'] ?? null;
    }

    /**
     * Valida los campos obligatorios.
     * Regla de negocio: al GUARDAR la cantidad mínima es 1,
     * al MODIFICAR la cantidad mínima es 0 (producto agotado).
     */
    public function validar(string $accion): bool
    {
        $this->errores = [];

        if ($this->codigo === '') {
            $this->errores['Codigo'] = 'El código es obligatorio.';
        } elseif (mb_strlen($this->codigo) > 20) {
            $this->errores['Codigo'] = 'El código no puede superar 20 caracteres.';
        }

        if ($this->producto === '') {
            $this->errores['Producto'] = 'El nombre del producto es obligatorio.';
        } elseif (mb_strlen($this->producto) > 100) {
            $this->errores['Producto'] = 'El producto no puede superar 100 caracteres.';
        }

        if ($this->precio === null || $this->precio === '' || !is_numeric($this->precio)) {
            $this->errores['Precio'] = 'El precio es obligatorio y debe ser numérico.';
        } elseif ((float) $this->precio <= 0) {
            $this->errores['Precio'] = 'El precio debe ser mayor que 0.';
        }

        $minCantidad = ($accion === 'Guardar') ? 1 : 0;
        if ($this->cantidad === null || $this->cantidad === '' || filter_var($this->cantidad, FILTER_VALIDATE_INT) === false) {
            $this->errores['Cantidad'] = 'La cantidad es obligatoria y debe ser un número entero.';
        } elseif ((int) $this->cantidad < $minCantidad) {
            $this->errores['Cantidad'] = "La cantidad mínima es {$minCantidad}.";
        }

        if ($accion === 'Modificar' && $this->id === null) {
            $this->errores['id'] = 'No se indicó el producto a modificar.';
        }

        return count($this->errores) === 0;
    }

    /** Inserta un producto nuevo. Devuelve el id generado. */
    public function guardar(): int
    {
        return $this->db->insertSeguro(
            'INSERT INTO productos (codigo, producto, precio, cantidad)
             VALUES (:codigo, :producto, :precio, :cantidad)',
            [
                ':codigo'   => $this->codigo,
                ':producto' => $this->producto,
                ':precio'   => (float) $this->precio,
                ':cantidad' => (int) $this->cantidad,
            ]
        );
    }

    /** Actualiza un producto existente. Devuelve filas afectadas. */
    public function editar(): int
    {
        return $this->db->updateSeguro(
            'UPDATE productos
                SET codigo = :codigo, producto = :producto,
                    precio = :precio, cantidad = :cantidad
              WHERE id = :id',
            [
                ':codigo'   => $this->codigo,
                ':producto' => $this->producto,
                ':precio'   => (float) $this->precio,
                ':cantidad' => (int) $this->cantidad,
                ':id'       => $this->id,
            ]
        );
    }

    /** Busca por código o nombre (coincidencia parcial). */
    public function buscar(string $termino): array
    {
        $like = '%' . $termino . '%';
        return $this->db->query(
            'SELECT id, codigo, producto, precio, cantidad
               FROM productos
              WHERE codigo LIKE :t1 OR producto LIKE :t2
              ORDER BY id DESC',
            [':t1' => $like, ':t2' => $like]
        );
    }

    /** Lista todos los productos. */
    public function listar(): array
    {
        return $this->db->query(
            'SELECT id, codigo, producto, precio, cantidad FROM productos ORDER BY id DESC'
        );
    }

    /** ¿Existe otro producto con el mismo código? (evita duplicados) */
    public function codigoExiste(): bool
    {
        $filas = $this->db->query(
            'SELECT id FROM productos WHERE codigo = :c AND id <> :id',
            [':c' => $this->codigo, ':id' => $this->id ?? 0]
        );
        return count($filas) > 0;
    }
}
