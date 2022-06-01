<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\Action\Entity;

use App\Communication\Entity\Message;
use App\Communication\Interfaces\MessagerInterface;
use App\Solidary\Entity\Solidary;
use App\Solidary\Entity\SolidarySolution;
use App\User\Entity\User;

/**
 * An animation action.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class Animation implements MessagerInterface
{
    /**
     * @var int the id of this animation
     */
    private $id;

    /**
     * @var Message a message related to the animation
     */
    private $message;

    /**
     * @var string a comment related to the animation
     */
    private $comment;

    /**
     * @var Action the action related with the animation
     */
    private $action;

    /**
     * @var User the user related with the animation
     */
    private $user;

    /**
     * @var User the author of the animation
     */
    private $author;

    /**
     * @var null|int the progression related with the animation, if needed and relevant
     */
    private $progression;

    /**
     * @var null|Solidary the solidary record if the animation concerns a solidary record
     */
    private $solidary;

    /**
     * @var null|SolidarySolution the solidary solution if the animation concerns a solidary record solution
     */
    private $solidarySolution;

    /**
     * @var null|User the volunteer associated with the animation, if needed and relevant
     */
    private $volunteer;

    /**
     * @var null|User the carpooler associated with the animation, if needed and relevant
     */
    private $carpooler;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getAction(): Action
    {
        return $this->action;
    }

    public function setAction(Action $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getMessage(): ?Message
    {
        return $this->message;
    }

    public function setMessage(?Message $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getProgression(): ?string
    {
        return $this->progression;
    }

    public function setProgression(?string $progression): self
    {
        $this->progression = $progression;

        return $this;
    }

    public function getSolidary(): ?Solidary
    {
        return $this->solidary;
    }

    public function setSolidary(?Solidary $solidary): self
    {
        $this->solidary = $solidary;

        return $this;
    }

    public function getSolidarySolution(): ?SolidarySolution
    {
        return $this->solidarySolution;
    }

    public function setSolidarySolution(?SolidarySolution $solidarySolution): self
    {
        $this->solidarySolution = $solidarySolution;

        return $this;
    }

    public function getVolunteer(): ?User
    {
        return $this->volunteer;
    }

    public function setVolunteer(?User $volunteer): self
    {
        $this->volunteer = $volunteer;

        return $this;
    }

    public function getCarpooler(): ?User
    {
        return $this->carpooler;
    }

    public function setCarpooler(?User $carpooler): self
    {
        $this->carpooler = $carpooler;

        return $this;
    }
}
