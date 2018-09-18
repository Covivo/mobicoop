<?php 

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * A postal address.
 *
 * @ORM\Entity
 * @ApiResource
 */
Class Address
{
    /**
     * @var int The id of this address.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @var string The street address.
     *
     * @ORM\Column(type="string", length=255)
     */
    private $streetAddress;
    
    /**
     * @var string|null The postal code of the address.
     *
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $postalCode;
    
    /**
     * @var string The locality of the address.
     *
     * @ORM\Column(type="string", length=100)
     */
    private $addressLocality;
    
    /**
     * @var string The country of the address.
     *
     * @ORM\Column(type="string", length=100)
     */
    private $addressCountry;
    
    /**
     * @var string The latitude of the address.
     * 
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     */
    private $latitude;
    
    /**
     * @var string The longitude of the address.
     * 
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     */
    private $longitude;
    
    /**
     * @var int|null The elevation of the address in metres.
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $elevation;
    
    /**
     * @var UserAddress[] An address may have many users.
     *
     * @ORM\OneToMany(targetEntity="UserAddress", mappedBy="user")
     */
    private $userAddresses;
    
    public function __construct() {
        $this->userAddresses = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getStreetAddress ()
    {
        return $this->streetAddress;
    }

    public function getPostalCode ()
    {
        return $this->postalCode;
    }

    public function getAddressLocality ()
    {
        return $this->addressLocality;
    }

    public function getAddressCountry ()
    {
        return $this->addressCountry;
    }

    public function getLatitude ()
    {
        return $this->latitude;
    }

    public function getLongitude ()
    {
        return $this->longitude;
    }

    public function getElevation ()
    {
        return $this->elevation;
    }

    public function getUserAddresses ()
    {
        return $this->userAddresses;
    }

    public function setStreetAddress ($streetAddress)
    {
        $this->streetAddress = $streetAddress;
    }

    public function setPostalCode ($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    public function setAddressLocality ($addressLocality)
    {
        $this->addressLocality = $addressLocality;
    }

    public function setAddressCountry ($addressCountry)
    {
        $this->addressCountry = $addressCountry;
    }

    public function setLatitude ($latitude)
    {
        $this->latitude = $latitude;
    }

    public function setLongitude ($longitude)
    {
        $this->longitude = $longitude;
    }

    public function setElevation ($elevation)
    {
        $this->elevation = $elevation;
    }

    public function setUserAddresses ($userAddresses)
    {
        $this->userAddresses = $userAddresses;
    }
    
}