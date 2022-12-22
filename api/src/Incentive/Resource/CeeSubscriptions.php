<?php

namespace App\Incentive\Resource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A User Cee subscriptions.
 *
 * @ApiResource(
 *      collectionOperations={
 *          "get"={
 *              "path"="/my_cee_subscriptions",
 *              "swagger_context" = {
 *                  "tags"={"Subscription"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Subscription"},
 *                  "summary"="Not implemented"
 *              }
 *          }
 *      }
 * )
 *
 * @author Olivier Fillol <olivier.fillol@mobicoop.org>
 */
class CeeSubscriptions
{
    public const DEFAULT_ID = '999999999999';

    /**
     * @var int The id of this CEE subscription
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readSubscription"})
     */
    private $id;

    /**
     * @var ShortDistanceSubscription Short distance subscription
     *
     * @Groups({"readSubscription"})
     */
    private $shortDistanceSubscriptions;

    /**
     * @var LongDistanceSubscription Long distance subscriptions
     *
     * @Groups({"readSubscription"})
     */
    private $longDistanceSubscriptions;

    public function __construct($id = null)
    {
        $this->id = $id ? $id : self::DEFAULT_ID;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get short distance subscription.
     *
     * @return ShortDistanceSubscription
     */
    public function getShortDistanceSubscriptions()
    {
        return $this->shortDistanceSubscriptions;
    }

    /**
     * Set short distance subscription.
     *
     * @param ShortDistanceSubscription $shortDistanceSubscription  Short distance subscription
     * @param mixed                     $shortDistanceSubscriptions
     *
     * @return self
     */
    public function setShortDistanceSubscriptions(?array $shortDistanceSubscriptions)
    {
        $this->shortDistanceSubscriptions = $shortDistanceSubscriptions;

        return $this;
    }

    /**
     * Get long distance subscriptions.
     *
     * @return LongDistanceSubscription
     */
    public function getLongDistanceSubscriptions()
    {
        return $this->longDistanceSubscriptions;
    }

    /**
     * Set long distance subscriptions.
     *
     * @param LongDistanceSubscription $longDistanceSubscription  Long distance subscriptions
     * @param mixed                    $longDistanceSubscriptions
     *
     * @return self
     */
    public function setLongDistanceSubscriptions(?array $longDistanceSubscriptions)
    {
        $this->longDistanceSubscriptions = $longDistanceSubscriptions;

        return $this;
    }
}
