<?php

namespace App\Incentive\Entity;

abstract class Journey
{
    protected $createdAt;

    public function setCreatedAt(\DateTime $date): self
    {
        $this->createdAt = $date;

        return $this;
    }
}
