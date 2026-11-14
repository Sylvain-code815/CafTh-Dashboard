<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Employee;

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
}