<?php
namespace App\Core;

// Gestion de l'employé connecté (stocké en session)
class Auth
{
    public const ADMIN = 'ROLE_ADMIN';
    public const SELLER = 'ROLE_SELLER';

    public static function login(array $employee): void
    {
        session_regenerate_id(true); // nouvel identifiant de session : empêche la fixation de session
        $_SESSION['user'] = [
            'id'     => (int) $employee['num_employe'],
            'nom'    => $employee['nom_employe'],
            'prenom' => $employee['prenom_employe'],
            'role'   => $employee['role'],
        ];
        $_SESSION['last_activity'] = time();
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): int
    {
        return self::user()['id'] ?? 0;
    }

    public static function isAdmin(): bool
    {
        return (self::user()['role'] ?? null) === self::ADMIN;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    // Déconnexion automatique après une période d'inactivité (CDC 5.1)
    public static function checkTimeout(int $timeout): void
    {
        if (self::user() && time() - ($_SESSION['last_activity'] ?? 0) > $timeout) {
            self::logout();
            session_start();
            flash('Session expirée après 30 minutes d\'inactivité, reconnectez-vous.', 'error');
        }
        $_SESSION['last_activity'] = time();
    }
}