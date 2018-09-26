<?php

namespace App\Entity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Resource;

/**
 * A user address.
 */
Class UserAddress implements Resource
{
    /**
     * @var int The id of this user address.
     */
    private $id;
    
    /**
     * @var string|null The iri of this user address.
     */
    private $iri;
    
    /**
     * @var string The name of the address for the user.
     *
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var User The user that uses the address.
     * 
     * @Assert\NotBlank
     */
    private $user;
    
    /**
     * @var Address The address used.
     *
     * @Assert\NotBlank
     */
    private $address;
    
    public function getId(): int
    {
        return $this->id;
    }
    
    public function getIri ()
    {
        return $this->iri;
    }
    
    public function getName (): ?string
    {
        return $this->name;
    }

    public function getUser (): User
    {
        return $this->user;
    }

    public function getAddress (): Address
    {
        return $this->address;
    }
    
    public function setId (int $id)
    {
        $this->id = $id;
    }
    
    public function setIri ($iri)
    {
        $this->iri = $iri;
    }

    public function setName (string $name)
    {
        $this->name = $name;
    }

    public function setUser (User $user)
    {
        $this->user = $user;
    }

    public function setAddress (Address $address)
    {
        $this->address = $address;
    }
    
}

