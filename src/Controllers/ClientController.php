<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Client;

class ClientController extends Controller
{
    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');
        $this->render('clients/index', [
            'title'   => 'Clients',
            'clients' => (new Client())->search($search),
            'search'  => $search,
        ]);
    }

    public function show(): void
    {
        $model = new Client();
        $client = $model->find($this->idParam());
        if (!$client) {
            flash('Client introuvable', 'error');
            redirect('clients');
        }

        $this->render('clients/show', [
            'title'  => $client['prenom_client'] . ' ' . $client['nom_client'],
            'client' => $client,
            'orders' => $model->orders((int) $client['code_client']),
            'stats'  => $model->stats((int) $client['code_client']),
        ]);
    }

    public function create(): void
    {
        $data = ['nom_client' => '', 'prenom_client' => '', 'email' => '', 'telephone' => ''];
        $errors = [];

        if ($this->isPost()) {
            check_csrf();
            $model = new Client();
            foreach ($data as $field => $value) {
                $data[$field] = trim($_POST[$field] ?? '');
            }

            if ($data['nom_client'] === '' || mb_strlen($data['nom_client']) > 60) {
                $errors['nom_client'] = 'Nom obligatoire (60 caractères maximum)';
            }
            if ($data['prenom_client'] === '' || mb_strlen($data['prenom_client']) > 20) {
                $errors['prenom_client'] = 'Prénom obligatoire (20 caractères maximum)';
            }
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Email invalide';
            } elseif ($model->emailExists($data['email'])) {
                $errors['email'] = 'Un client utilise déjà cet email';
            }
            if (!preg_match('/^[0-9 +.]{10,15}$/', $data['telephone'])) {
                $errors['telephone'] = 'Téléphone invalide';
            }

            if (!$errors) {
                $id = $model->create($data, Auth::id());
                flash('Client créé. Il pourra activer son compte sur le site avec son email.');

                // Création rapide depuis la vente en magasin : on y retourne avec le client choisi
                if (isset($_GET['from_sale'])) {
                    redirect('sales/create', ['client' => $id]);
                }
                redirect('clients/show', ['id' => $id]);
            }
        }

        $this->render('clients/form', [
            'title'    => 'Nouveau client',
            'data'     => $data,
            'errors'   => $errors,
            'fromSale' => isset($_GET['from_sale']),
        ]);
    }
}