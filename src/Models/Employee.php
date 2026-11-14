<?php
namespace App\Models;

use App\Core\Model;

class Employee extends Model
{
    // Colonnes affichables : le mot de passe haché n'est lu que pour la connexion
    private const PUBLIC_COLUMNS = 'num_employe, nom_employe, prenom_employe, email_employe, tel_employe, role';

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM employe WHERE email_employe = ?');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT ' . self::PUBLIC_COLUMNS . ' FROM employe WHERE num_employe = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function all(): array
    {
        return $this->db->query('SELECT ' . self::PUBLIC_COLUMNS . ' FROM employe ORDER BY nom_employe')->fetchAll();
    }

    public function create(array $data, string $password): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO employe (nom_employe, prenom_employe, email_employe, tel_employe, role, mdp_employe)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['nom_employe'], $data['prenom_employe'], $data['email_employe'],
            $data['tel_employe'], $data['role'], password_hash($password, PASSWORD_BCRYPT),
        ]);
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE employe SET nom_employe = ?, prenom_employe = ?, email_employe = ?, tel_employe = ?, role = ?
             WHERE num_employe = ?'
        );
        $stmt->execute([
            $data['nom_employe'], $data['prenom_employe'], $data['email_employe'],
            $data['tel_employe'], $data['role'], $id,
        ]);
    }

    public function updatePassword(int $id, string $password): void
    {
        $stmt = $this->db->prepare('UPDATE employe SET mdp_employe = ? WHERE num_employe = ?');
        $stmt->execute([password_hash($password, PASSWORD_BCRYPT), $id]);
    }

    // Vérifie le mot de passe actuel (changement de mot de passe, CDC 5.2)
    public function checkPassword(int $id, string $password): bool
    {
        $stmt = $this->db->prepare('SELECT mdp_employe FROM employe WHERE num_employe = ?');
        $stmt->execute([$id]);
        $hash = $stmt->fetchColumn();
        return $hash !== false && password_verify($password, $hash);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM employe WHERE num_employe = ?');
        $stmt->execute([$id]);
    }
}