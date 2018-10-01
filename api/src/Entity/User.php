<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\PersistentCollection;

/**
 * A user.
 *
 * @ORM\Entity
 * @UniqueEntity("email")
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
 * )
 * @ApiFilter(NumericFilter::class, properties={"id"})
 * @ApiFilter(SearchFilter::class, properties={"email":"exact"})
 * @ApiFilter(OrderFilter::class, properties={"id", "givenName", "familyName", "email", "gender", "nationality", "birthDate"}, arguments={"orderParameterName"="order"})
 */
class User
{
    /**
     * @var int $id The id of this user.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;
    
    /**
     * @var string|null The first name of the user.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write"})
     */
    private $givenName;
    
    /**
     * @var string|null The family name of the user.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write"})
     */
    private $familyName;
    
    /**
     * @var string The email of the user.
     *
     * @Assert\NotBlank
     * @Assert\Email()
     * @ORM\Column(type="string", length=100, unique=true)
     * @Groups({"read","write"})
     */
    private $email;
    
    /**
     * @var string The encoded password of the user.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups("write")
     */
    private $password;
    
    /**
     * @var string|null The gender of the user.
     *
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Groups({"read","write"})
     *
     * @ApiProperty(
     *     attributes={
     *         "swagger_context"={
     *             "type"="string",
     *             "enum"={"female", "male"}
     *         }
     *     }
     * )
     */
    private $gender;
    
    /**
     * @var string|null The nationality of the user.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write"})
     */
    private $nationality;
    
    /**
     * @var \DateTimeInterface|null $birthDate The birth date of the user.
     *
     * @Assert\Date()
     * @ORM\Column(type="date", nullable=true)
     * @Groups({"read","write"})
     *
     * @ApiProperty(
     *     attributes={
     *         "swagger_context"={"type"="string", "format"="date"}
     *     }
     * )
     */
    private $birthDate;
    
    /**
     * @var string|null The telephone number of the user.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write"})
     */
    private $telephone;
    
    /**
     * @var int|null The maximum deviation time (in seconds) as a driver to accept a request proposal.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write"})
     */
    private $maxDeviationTime;
    
    /**
     * @var int|null The maximum deviation distance (in metres) as a driver to accept a request proposal.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write"})
     */
    private $maxDeviationDistance;
    
    /**
     * @var UserAddress[]|null A user may have many names addresses.
     *
     * @ORM\OneToMany(targetEntity="UserAddress", mappedBy="user", cascade={"persist","remove"})
     * @Groups({"read","write"})
     * @MaxDepth(1)
     *
     */
    private $userAddresses;

    /**
     * @var Proposal[]|null The proposals made by this user.
     * 
     * @ORM\OneToMany(targetEntity="App\Entity\Proposal", mappedBy="user")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $proposals;

    /**
     * @var Solicitation[]|null The solicitations made by this user.
     * 
     * @ORM\OneToMany(targetEntity="App\Entity\Solicitation", mappedBy="user")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $solicitations;

    /**
     * @var Solicitation[]|null The solicitations where the user is involved as a driver.
     * 
     * @ORM\OneToMany(targetEntity="App\Entity\Solicitation", mappedBy="userOffer")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $solicitationsOffer;

    /**
     * @var Solicitation[]|null The solicitations where the user is involved as a passenger.
     * 
     * @ORM\OneToMany(targetEntity="App\Entity\Solicitation", mappedBy="userRequest")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $solicitationsRequest;
    
    public function __construct()
    {
        $this->userAddresses = new ArrayCollection();
        $this->proposals = new ArrayCollection();
        $this->solicitations = new ArrayCollection();
        $this->solicitationsOffer = new ArrayCollection();
        $this->solicitationsRequest = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }
    
    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function getMaxDeviationTime(): ?int
    {
        return $this->maxDeviationTime;
    }

    public function getMaxDeviationDistance(): ?int
    {
        return $this->maxDeviationDistance;
    }

    public function getUserAddresses()
    {
        return $this->userAddresses;
    }

    public function setGivenName(?string $givenName)
    {
        $this->givenName = $givenName;
    }

    public function setFamilyName(?string $familyName)
    {
        $this->familyName = $familyName;
    }
    
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    public function setPassword(?string $password)
    {
        $this->password = $password;
    }

    public function setGender(?string $gender)
    {
        $this->gender = $gender;
    }

    public function setNationality(?string $nationality)
    {
        $this->nationality = $nationality;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate)
    {
        $this->birthDate = $birthDate;
    }

    public function setTelephone(?string $telephone)
    {
        $this->telephone = $telephone;
    }

    public function setMaxDeviationTime(?int $maxDeviationTime)
    {
        $this->maxDeviationTime = $maxDeviationTime;
    }

    public function setMaxDeviationDistance(?int $maxDeviationDistance)
    {
        $this->maxDeviationDistance = $maxDeviationDistance;
    }

    public function setUserAddresses(?array $userAddresses)
    {
        $this->userAddresses = $userAddresses;
    }
    
    public function addUserAddress(UserAddress $userAddress)
    {
        $userAddress->setUser($this);
        $this->userAddresses->add($userAddress);
    }
    
    public function removeUserAddress(UserAddress $userAddress)
    {
        $this->userAddresses->removeElement($userAddress);
        $userAddress->setUser(null);
    }

    /**
     * @return Collection|Proposal[]
     */
    public function getProposals(): Collection
    {
        return $this->proposals;
    }

    public function addProposal(Proposal $proposal): self
    {
        if (!$this->proposals->contains($proposal)) {
            $this->proposals[] = $proposal;
            $proposal->setUser($this);
        }

        return $this;
    }

    public function removeProposal(Proposal $proposal): self
    {
        if ($this->proposals->contains($proposal)) {
            $this->proposals->removeElement($proposal);
            // set the owning side to null (unless already changed)
            if ($proposal->getUser() === $this) {
                $proposal->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Solicitation[]
     */
    public function getSolicitations(): Collection
    {
        return $this->solicitations;
    }

    public function addSolicitation(Solicitation $solicitation): self
    {
        if (!$this->solicitations->contains($solicitation)) {
            $this->solicitations[] = $solicitation;
            $solicitation->setUser($this);
        }

        return $this;
    }

    public function removeSolicitation(Solicitation $solicitation): self
    {
        if ($this->solicitations->contains($solicitation)) {
            $this->solicitations->removeElement($solicitation);
            // set the owning side to null (unless already changed)
            if ($solicitation->getUser() === $this) {
                $solicitation->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Solicitation[]
     */
    public function getSolicitationsOffer(): Collection
    {
        return $this->solicitationsOffer;
    }

    public function addSolicitationsOffer(Solicitation $solicitationsOffer): self
    {
        if (!$this->solicitationsOffer->contains($solicitationsOffer)) {
            $this->solicitationsOffer[] = $solicitationsOffer;
            $solicitationsOffer->setUserOffer($this);
        }

        return $this;
    }

    public function removeSolicitationsOffer(Solicitation $solicitationsOffer): self
    {
        if ($this->solicitationsOffer->contains($solicitationsOffer)) {
            $this->solicitationsOffer->removeElement($solicitationsOffer);
            // set the owning side to null (unless already changed)
            if ($solicitationsOffer->getUserOffer() === $this) {
                $solicitationsOffer->setUserOffer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Solicitation[]
     */
    public function getSolicitationsRequest(): Collection
    {
        return $this->solicitationsRequest;
    }

    public function addSolicitationsRequest(Solicitation $solicitationsRequest): self
    {
        if (!$this->solicitationsRequest->contains($solicitationsRequest)) {
            $this->solicitationsRequest[] = $solicitationsRequest;
            $solicitationsRequest->setUserRequest($this);
        }

        return $this;
    }

    public function removeSolicitationsRequest(Solicitation $solicitationsRequest): self
    {
        if ($this->solicitationsRequest->contains($solicitationsRequest)) {
            $this->solicitationsRequest->removeElement($solicitationsRequest);
            // set the owning side to null (unless already changed)
            if ($solicitationsRequest->getUserRequest() === $this) {
                $solicitationsRequest->setUserRequest(null);
            }
        }

        return $this;
    }
}
