<?php 

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * A user.
 *
 * @ORM\Entity
 * @ApiResource
 */
class User
{
    /**
     * @var int The id of this user.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @var string|null The first name of the user.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $givenName;
    
    /**
     * @var string|null The family name of the user.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $familyName;
    
    /**
     * @var string The encoded password of the user.
     *
     * @ORM\Column(type="string", length=100)
     */
    private $password;
    
    /**
     * @var int|null The gender of the user.
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $gender;
    
    /**
     * @var string|null The nationality of the user.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $nationality;
    
    /**
     * @var \DateTimeInterface|null The birth date of the user.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $birthDate;
    
    /**
     * @var string|null The telephone number of the user.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $telephone;
    
    /**
     * @var int|null The maximum deviation time (in seconds) as a driver to accept a request proposal.
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxDeviationTime;
    
    /**
     * @var int|null The maximum deviation distance (in metres) as a driver to accept a request proposal.
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxDeviationDistance;
    
    /**
     * @var UserAddress[] A user may have many names addresses.
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
    
    public function getGivenName ()
    {
        return $this->givenName;
    }

    public function getFamilyName ()
    {
        return $this->familyName;
    }

    public function getPassword ()
    {
        return $this->password;
    }

    public function getGender ()
    {
        return $this->gender;
    }

    public function getNationality ()
    {
        return $this->nationality;
    }

    public function getBirthDate ()
    {
        return $this->birthDate;
    }

    public function getTelephone ()
    {
        return $this->telephone;
    }

    public function getMaxDeviationTime ()
    {
        return $this->maxDeviationTime;
    }

    public function getMaxDeviationDistance ()
    {
        return $this->maxDeviationDistance;
    }

    public function getUserAddresses ()
    {
        return $this->userAddresses;
    }

    public function setGivenName ($givenName)
    {
        $this->givenName = $givenName;
    }

    public function setFamilyName ($familyName)
    {
        $this->familyName = $familyName;
    }

    public function setPassword ($password)
    {
        $this->password = $password;
    }

    public function setGender ($gender)
    {
        $this->gender = $gender;
    }

    public function setNationality ($nationality)
    {
        $this->nationality = $nationality;
    }

    public function setBirthDate ($birthDate)
    {
        $this->birthDate = $birthDate;
    }

    public function setTelephone ($telephone)
    {
        $this->telephone = $telephone;
    }

    public function setMaxDeviationTime ($maxDeviationTime)
    {
        $this->maxDeviationTime = $maxDeviationTime;
    }

    public function setMaxDeviationDistance ($maxDeviationDistance)
    {
        $this->maxDeviationDistance = $maxDeviationDistance;
    }

    public function setUserAddresses ($userAddresses)
    {
        $this->userAddresses = $userAddresses;
    }
    
}