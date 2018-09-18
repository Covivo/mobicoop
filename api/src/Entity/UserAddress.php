<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * A user address.
 *
 * @ORM\Entity
 * @ApiResource
 */
Class UserAddress
{
    /**
     * @var string The name of the address for the user.
     *
     * @ORM\Column(type="string", length=45)
     */
    private $name;

    /**
     * @var User The user that uses the address.
     * 
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userAddresses")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
    
    /**
     * @var Address The address used.
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Address", inversedBy="userAddresses")
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id")
     */
    private $address;
    
    public function getName ()
    {
        return $this->name;
    }

    public function getUser ()
    {
        return $this->user;
    }

    public function getAddress ()
    {
        return $this->address;
    }

    public function setName ($name)
    {
        $this->name = $name;
    }

    public function setUser ($user)
    {
        $this->user = $user;
    }

    public function setAddress ($address)
    {
        $this->address = $address;
    }
    
}

