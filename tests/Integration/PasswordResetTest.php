<?php
namespace Tests\Integration;

use App\Models\Employee;
use App\Models\PasswordReset;
use Tests\Support\IntegrationTestCase;

class PasswordResetTest extends IntegrationTestCase
{
    private const PAUL = 2;

    public function testCreatedTokenIsValidAndOnlyItsHashIsStored(): void
    {
        $resets = new PasswordReset();
        $token = $resets->create(PasswordReset::EMPLOYEE, self::PAUL);

        $reset = $resets->findUsable(PasswordReset::EMPLOYEE, $token);
        $this->assertNotNull($reset);
        $this->assertSame(self::PAUL, (int) $reset['account_id']);
        $this->assertSame(hash('sha256', $token), $reset['token_hash']);
        $this->assertSame(0, (int) $this->value('SELECT COUNT(*) FROM password_reset WHERE token_hash = ?', [$token]));
    }

    public function testTokenExpiresAfterOneHour(): void
    {
        $resets = new PasswordReset();
        $token = $resets->create(PasswordReset::EMPLOYEE, self::PAUL);

        $minutes = (int) $this->value(
            'SELECT TIMESTAMPDIFF(MINUTE, NOW(), expires_at) FROM password_reset WHERE token_hash = ?',
            [hash('sha256', $token)]
        );
        $this->assertContains($minutes, [59, 60]);

        $this->db->prepare('UPDATE password_reset SET expires_at = NOW() - INTERVAL 1 SECOND WHERE token_hash = ?')
            ->execute([hash('sha256', $token)]);
        $this->assertNull($resets->findUsable(PasswordReset::EMPLOYEE, $token));
    }

    public function testTokenCanBeUsedOnlyOnce(): void
    {
        $resets = new PasswordReset();
        $token = $resets->create(PasswordReset::EMPLOYEE, self::PAUL);
        $reset = $resets->findUsable(PasswordReset::EMPLOYEE, $token);

        $this->assertTrue($resets->markUsed((int) $reset['id']));
        $this->assertFalse($resets->markUsed((int) $reset['id'])); // second envoi du formulaire
        $this->assertNull($resets->findUsable(PasswordReset::EMPLOYEE, $token));
    }

    public function testANewTokenInvalidatesThePreviousOne(): void
    {
        $resets = new PasswordReset();
        $first = $resets->create(PasswordReset::EMPLOYEE, self::PAUL);
        $second = $resets->create(PasswordReset::EMPLOYEE, self::PAUL);

        $this->assertNull($resets->findUsable(PasswordReset::EMPLOYEE, $first));
        $this->assertNotNull($resets->findUsable(PasswordReset::EMPLOYEE, $second));
    }

    public function testUnknownTokenOrOtherAccountTypeIsRefused(): void
    {
        $resets = new PasswordReset();
        $token = $resets->create(PasswordReset::EMPLOYEE, self::PAUL);

        $this->assertNull($resets->findUsable(PasswordReset::EMPLOYEE, PasswordReset::generateToken()));
        $this->assertNull($resets->findUsable('client', $token)); // jeton d'employé inutilisable pour un client
    }

    public function testRequestsAreLimitedPerAccount(): void
    {
        $resets = new PasswordReset();
        for ($i = 0; $i < PasswordReset::MAX_PER_ACCOUNT; $i++) {
            $this->assertFalse($resets->tooManyRequests(PasswordReset::EMPLOYEE, self::PAUL));
            $resets->create(PasswordReset::EMPLOYEE, self::PAUL);
        }
        $this->assertTrue($resets->tooManyRequests(PasswordReset::EMPLOYEE, self::PAUL));
        $this->assertFalse($resets->tooManyRequests(PasswordReset::EMPLOYEE, 1)); // autre compte
    }

    public function testNewPasswordIsHashedAndReplacesTheOldOne(): void
    {
        $employees = new Employee();
        $employees->updatePassword(self::PAUL, 'NouveauMdp2026');

        $hash = $this->value('SELECT mdp_employe FROM employe WHERE num_employe = ?', [self::PAUL]);
        $this->assertNotSame('NouveauMdp2026', $hash);
        $this->assertTrue($employees->checkPassword(self::PAUL, 'NouveauMdp2026'));
        $this->assertFalse($employees->checkPassword(self::PAUL, 'CafThe#2026test'));
    }
}
