<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;

class OrderController extends Controller
{
    public function index(): void
    {
        // Par défaut : les commandes en ligne
        $status = $_GET['statut'] ?? '';
        $mode = $_GET['mode'] ?? 'web';
        $client = trim($_GET['client'] ?? '');

        $this->render('orders/index', [
            'title'  => 'Commandes',
            'orders' => (new Order())->search($status, $mode, $client),
            'status' => $status,
            'mode'   => $mode,
            'client' => $client,
        ]);
    }

    public function show(): void
    {
        $order = (new Order())->find($this->idParam());
        if (!$order) {
            flash('Commande introuvable', 'error');
            redirect('orders');
        }
        $this->render('orders/show', ['title' => 'Commande n°' . $order['num_commande'], 'order' => $order]);
    }

    public function nextStatus(): void
    {
        $id = $this->idParam();
        if ($this->isPost()) {
            check_csrf();
            if ((new Order())->advanceStatus($id)) {
                flash('Statut mis à jour');
            } else {
                flash('Cette commande ne peut plus changer de statut', 'error');
            }
        }
        redirect('orders/show', ['id' => $id]);
    }
}