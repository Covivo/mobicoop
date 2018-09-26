<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Resource;

/**
 * A postal address.
 */
class Address implements Resource
{
    /**
     * @var int The id of this address.
     */
    private $id;
    
    /**
     * @var string|null The iri of this user.
     */
    private $iri;
    
    /**
     * @var string The street address.
     * @Assert\NotBlank
     */
    private $streetAddress;
    
    /**
     * @var string|null The postal code of the address.
     */
    private $postalCode;
    
    /**
     * @var string The locality of the address.
     *
     * @Assert\NotBlank
     */
    private $addressLocality;
    
    /**
     * @var string The country of the address.
     *
     * @Assert\NotBlank
     */
    private $addressCountry;
    
    /**
     * @var string|null The latitude of the address.
     */
    private $latitude;
    
    /**
     * @var string|null The longitude of the address.
     */
    private $longitude;
    
    /**
     * @var int|null The elevation of the address in metres.
     */
    private $elevation;
    
    /**
     * @var UserAddress[] | ArrayCollection An address may have many users.
     */
    private $userAddresses;
    
    public function __construct()
    {
        $this->userAddresses = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getIri()
    {
        return $this->iri;
    }
    
    public function getStreetAddress(): string
    {
        return $this->streetAddress;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function getAddressLocality(): string
    {
        return $this->addressLocality;
    }

    public function getAddressCountry(): string
    {
        return $this->addressCountry;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function getElevation(): ?int
    {
        return $this->elevation;
    }

    public function getUserAddresses()
    {
        return $this->userAddresses;
    }
    
    public function setId(int $id)
    {
        $this->id = $id;
    }
    
    public function setIri($iri)
    {
        $this->iri = $iri;
    }

    public function setStreetAddress(string $streetAddress)
    {
        $this->streetAddress = $streetAddress;
    }

    public function setPostalCode(string $postalCode)
    {
        $this->postalCode = $postalCode;
    }

    public function setAddressLocality(string $addressLocality)
    {
        $this->addressLocality = $addressLocality;
    }

    public function setAddressCountry(string $addressCountry)
    {
        $this->addressCountry = $addressCountry;
    }

    public function setLatitude(?float $latitude)
    {
        $this->latitude = $latitude;
    }

    public function setLongitude(?float $longitude)
    {
        $this->longitude = $longitude;
    }

    public function setElevation(?int $elevation)
    {
        $this->elevation = $elevation;
    }

    public function setUserAddresses(array $userAddresses)
    {
        $this->userAddresses = $userAddresses;
    }
    
    public function addUserAddress(UserAddress $userAddress)
    {
        $userAddress->setAddress($this);
        $this->userAddresses->add($userAddress);
    }
    
    public function removeUserAddress(UserAddress $userAddress)
    {
        $this->userAddresses->removeElement($userAddress);
        $userAddress->setAddress(null);
    }
}
