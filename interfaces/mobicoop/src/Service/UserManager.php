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
        $response = $this->dataProvider->getItem($id);
        if ($response->getCode() == 200) {
            return $response->getValue();
        }
        return null;
    }
    
    /**
     * Get all users
     *
     * @return array|null The users found or null if not found.
     */
    public function getUsers()
    {
        $response = $this->dataProvider->getCollection();
        if ($response->getCode() == 200) {
            return $response->getValue();
        }
        return null;
    }
    
    /**
     * Create a user
     *
     * @param User $user The user to create
     *
     * @return User|null The user created or null if error.
     */
    public function createUser(User $user)
    {
        $response = $this->dataProvider->post($user);
        if ($response->getCode() == 201) {
            return $response->getValue();
        }
        return null;
    }
    
    /**
     * Update a user
     *
     * @param User $user The user to update
     *
     * @return User|null The user updated or null if error.
     */
    public function updateUser(User $user)
    {
        $response = $this->dataProvider->put($user);
        if ($response->getCode() == 200) {
            return $response->getValue();
        }
        return null;
    }
    
    /**
     * Delete a user
     *
     * @param int $id The id of the user to delete
     *
     * @return boolean The result of the deletion.
     */
    public function deleteUser(int $id)
    {
        $response = $this->dataProvider->delete($id);
        if ($response->getCode() == 204) {
            return true;
        }
        return false;
    }
}
