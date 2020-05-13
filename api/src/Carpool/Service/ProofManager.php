<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 **************************/

namespace App\Carpool\Service;

use App\Carpool\Entity\CarpoolProof;
use App\DataProvider\Entity\CarpoolProofGouvProvider;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Carpool proof manager service, used to send proofs to a register.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class ProofManager
{
    private $entityManager;
    private $logger;
    private $provider;
    
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ProposalManager $proposalManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        string $provider,
        string $uri,
        string $token
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;

        switch ($provider) {
            case 'BetaGouv':
            default:
                $this->provider = new CarpoolProofGouvProvider($uri,$token);
                break;
        }
    }



    /************
     *  COMMON  *
     ************/

    /**
     * Send the current proofs : 
     * - persisted carpoolProofs with status = 0 (dynamic or punctual)
     * - unpersisted carpoolProofs (regular)
     *
     * @return void
     */
    public function sendProofs() 
    {
        $proofs = [];
        // search the dynamic or punctual proofs
        
        // search the regular proofs

        // send the proofs
        foreach ($proofs as $proof) {
            /**
             * @var CarpoolProof $proof
             */
            $this->provider->postCollection($proof);
        }
    }

    /****************
     *  CLASSIC AD  *
     ****************/



    /****************
     *  DYNAMIC AD  *
     ****************/

}