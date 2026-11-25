<?php
/**
 * Clase DB (patrón singleton)
 * Maneja la conexión PDO a MySQL y expone métodos seguros
 * (consultas preparadas) para evitar inyección SQL.
 */
class DB
{
    private static ?DB $instancia = null;
    private PDO $pdo;

    // Ajusta estos datos según tu WampServer / XAMPP
    private string $host = 'localhost';
    private string $db   = 'productosdb';
    private string $user = 'root';
    private string $pass = '';

    private function __construct()
    {
        $dsn = "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $this->user, $this->pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    /** Devuelve siempre la misma instancia (singleton) */
    public static function getInstancia(): DB
    {
        if (self::$instancia === null) {
            self::$instancia = new DB();
        }
        return self::$instancia;
    }

    /** INSERT seguro con consulta preparada. Devuelve el id insertado. */
    public function insertSeguro(string $sql, array $params): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $this->pdo->lastInsertId();
    }

    /** UPDATE seguro con consulta preparada. Devuelve filas afectadas. */
    public function updateSeguro(string $sql, array $params): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /** SELECT con consulta preparada. Devuelve arreglo de filas. */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
