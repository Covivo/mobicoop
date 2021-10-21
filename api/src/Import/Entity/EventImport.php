<?php

namespace App\Import\Entity;

use App\Event\Entity\Event;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use App\Import\Controller\ImportImageEventController;

/**
 * An event imported from an external system.
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
 *          "import-event-from-v1"={
 *              "method"="GET",
 *              "path"="/import/images-from-v1/event",
 *              "controller"=ImportImageEventController::class,
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
class EventImport
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var event|null Event imported in the platform.
     *
     * @ORM\OneToOne(targetEntity="\App\Event\Entity\Event", cascade={"persist"})
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $event;

    /**
     * @var string|null The event id in the external system.
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Groups({"read","write"})
     */
    private $eventExternalId;

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

    public function getEvent()
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getEventExternalId(): string
    {
        return $this->eventExternalId;
    }

    public function setEventExternalId(string $eventExternalId): self
    {
        $this->eventExternalId = $eventExternalId;

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
