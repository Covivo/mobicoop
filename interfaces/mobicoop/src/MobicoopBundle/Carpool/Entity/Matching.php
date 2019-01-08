<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

namespace Mobicoop\Bundle\MobicoopBundle\Carpool\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Carpooling : matching between an offer and a request.
 */
class Matching
{
    /**
     * @var int The id of this matching.
     */
    private $id;
    
    /**
     * @var string|null The iri of this user.
     */
    private $iri;

    /**
     * @var Proposal The offer proposal.
     *
     * @Assert\NotBlank
     */
    private $proposalOffer;

    /**
     * @var Proposal The request proposal.
     *
     * @Assert\NotBlank
     */
    private $proposalRequest;

    /**
     * @var Criteria The criteria applied to this matching.
     *
     * @Assert\NotBlank
     */
    private $criteria;

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(int $id)
    {
        $this->id = $id;
    }
    
    public function getIri()
    {
        return $this->iri;
    }
    
    public function setIri($iri)
    {
        $this->iri = $iri;
    }

    public function getProposalOffer(): ?Proposal
    {
        return $this->proposalOffer;
    }
    
    public function setProposalOffer(?Proposal $proposalOffer): self
    {
        $this->proposalOffer = $proposalOffer;
        
        return $this;
    }
    
    public function getProposalRequest(): ?Proposal
    {
        return $this->proposalRequest;
    }
    
    public function setProposalRequest(?Proposal $proposalRequest): self
    {
        $this->proposalRequest = $proposalRequest;
        
        return $this;
    }
    
    public function getCriteria(): ?Criteria
    {
        return $this->criteria;
    }
    
    public function setCriteria(Criteria $criteria): self
    {
        $this->criteria = $criteria;
        
        return $this;
    }
}
