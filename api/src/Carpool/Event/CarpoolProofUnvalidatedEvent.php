<?php
namespace App\Carpool\Event;

use App\Carpool\Entity\CarpoolProof;
use Symfony\Contracts\EventDispatcher\Event;

class CarpoolProofUnvalidatedEvent extends Event
{
    public const NAME = 'carpool_proof_unvalidated';

    protected $carpoolProof;

    public function __construct(CarpoolProof $carpoolProof)
    {
        $this->carpoolProof = $carpoolProof;
    }

    public function getCarpoolProof(): CarpoolProof
    {
        return $this->carpoolProof;
    }
}
