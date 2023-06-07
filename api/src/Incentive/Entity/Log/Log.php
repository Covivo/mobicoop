<?php

namespace App\Incentive\Entity\Log;

use Doctrine\ORM\Mapping as ORM;

/**
 * CommitmentRequestLog.
 *
 * @ORM\Entity
 *
 * @ORM\Table(name="mobconnect_commitment_request_log")
 *
 * @ORM\HasLifecycleCallbacks
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 *
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 *
 * @ORM\DiscriminatorMap({"long_distance_subscription" = "LongDistanceSubscriptionLog", "short_distance_subscription" = "ShortDistanceSubscriptionLog"})
 */
abstract class Log
{
    public const TYPE_SUBSCRIPTION = 1;             // Incentive subscription
    public const TYPE_COMMITMENT = 2;               // Ask update - Commitment proof
    public const TYPE_ATTESTATION = 3;              // Ask update - Sworn statement
    public const TYPE_VERIFY = 4;                   // Ask verification

    public const TYPE_TIMESTAMP_SUBSCRIPTION = 5;   // Timestamp for subscription - Step 5 of the mobConnect process
    public const TYPE_TIMESTAMP_COMMITMENT = 6;     // Timestamp for commitment -   Step 9 of the mobConnect process
    public const TYPE_TIMESTAMP_ATTESTATION = 7;    // Timestamp for attestation -  Step 17 of the mobConnect process

    public const ALLOWED_TYPES = [
        self::TYPE_SUBSCRIPTION,
        self::TYPE_ATTESTATION,
        self::TYPE_COMMITMENT,
        self::TYPE_VERIFY,
        self::TYPE_TIMESTAMP_SUBSCRIPTION,
        self::TYPE_TIMESTAMP_COMMITMENT,
        self::TYPE_TIMESTAMP_ATTESTATION,
    ];

    public const VERIFICATION_VALIDATION_ERROR = 403;

    public const ERROR_MESSAGES = [
        self::VERIFICATION_VALIDATION_ERROR => 'The subscription did not pass the test before the verify operation',
    ];

    /**
     * @var int The cee ID
     *
     * @ORM\Column(name="id", type="integer")
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Code returned by the mobConnect HTTP request.
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true, options={"comment":"Code returned by the mobConnect HTTP request."})
     */
    private $code;

    /**
     * Content returned by the mobConnect HTTP request.
     *
     * @ORM\Column(type="json", nullable=true, options={"comment":"Content returned by the mobConnect HTTP request."})
     */
    private $content;

    /**
     * Payload send by the mobConnect HTTP request.
     *
     * @var array
     *
     * @ORM\Column(type="json", nullable=true, options={"comment":"Payload send by the mobConnect HTTP request."})
     */
    private $payload;

    /**
     * The type of log: stage of the process at which it occurs.
     *
     * @var int
     *
     * @ORM\Column(type="integer", options={"comment":"The type of log: stage of the process at which it occurs."})
     */
    private $type;

    /**
     * Log creation date.
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $createdDate;

    public function __construct(int $type, int $code, $content, ?array $payload = [])
    {
        $this->setCode($code);
        $this->setContent($content);
        $this->setPayload($payload);
        $this->setType($type);
    }

    /**
     * Get code returned by the mobConnect HTTP request.
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set code returned by the mobConnect HTTP request.
     *
     * @param int $code code returned by the mobConnect HTTP request
     *
     * @return self
     */
    public function setCode(int $code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get content returned by the mobConnect HTTP request.
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set content returned by the mobConnect HTTP request.
     *
     * @param mixed $content
     */
    public function setContent($content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get payload send by the mobConnect HTTP request.
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * Set payload send by the mobConnect HTTP request.
     *
     * @param array $payload payload returned by the mobConnect HTTP request
     */
    public function setPayload(?array $payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * Get log creation date.
     *
     * @return \DateTimeInterface
     */
    public function getCreatedDate(): \DateTime
    {
        return $this->createdDate;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedDate(): self
    {
        $this->createdDate = new \DateTime('now');

        return $this;
    }

    /**
     * Get the type of log: stage of the process at which it occurs.
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * Set the type of log: stage of the process at which it occurs.
     *
     * @param int $type the type of log: stage of the process at which it occurs
     */
    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the cee ID.
     */
    protected function getId(): int
    {
        return $this->id;
    }
}
