<?php
namespace Tests\Unit;

use App\Models\Employee;
use App\Models\PasswordReset;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

// Règle de mot de passe et jetons de réinitialisation, sans base de données
class PasswordRulesTest extends TestCase
{
    #[DataProvider('passwords')]
    public function testPasswordStrength(string $password, bool $strong): void
    {
        $this->assertSame($strong, Employee::isStrongPassword($password));
    }

    public static function passwords(): array
    {
        return [
            'compte de test'   => ['CafThe#2026test', true],
            'juste 8'          => ['Abcdefg1', true],
            'trop court'       => ['Abcdef1', false],
            'sans majuscule'   => ['abcdefg1', false],
            'sans minuscule'   => ['ABCDEFG1', false],
            'sans chiffre'     => ['Abcdefgh', false],
            'vide'             => ['', false],
        ];
    }

    public function testGeneratedTokenIs64HexCharacters(): void
    {
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', PasswordReset::generateToken());
    }

    public function testGeneratedTokensAreDifferent(): void
    {
        $this->assertNotSame(PasswordReset::generateToken(), PasswordReset::generateToken());
    }

    public function testTokenHashIsSha256(): void
    {
        $this->assertSame(
            'ba7816bf8f01cfea414140de5dae2223b00361a396177a9cb410ff61f20015ad',
            PasswordReset::hashToken('abc')
        );
    }

    public function testStoredHashIsNotTheToken(): void
    {
        $token = PasswordReset::generateToken();
        $this->assertNotSame($token, PasswordReset::hashToken($token));
        $this->assertSame(64, strlen(PasswordReset::hashToken($token)));
    }

    public function testFreshTokenIsUsable(): void
    {
        $now = strtotime('2026-11-14 10:00:00');
        $reset = ['used_at' => null, 'expires_at' => '2026-11-14 10:59:59'];
        $this->assertTrue(PasswordReset::isUsable($reset, $now));
    }

    public function testExpiredTokenIsNotUsable(): void
    {
        $now = strtotime('2026-11-14 11:00:01');
        $reset = ['used_at' => null, 'expires_at' => '2026-11-14 11:00:00'];
        $this->assertFalse(PasswordReset::isUsable($reset, $now));
    }

    public function testTokenIsNotUsableAtTheExactExpiryTime(): void
    {
        $now = strtotime('2026-11-14 11:00:00');
        $reset = ['used_at' => null, 'expires_at' => '2026-11-14 11:00:00'];
        $this->assertFalse(PasswordReset::isUsable($reset, $now));
    }

    public function testUsedTokenIsNotUsable(): void
    {
        $now = strtotime('2026-11-14 10:00:00');
        $reset = ['used_at' => '2026-11-14 09:30:00', 'expires_at' => '2026-11-14 10:30:00'];
        $this->assertFalse(PasswordReset::isUsable($reset, $now));
    }

    public function testTokenLifetimeIsOneHour(): void
    {
        $this->assertSame(3600, PasswordReset::LIFETIME);
    }
}
