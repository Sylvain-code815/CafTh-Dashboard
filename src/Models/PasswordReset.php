<?php
namespace App\Models;

use App\Core\Model;

// Jetons de réinitialisation du mot de passe
// Le jeton n'est envoyé que par email ; la base ne garde que son empreinte SHA-256.
class PasswordReset extends Model
{
    public const EMPLOYEE = 'employee';
    public const LIFETIME = 3600;     // valable 1 heure
    public const MAX_PER_ACCOUNT = 3; // demandes acceptées par compte sur 15 minutes

    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32)); // 64 caractères hexadécimaux, impossible à deviner
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    // Jeton utilisable : jamais utilisé et pas encore expiré
    public static function isUsable(array $reset, int $now): bool
    {
        return $reset['used_at'] === null && strtotime($reset['expires_at']) > $now;
    }

    // Crée un jeton et renvoie sa valeur en clair (à mettre dans le lien de l'email).
    // Les anciens jetons non utilisés du compte sont invalidés : seul le dernier lien fonctionne.
    public function create(string $accountType, int $accountId): string
    {
        $stmt = $this->db->prepare(
            'UPDATE password_reset SET used_at = NOW()
             WHERE account_type = ? AND account_id = ? AND used_at IS NULL'
        );
        $stmt->execute([$accountType, $accountId]);

        $token = self::generateToken();
        // Dates calculées par MySQL
        $stmt = $this->db->prepare(
            'INSERT INTO password_reset (account_type, account_id, token_hash, expires_at)
             VALUES (?, ?, ?, NOW() + INTERVAL ? SECOND)'
        );
        $stmt->execute([$accountType, $accountId, self::hashToken($token), self::LIFETIME]);
        return $token;
    }

    // Limite l'envoi d'emails en rafale vers un même compte
    public function tooManyRequests(string $accountType, int $accountId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM password_reset
             WHERE account_type = ? AND account_id = ? AND created_at > NOW() - INTERVAL 15 MINUTE'
        );
        $stmt->execute([$accountType, $accountId]);
        return (int) $stmt->fetchColumn() >= self::MAX_PER_ACCOUNT;
    }

    // Jeton valide pour ce type de compte, ou null (inconnu, expiré ou déjà utilisé)
    public function findUsable(string $accountType, string $token): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT *, NOW() AS db_now FROM password_reset WHERE token_hash = ? AND account_type = ?'
        );
        $stmt->execute([self::hashToken($token), $accountType]);
        $reset = $stmt->fetch();
        return $reset && self::isUsable($reset, strtotime($reset['db_now'])) ? $reset : null;
    }

    // Usage unique : renvoie false si le jeton a déjà été utilisé entre-temps (double envoi du formulaire)
    public function markUsed(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE password_reset SET used_at = NOW() WHERE id = ? AND used_at IS NULL');
        $stmt->execute([$id]);
        return $stmt->rowCount() === 1;
    }
}
