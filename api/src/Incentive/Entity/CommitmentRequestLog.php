<?php

namespace App\Incentive\Entity;

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
 * @ORM\DiscriminatorMap({"long_distance" = "LongDistanceLog", "short_distance" = "ShortDistanceLog"})
 */
abstract class CommitmentRequestLog
{
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
     * @var null|string
     *
     * @ORM\Column(type="text", nullable=true, options={"comment":"Content returned by the mobConnect HTTP request."})
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
     * Log creation date.
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $createdDate;

    public function __construct(int $code, $content, array $payload)
    {
        $this->setCode($code);
        $this->setContent($content);
        $this->setPayload($payload);
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
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Set content returned by the mobConnect HTTP request.
     *
     * @param string $content content returned by the mobConnect HTTP request
     */
    public function setContent(?string $content): self
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
     * Get the cee ID.
     */
    protected function getId(): int
    {
        return $this->id;
    }
}
