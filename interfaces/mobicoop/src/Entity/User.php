<?php 

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A user.
 */
class User
{
    
    /**
     * @var int The id of this user.
     */
    private $id;
    
    /**
     * @var string|null The iri of this user.
     */
    private $iri;
    
    /**
     * @var string|null The first name of the user.
     */
    private $givenName;
    
    /**
     * @var string|null The family name of the user.
     */
    private $familyName;
    
    /**
     * @var string The email of the user.
     *
     * @Assert\NotBlank
     * @Assert\Email()
     */
    private $email;
    
    /**
     * @var string|null The encoded password of the user.
     */
    private $password;
    
    /**
     * @var string|null The gender of the user.
     */
    private $gender;
    
    /**
     * @var string|null The nationality of the user.
     */
    private $nationality;
    
    /**
     * @var \DateTimeInterface|null The birth date of the user.
     *
     * @Assert\Date()
     */
    private $birthDate;
    
    /**
     * @var string|null The telephone number of the user.
     */
    private $telephone;
    
    /**
     * @var int|null The maximum deviation time (in seconds) as a driver to accept a request proposal.
     */
    private $maxDeviationTime;
    
    /**
     * @var int|null The maximum deviation distance (in metres) as a driver to accept a request proposal.
     */
    private $maxDeviationDistance;
    
    /**
     * @var UserAddress[]|null A user may have many names addresses.
     */
    private $userAddresses;
        
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getIri ()
    {
        return $this->iri;
    }

    public function getGivenName (): ?string
    {
        return $this->givenName;
    }

    public function getFamilyName (): ?string
    {
        return $this->familyName;
    }
    
    public function getEmail (): string
    {
        return $this->email;
    }

    public function getPassword (): ?string
    {
        return $this->password;
    }

    public function getGender (): ?string
    {
        return $this->gender;
    }

    public function getNationality (): ?string
    {
        return $this->nationality;
    }

    public function getBirthDate (): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function getTelephone (): ?string
    {
        return $this->telephone;
    }

    public function getMaxDeviationTime (): ?int
    {
        return $this->maxDeviationTime;
    }

    public function getMaxDeviationDistance (): ?int
    {
        return $this->maxDeviationDistance;
    }

    public function getUserAddresses ()
    {
        return $this->userAddresses;
    }
    
    public function setId ($id)
    {
        $this->id = $id;
    }
    
    public function setIri ($iri)
    {
        $this->iri = $iri;
    }
    
    public function setGivenName (?string $givenName)
    {
        $this->givenName = $givenName;
    }

    public function setFamilyName (?string $familyName)
    {
        $this->familyName = $familyName;
    }
    
    public function setEmail (?string $email)
    {
        $this->email = $email;
    }

    public function setPassword (?string $password)
    {
        $this->password = $password;
    }

    public function setGender (?string $gender)
    {
        $this->gender = $gender;
    }

    public function setNationality (?string $nationality)
    {
        $this->nationality = $nationality;
    }

    public function setBirthDate (?\DateTimeInterface $birthDate)
    {
        $this->birthDate = $birthDate;
    }

    public function setTelephone (?string $telephone)
    {
        $this->telephone = $telephone;
    }

    public function setMaxDeviationTime (?int $maxDeviationTime)
    {
        $this->maxDeviationTime = $maxDeviationTime;
    }

    public function setMaxDeviationDistance (?int $maxDeviationDistance)
    {
        $this->maxDeviationDistance = $maxDeviationDistance;
    }

    public function setUserAddresses (?array $userAddresses)
    {
        $this->userAddresses = $userAddresses;
    }
    
}