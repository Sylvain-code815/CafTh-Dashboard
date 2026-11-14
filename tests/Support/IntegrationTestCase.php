<?php
namespace Tests\Support;

use App\Core\Database;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;

// Base des tests d'intégration : vraie base MySQL (cafthe_test), remise en l'état après chaque test
abstract class IntegrationTestCase extends TestCase
{
    private static ?NestedTransactionPdo $pdo = null;
    protected PDO $db;

    protected function setUp(): void
    {
        if (self::$pdo === null) {
            $db = (require ROOT . '/tests/config.php')['db'];
            try {
                self::$pdo = new NestedTransactionPdo(Database::dsn($db), $db['user'], $db['pass'], Database::OPTIONS);
            } catch (PDOException $e) {
                $this->markTestSkipped(
                    "Base de test inaccessible ({$e->getMessage()}) : lancer php tests/create_test_db.php"
                );
            }
        }
        $this->db = self::$pdo;
        Database::setConnection($this->db);
        $this->db->beginTransaction();
    }

    protected function tearDown(): void
    {
        // Annule tout ce que le test a écrit
        while (isset($this->db) && $this->db->inTransaction()) {
            $this->db->rollBack();
        }
    }

    protected function value(string $sql, array $params = []): mixed
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}
