<?php
namespace Tests\Support;

use PDO;

// Connexion de test : chaque test tourne dans une transaction annulée à la fin.
// Une transaction ouverte par le code testé (vente en magasin) devient un SAVEPOINT,
// MySQL ne sachant pas imbriquer deux transactions.
final class NestedTransactionPdo extends PDO
{
    private int $depth = 0;

    public function beginTransaction(): bool
    {
        if ($this->depth === 0) {
            parent::beginTransaction();
        } else {
            $this->exec("SAVEPOINT level{$this->depth}");
        }
        $this->depth++;
        return true;
    }

    public function commit(): bool
    {
        $this->depth--;
        if ($this->depth === 0) {
            return parent::commit();
        }
        $this->exec("RELEASE SAVEPOINT level{$this->depth}");
        return true;
    }

    public function rollBack(): bool
    {
        $this->depth--;
        if ($this->depth === 0) {
            return parent::rollBack();
        }
        $this->exec("ROLLBACK TO SAVEPOINT level{$this->depth}");
        return true;
    }

    public function inTransaction(): bool
    {
        return $this->depth > 0;
    }
}
