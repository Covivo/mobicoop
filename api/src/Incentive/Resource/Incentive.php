<?php

namespace App\Incentive\Resource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Incentive\Controller\IncentivesController;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * User eligibility for subscription to EEC aids.
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={
 *              "groups"={"eecIncentive"},
 *              "enable_max_depth"=true
 *          }
 *      },
 *      collectionOperations={
 *          "get"={
 *              "method"="GET",
 *              "path"="/incentives",
 *              "controller"=IncentivesController::class,
 *              "normalization_context"={
 *                  "groups"={"eecIncentive"},
 *                  "skip_null_values"=false
 *              },
 *              "swagger_context"={
 *                  "tags"={"Incentives"}
 *              }
 *          }
 *      }
 * )
 *
 * @author Olivier Fillol <olivier.fillol@mobicoop.org>
 */
class Incentive
{
    /**
     * @var string
     *
     * @ApiProperty(identifier=true)
     *
     * @Groups({"eecIncentive"})
     */
    private $id;

    /**
     * @var null|string
     *
     * @Groups({"eecIncentive"})
     */
    private $title;

    /**
     * @var null|string
     *
     * @Groups({"eecIncentive"})
     */
    private $type;

    /**
     * @var null|string
     *
     * @Groups({"eecIncentive"})
     */
    private $subscriptionLink;

    /**
     * @var null|string
     *
     * @Groups({"eecIncentive"})
     */
    private $description;

    public function __construct(
        string $id,
        string $type,
        string $title,
        string $description,
        string $subscriptionLink = null
    ) {
        $this->setId($id);
        $this->setType($type);
        $this->setTitle($title);
        $this->setDescription($description);
        $this->setSubscriptionLink($subscriptionLink);
    }

    /**
     * Get the value of id.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Set the value of id.
     */
    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of type.
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Set the value of type.
     */
    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the value of title.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set the value of title.
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the value of description.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set the value of description.
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of subscriptionLink.
     */
    public function getSubscriptionLink(): ?string
    {
        return $this->subscriptionLink;
    }

    /**
     * Set the value of subscriptionLink.
     */
    public function setSubscriptionLink(?string $subscriptionLink): self
    {
        $this->subscriptionLink = $subscriptionLink;

        return $this;
    }
}
