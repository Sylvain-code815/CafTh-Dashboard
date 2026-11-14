<?php
namespace App\Core;

// Envoi des emails (lien de réinitialisation du mot de passe, CDC 5.1)
// transport 'mail' : fonction mail() de PHP (serveur Plesk)
// transport 'log'  : l'email est ajouté à storage/mails.log au lieu d'être envoyé (développement)
class Mailer
{
    private string $transport;
    private string $from;
    private string $logFile;

    public function __construct(array $config = [])
    {
        // Valeurs par défaut si la clé 'mail' manque dans config.php
        $host = preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? 'localhost'); // sans le port
        $this->transport = $config['transport'] ?? 'mail';
        $this->from = $config['from'] ?? "no-reply@$host";
        $this->logFile = $config['log_file'] ?? ROOT . '/storage/mails.log';
    }

    public function send(string $to, string $subject, string $body): bool
    {
        if ($this->transport === 'log') {
            return $this->log($to, $subject, $body);
        }

        $headers = [
            'From'                      => $this->from,
            'MIME-Version'              => '1.0',
            'Content-Type'              => 'text/plain; charset=UTF-8',
            'Content-Transfer-Encoding' => '8bit',
        ];
        // Objet encodé pour les accents ; -f fixe l'expéditeur technique (meilleure délivrabilité)
        return mail($to, mb_encode_mimeheader($subject, 'UTF-8'), $body, $headers, '-f' . $this->from);
    }

    private function log(string $to, string $subject, string $body): bool
    {
        $dir = dirname($this->logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $entry = '=== ' . date('Y-m-d H:i:s') . "\nDe : {$this->from}\nÀ : $to\nObjet : $subject\n\n$body\n\n";
        return file_put_contents($this->logFile, $entry, FILE_APPEND | LOCK_EX) !== false;
    }
}
