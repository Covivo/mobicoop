<?php

namespace App\User\Entity;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class UserIdentityStatusTest extends TestCase
{
    /**
     * @test
     */
    public function noProofGivesStatusNone()
    {
        $user = new User();
        $user->computeIdentityStatus();

        $this->assertSame(IdentityProof::STATUS_NONE, $user->getIdentityStatus());
    }

    /**
     * @test
     */
    public function newProofOnUserWithExistingProofIsTheLatest()
    {
        $user = new User();
        $this->addPersistedProof($user, 12, IdentityProof::STATUS_CANCELED);

        $newProof = new IdentityProof();
        $newProof->setUser($user);
        $newProof->setStatus(IdentityProof::STATUS_PENDING);

        $this->assertSame(IdentityProof::STATUS_PENDING, $user->getIdentityStatus());
    }

    /**
     * @test
     */
    public function externalProofAcceptedOnUserWithCanceledProof()
    {
        $user = new User();
        $this->addPersistedProof($user, 12, IdentityProof::STATUS_CANCELED);

        $externalProof = new IdentityProof();
        $externalProof->setUser($user);
        $externalProof->setStatus(IdentityProof::STATUS_ACCEPTED);

        $this->assertSame(IdentityProof::STATUS_ACCEPTED, $user->getIdentityStatus());
    }

    /**
     * @test
     */
    public function highestIdWinsAmongPersistedProofs()
    {
        $user = new User();
        $this->addPersistedProof($user, 20, IdentityProof::STATUS_REFUSED);
        $this->addPersistedProof($user, 10, IdentityProof::STATUS_ACCEPTED);

        $user->computeIdentityStatus();

        $this->assertSame(IdentityProof::STATUS_REFUSED, $user->getIdentityStatus());
    }

    private function addPersistedProof(User $user, int $id, int $status): IdentityProof
    {
        $proof = new IdentityProof();
        $idProperty = new \ReflectionProperty(IdentityProof::class, 'id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($proof, $id);
        $proof->setUser($user);
        $proof->setStatus($status);

        return $proof;
    }
}
