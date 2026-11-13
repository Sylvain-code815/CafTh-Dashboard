<?php
namespace App\Core;

use PDO;

// Classe mère des modèles : leur donne l'accès à la base
abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::get();
    }
}