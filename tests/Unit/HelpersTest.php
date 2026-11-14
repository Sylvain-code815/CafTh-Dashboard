<?php
namespace Tests\Unit;

use App\Core\Config;
use App\Core\Mailer;
use App\Models\Order;
use PHPUnit\Framework\TestCase;

// Fonctions utilitaires, configuration et envoi d'email en mode « log »
class HelpersTest extends TestCase
{
    private string $logFile;

    protected function setUp(): void
    {
        $this->logFile = sys_get_temp_dir() . '/cafthe-mails-' . uniqid() . '.log';
    }

    protected function tearDown(): void
    {
        Config::init([]);
        unset($_SERVER['HTTP_HOST']);
        if (is_file($this->logFile)) {
            unlink($this->logFile);
        }
    }

    public function testEscapeProtectsAgainstXss(): void
    {
        $this->assertSame('&lt;script&gt;alert(&#039;x&#039;)&lt;/script&gt;', e("<script>alert('x')</script>"));
    }

    public function testPriceFormat(): void
    {
        $this->assertSame('1 234,50 €', price(1234.5));
        $this->assertSame('0,00 €', price(null));
    }

    public function testUrlBuildsAnInternalRoute(): void
    {
        $this->assertSame('index.php?route=orders%2Fshow&id=3', url('orders/show', ['id' => 3]));
    }

    public function testAbsoluteUrlUsesAppUrl(): void
    {
        Config::init(['app_url' => 'https://dashboard.example.com/']);
        $this->assertSame(
            'https://dashboard.example.com/index.php?route=reset-password&token=abc',
            absolute_url('reset-password', ['token' => 'abc'])
        );
    }

    public function testAbsoluteUrlFallsBackToTheRequestHostInHttps(): void
    {
        // Ancien config.php sans app_url : pas d'erreur
        Config::init([]);
        $_SERVER['HTTP_HOST'] = 'dashboard.example.com';
        $this->assertSame('https://dashboard.example.com/index.php?route=login', absolute_url('login'));
    }

    public function testConfigReturnsTheDefaultForAMissingKey(): void
    {
        Config::init(['session_timeout' => 1800]);
        $this->assertSame(1800, Config::get('session_timeout'));
        $this->assertSame([], Config::get('mail', []));
    }

    public function testLogTransportWritesTheEmailInsteadOfSendingIt(): void
    {
        $mailer = new Mailer(['transport' => 'log', 'from' => 'no-reply@example.com', 'log_file' => $this->logFile]);

        $this->assertTrue($mailer->send('paul@example.com', 'Objet accentué', "Bonjour,\nlien : https://x/y"));

        $log = file_get_contents($this->logFile);
        $this->assertStringContainsString('De : no-reply@example.com', $log);
        $this->assertStringContainsString('À : paul@example.com', $log);
        $this->assertStringContainsString('Objet : Objet accentué', $log);
        $this->assertStringContainsString('lien : https://x/y', $log);
    }

    public function testMailerBuildsADefaultSenderFromTheHostWithoutPort(): void
    {
        $_SERVER['HTTP_HOST'] = 'dashboard.example.com:8443';
        (new Mailer(['transport' => 'log', 'log_file' => $this->logFile]))->send('a@example.com', 'Test', 'Test');

        $this->assertStringContainsString('De : no-reply@dashboard.example.com', file_get_contents($this->logFile));
    }

    public function testOrderStatusFlow(): void
    {
        $this->assertSame(['pending', 'preparing', 'shipped', 'delivered'], Order::STATUS_FLOW);
        $this->assertSame(Order::STATUS_FLOW, array_keys(ORDER_STATUS)); // chaque statut a un libellé
    }
}
