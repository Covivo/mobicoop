<?php

namespace App\DataProvider\Entity\MobConnect\Response;

interface MobConnectResponseInterface
{
    public function getCode(): ?int;

    public function getContent();

    public function getPayload(): ?array;
}
