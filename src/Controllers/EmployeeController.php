<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Employee;
use PDOException;

// Gestion des comptes vendeurs : réservée à l'administrateur
class EmployeeController extends Controller
{
    public function index(): void
    {
        $this->render('employees/index', ['title' => 'Vendeurs', 'employees' => (new Employee())->all()]);
    }

    public function create(): void
    {
        $this->form(null);
    }

    public function edit(): void
    {
        $employee = (new Employee())->find($this->idParam());
        if (!$employee) {
            flash('Compte introuvable', 'error');
            redirect('employees');
        }
        $this->form($employee);
    }

    private function form(?array $employee): void
    {
        $data = $employee ?? [
            'nom_employe' => '', 'prenom_employe' => '', 'email_employe' => '', 'tel_employe' => '', 'role' => Auth::SELLER,
        ];
        $errors = [];

        if ($this->isPost()) {
            check_csrf();
            foreach (['nom_employe', 'prenom_employe', 'email_employe', 'tel_employe', 'role'] as $field) {
                $data[$field] = trim($_POST[$field] ?? '');
            }
            $password = $_POST['password'] ?? '';

            if ($data['nom_employe'] === '' || $data['prenom_employe'] === '') {
                $errors['nom_employe'] = 'Nom et prénom obligatoires';
            }
            if (!filter_var($data['email_employe'], FILTER_VALIDATE_EMAIL)) {
                $errors['email_employe'] = 'Email invalide';
            }
            if (!in_array($data['role'], [Auth::SELLER, Auth::ADMIN], true)) {
                $errors['role'] = 'Rôle invalide';
            }
            // Mot de passe obligatoire à la création, facultatif en modification
            if ((!$employee || $password !== '') && !$this->isStrongPassword($password)) {
                $errors['password'] = '8 caractères minimum, avec majuscule, minuscule et chiffre';
            }

            if (!$errors) {
                $model = new Employee();
                try {
                    if ($employee) {
                        $id = (int) $employee['num_employe'];
                        $model->update($id, $data);
                        if ($password !== '') {
                            $model->updatePassword($id, $password);
                        }
                        flash('Compte modifié');
                    } else {
                        $model->create($data, $password);
                        flash('Compte créé');
                    }
                    redirect('employees');
                } catch (PDOException $e) {
                    // 23000 = violation de contrainte : email unique
                    if ($e->getCode() !== '23000') {
                        throw $e;
                    }
                    $errors['email_employe'] = 'Cet email est déjà utilisé';
                }
            }
        }

        $this->render('employees/form', [
            'title'    => $employee ? 'Modifier un compte' : 'Nouveau compte',
            'employee' => $employee,
            'data'     => $data,
            'errors'   => $errors,
        ]);
    }

    public function delete(): void
    {
        $id = $this->idParam();
        if ($this->isPost()) {
            check_csrf();
            if ($id === Auth::id()) {
                flash('Vous ne pouvez pas supprimer votre propre compte', 'error');
            } else {
                (new Employee())->delete($id);
                flash('Compte supprimé');
            }
        }
        redirect('employees');
    }

    private function isStrongPassword(string $password): bool
    {
        return strlen($password) >= 8
            && preg_match('/[A-Z]/', $password)
            && preg_match('/[a-z]/', $password)
            && preg_match('/[0-9]/', $password);
    }
}