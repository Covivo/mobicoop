<?php

namespace App\Import\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Import\Controller\ImportImageRelayController;
use App\RelayPoint\Entity\RelayPoint;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * A relay point imported from an external system.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "import-relay-from-v1"={
 *              "method"="GET",
 *              "path"="/import/images-from-v1/relay",
 *              "controller"=ImportImageRelayController::class,
 *              "read"=false,
 *              "security"="is_granted('import_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Import"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('import_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Import"}
 *              }
 *          },
 *      }
 * )
 */
class RelayPointImport
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var null|RelayPoint relay point imported in the platform
     *
     * @ORM\OneToOne(targetEntity="\App\RelayPoint\Entity\RelayPoint", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $relay;

    /**
     * @var null|string the relay point id in the external system
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Groups({"read","write"})
     */
    private $relayExternalId;

    /**
     * @var int import status
     *
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     * Status for the import : 0 = not imported, 1 = imported
     */
    private $status;

    public function __construct()
    {
        $this->status = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRelay()
    {
        return $this->relay;
    }

    public function setRelay(?RelayPoint $relay): self
    {
        $this->relay = $relay;

        return $this;
    }

    public function getRelayExternalId(): string
    {
        return $this->relayExternalId;
    }

    public function setRelayExternalId(string $relayExternalId): self
    {
        $this->relayExternalId = $relayExternalId;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }
}
