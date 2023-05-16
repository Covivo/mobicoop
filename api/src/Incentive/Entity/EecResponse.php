<?php

namespace App\Incentive\Entity;

use App\User\Entity\User;

class EecResponse implements \JsonSerializable
{
    /**
     * @var int
     */
    private $_user;

    /**
     * @var array
     */
    private $_errors = [];

    public function __construct(User $user)
    {
        $this->_user = $user->getId();
    }

    public function getErrors(): array
    {
        return $this->_errors;
    }

    public function addError(string $error): self
    {
        array_push($this->_errors, $error);

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'user' => $this->_user,
            'errors' => $this->_errors,
        ];
    }
}
