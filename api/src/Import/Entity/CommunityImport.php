<?php

namespace App\Import\Entity;

use App\Community\Entity\Community;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use App\Import\Controller\ImportImageCommunityController;

/**
 * A community imported from an external system.
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
 *          "import-community-from-v1"={
 *              "method"="GET",
 *              "path"="/import/images-from-v1/community",
 *              "controller"=ImportImageCommunityController::class,
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
 *
 */
class CommunityImport
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Community|null Community imported in the platform.
     *
     * @ORM\OneToOne(targetEntity="\App\Community\Entity\Community", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $community;

    /**
     * @var string|null The community id in the external system.
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Groups({"read","write"})
     */
    private $communityExternalId;

    /**
     * @var int Import status.
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

    public function getCommunity(): Community
    {
        return $this->community;
    }

    public function setCommunity(?Community $community): self
    {
        $this->community = $community;

        return $this;
    }

    public function getCommunityExternalId(): string
    {
        return $this->communityExternalId;
    }

    public function setCommunityExternalId(string $communityExternalId): self
    {
        $this->communityExternalId = $communityExternalId;

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
