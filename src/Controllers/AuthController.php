<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Config;
use App\Core\Controller;
use App\Core\Mailer;
use App\Models\Employee;
use App\Models\PasswordReset;

class AuthController extends Controller
{
    public function login(): void
    {
        if (Auth::user()) {
            redirect('dashboard');
        }

        $error = null;
        $email = '';

        if ($this->isPost()) {
            check_csrf();
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $employee = (new Employee())->findByEmail($email);

            // Même message que l'email soit inconnu ou le mot de passe faux :
            // on ne révèle pas quels comptes existent
            if ($employee && password_verify($password, $employee['mdp_employe'])) {
                Auth::login($employee);
                redirect('dashboard');
            }
            $error = 'Email ou mot de passe incorrect';
        }

        $this->render('auth/login', ['title' => 'Connexion', 'error' => $error, 'email' => $email]);
    }

    public function logout(): void
    {
        if ($this->isPost()) {
            check_csrf();
            Auth::logout();
        }
        redirect('login');
    }

    // Mot de passe oublié (CDC 5.1) : envoie un lien temporaire par email
    public function forgotPassword(): void
    {
        if (Auth::user()) {
            redirect('dashboard');
        }

        $error = null;
        $sent = false;
        $email = '';

        if ($this->isPost()) {
            check_csrf();
            $email = trim($_POST['email'] ?? '');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Email invalide';
            } elseif ($this->tooManyResetRequests()) {
                $error = 'Trop de demandes, réessayez dans quelques minutes.';
            } else {
                $this->sendResetLink($email);
                // Même réponse que le compte existe ou non : on ne révèle pas quels emails sont inscrits
                $sent = true;
            }
        }

        $this->render('auth/forgot', [
            'title' => 'Mot de passe oublié', 'error' => $error, 'sent' => $sent, 'email' => $email,
        ]);
    }

    // Lien reçu par email : choix du nouveau mot de passe
    public function resetPassword(): void
    {
        if (Auth::user()) {
            redirect('dashboard');
        }
        header('Referrer-Policy: no-referrer'); // le jeton de l'URL ne part pas vers un autre site

        $token = $_POST['token'] ?? $_GET['token'] ?? '';
        $token = is_string($token) ? $token : '';
        $resets = new PasswordReset();
        $reset = $token !== '' ? $resets->findUsable(PasswordReset::EMPLOYEE, $token) : null;
        $errors = [];

        if ($reset && $this->isPost()) {
            check_csrf();
            $password = $_POST['password'] ?? '';

            if (!Employee::isStrongPassword($password)) {
                $errors['password'] = Employee::PASSWORD_RULE;
            } elseif ($password !== ($_POST['confirm_password'] ?? '')) {
                $errors['confirm_password'] = 'Les deux mots de passe ne correspondent pas';
            } elseif ($resets->markUsed((int) $reset['id'])) {
                (new Employee())->updatePassword((int) $reset['account_id'], $password);
                flash('Mot de passe modifié, vous pouvez vous connecter.');
                redirect('login');
            } else {
                $reset = null; // utilisé entre-temps
            }
        }

        $this->render('auth/reset', [
            'title'  => 'Nouveau mot de passe',
            'valid'  => $reset !== null,
            'token'  => $token,
            'errors' => $errors,
        ]);
    }

    private function sendResetLink(string $email): void
    {
        $employee = (new Employee())->findByEmail($email);
        $resets = new PasswordReset();
        $id = $employee ? (int) $employee['num_employe'] : 0;

        if (!$employee || $resets->tooManyRequests(PasswordReset::EMPLOYEE, $id)) {
            return;
        }

        $token = $resets->create(PasswordReset::EMPLOYEE, $id);
        $link = absolute_url('reset-password', ['token' => $token]);
        $body = "Bonjour {$employee['prenom_employe']},\n\n"
            . "Une réinitialisation du mot de passe de votre compte CafThé Dashboard a été demandée.\n"
            . "Pour choisir un nouveau mot de passe, ouvrez ce lien (valable 1 heure, utilisable une seule fois) :\n\n"
            . "$link\n\n"
            . "Si vous n'êtes pas à l'origine de cette demande, ignorez cet email : votre mot de passe actuel reste valable.\n";

        (new Mailer(Config::get('mail', [])))->send($email, 'CafThé - Réinitialisation du mot de passe', $body);
    }

    // Limite simple par session : 5 demandes par quart d'heure
    private function tooManyResetRequests(): bool
    {
        $since = time() - 900;
        $requests = array_filter($_SESSION['reset_requests'] ?? [], fn (int $time) => $time > $since);
        $requests[] = time();
        $_SESSION['reset_requests'] = $requests;
        return count($requests) > 5;
    }
}
