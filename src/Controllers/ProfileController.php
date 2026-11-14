<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Employee;

// Profil de l'employé connecté
class ProfileController extends Controller
{
    public function index(): void
    {
        $model = new Employee();
        $errors = [];

        if ($this->isPost()) {
            check_csrf();
            $old = $_POST['old_password'] ?? '';
            $new = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            // Le changement exige l'ancien mot de passe
            if (!$model->checkPassword(Auth::id(), $old)) {
                $errors['old_password'] = 'Mot de passe actuel incorrect';
            } elseif (strlen($new) < 8 || !preg_match('/[A-Z]/', $new) || !preg_match('/[a-z]/', $new) || !preg_match('/[0-9]/', $new)) {
                $errors['new_password'] = '8 caractères minimum, avec majuscule, minuscule et chiffre';
            } elseif ($new !== $confirm) {
                $errors['confirm_password'] = 'Les deux mots de passe ne correspondent pas';
            } else {
                $model->updatePassword(Auth::id(), $new);
                flash('Mot de passe modifié');
                redirect('profile');
            }
        }
    }
}