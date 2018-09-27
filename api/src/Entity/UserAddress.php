<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;

/**
 * A user address.
 *
 * @ORM\Entity
 * @ORM\Table(
 *      uniqueConstraints={@ORM\UniqueConstraint(columns={"name", "user_id"})}
 * )
 * @UniqueEntity({"name","user"})
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
 * )
 * @ApiFilter(OrderFilter::class, properties={"id", "name"}, arguments={"orderParameterName"="order"})
 */
class UserAddress
{
    /**
     * @var int The id of this user address.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;
    
    /**
     * @var string $name The name of the address for the user.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=45)
     * @Groups({"read","write"})
     */
    private $name;

    /**
     * @var User The user that uses the address.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userAddresses")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $user;
    
    /**
     * @var Address The address used.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="Address", inversedBy="userAddresses", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $address;
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getName(): string
    {
        return $this->name;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function setAddress(Address $address)
    {
        $this->address = $address;
    }
}
