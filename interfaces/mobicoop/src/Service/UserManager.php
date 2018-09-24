<?php

namespace App\Service;

use App\Entity\User;

/**
 * User management service.
 */
class UserManager
{
    private $dataProvider;
    private $deserializer;
    
    public function __construct(DataProvider $dataProvider, Deserializer $deserializer)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(User::class);
    }
    
    /**
     * Get a user by its identifier
     *
     * @param String $id The user id
     *
     * @return User|null The user found or null if not found.
     */
    public function getUser($id)
    {
        return $this->dataProvider->getItem($id);
    }
    
    /**
     * Get all users
     *
     * @return User[]|null The users found or null if not found.
     */
    public function getUsers()
    {
        return $this->dataProvider->getCollection();
    }
    
}