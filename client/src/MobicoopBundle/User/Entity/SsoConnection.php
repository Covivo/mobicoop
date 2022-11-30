<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 */

namespace Mobicoop\Bundle\MobicoopBundle\User\Entity;

/**
 * A SSO Connection.
 */
class SsoConnection implements \JsonSerializable
{
    /**
     * @var string The SSO service
     *
     * @Groups({"post"})
     */
    private $service;

    /**
     * @var string The SSO provider internal name
     *
     * @Groups({"readSSOConnection"})
     */
    private $ssoProvider;

    /**
     * @var string The uri of the SSO login form
     */
    private $uri;

    /**
     * @var null|string The SSO service icon for the button
     */
    private $buttonIcon;

    /**
     * @var null|string The SSO service picto for the text button
     */
    private $picto;

    /**
     * @var null|bool true : use the Button icon, false use the picto
     */
    private $useButtonIcon;

    /**
     * @var null|bool true : This SSO provider allow deletation of account only on its side
     */
    private $externalAccountDeletion;

    public function getService(): string
    {
        return $this->service;
    }

    public function setService(string $service): self
    {
        $this->service = $service;

        return $this;
    }

    public function getSsoProvider(): ?string
    {
        return $this->ssoProvider;
    }

    public function setSsoProvider(?string $ssoProvider): self
    {
        $this->ssoProvider = $ssoProvider;

        return $this;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function setUri(string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    public function getPicto(): ?string
    {
        return $this->picto;
    }

    public function setPicto(?string $picto): self
    {
        $this->picto = $picto;

        return $this;
    }

    public function getButtonIcon(): ?string
    {
        return $this->buttonIcon;
    }

    public function setButtonIcon(?string $buttonIcon): self
    {
        $this->buttonIcon = $buttonIcon;

        return $this;
    }

    public function hasUseButtonIcon(): ?bool
    {
        return $this->useButtonIcon;
    }

    public function setUseButtonIcon(?bool $useButtonIcon): self
    {
        $this->useButtonIcon = $useButtonIcon;

        return $this;
    }

    public function hasExternalAccountDeletion(): ?bool
    {
        return (!is_null($this->externalAccountDeletion)) ? $this->externalAccountDeletion : false;
    }

    public function setExternalAccountDeletion(?bool $externalAccountDeletion): self
    {
        $this->externalAccountDeletion = $externalAccountDeletion;

        return $this;
    }

    // If you want more info from user you just have to add it to the jsonSerialize function
    public function jsonSerialize()
    {
        return [
            'service' => $this->getService(),
            'ssoProvider' => $this->getSsoProvider(),
            'uri' => $this->getUri(),
            'buttonIcon' => $this->getButtonIcon(),
            'picto' => $this->getPicto(),
            'useButtonIcon' => $this->hasUseButtonIcon(),
            'externalAccountDeletion' => $this->hasExternalAccountDeletion(),
        ];
    }
}
