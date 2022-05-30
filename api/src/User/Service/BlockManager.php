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
 **************************/

namespace App\User\Service;

use App\User\Entity\User;
use App\User\Repository\BlockRepository;
use App\User\Ressource\Block;
use App\User\Entity\Block as BlockEntity;
use App\User\Exception\BlockException;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;

/**
 * Block manager service.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BlockManager
{
    private $blockRepository;
    private $entityManager;

    public function __construct(BlockRepository $blockRepository, EntityManagerInterface $entityManager)
    {
        $this->blockRepository = $blockRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Get the blocked users
     * optional : get by a given user
     *
     * @param User|null $user    The User who made the block
     * @return User[]
     */
    public function getBlockedUsers(?User $user = null): array
    {
        $users = [];
        if (!is_null($user)) {
            $blocks = $this->blockRepository->findBy(['user'=>$user]);
        } else {
            $blocks = $this->blockRepository->findAll();
        }

        foreach ($blocks as $block) {
            $users[] = $block->getBlockedUser();
        }

        return $users;
    }

    /**
     * Get the users that block the User given in parameter
     * optional : get by a given user
     *
     * @param User|null $user    The User blocked
     * @return User[]
     */
    public function getBlockedByUsers(?User $user = null): array
    {
        $users = [];
        if (!is_null($user)) {
            $blocks = $this->blockRepository->findBy(['blockedUser'=>$user]);
        } else {
            $blocks = $this->blockRepository->findAll();
        }

        foreach ($blocks as $block) {
            if (!in_array($block->getUser(), $users)) {
                $users[] = $block->getUser();
            }
        }

        return $users;
    }

    /**
     * Make a Block Ressource from a Block Entity
     *
     * @param BlockEntity $blockEntity
     * @return Block
     */
    public function makeBlockRessource(BlockEntity $blockEntity): Block
    {
        $block = new Block();
        $block->setUser($blockEntity->getBlockedUser());
        $block->setCreatedDate($blockEntity->getCreatedDate());
        return $block;
    }

    /**
     * Handle the block status between two Users. It blocks or unblocks according the current state.
     * @param User $blocker     The user who make the block
     * @param User $blockedUser The blocked user
     * @return Block|null
     */
    public function handleBlock(User $blocker, User $blockedUser): ?Block
    {
        // Determines if the blockedUser is already blocked by the blocker
        $blocks = $this->blockRepository->findBy([
            'user'=>$blocker,
            'blockedUser'=>$blockedUser
        ]);
        if (count($blocks)>0) {
            // Already block, we want to unblock it
            // I do a foreach by security in case of multiple records. But it should only be one record at the time.
            foreach ($blocks as $block) {
                $this->unblock($block);
            }
            return null;
        } else {
            // Not already block, blocker wants to block blockedUser
            return $this->makeBlockRessource($this->block($blocker, $blockedUser));
        }
    }

    /**
     * Block the blockedUser by the blocker
     * @param User $blocker     The user who make the block
     * @param User $blockedUser The blocked user
     * @return BlockEntity|null
     */
    public function block(User $blocker, User $blockedUser): ?BlockEntity
    {
        $block = new BlockEntity();
        $block->setUser($blocker);
        $block->setBlockedUser($blockedUser);
        $this->entityManager->persist($block);
        $this->entityManager->flush();

        return $block;
    }

    /**
     * Delete the Block
     * @param BlockEntity $block The Block to delete
     */
    public function unblock(BlockEntity $block)
    {
        $this->entityManager->remove($block);
        $this->entityManager->flush();
    }

    /**
     * Get all the blocks involving $user1 and $user2
     * @param User $user1
     * @param User $user2
     * @return bool
     */
    public function getInvolvedInABlock(User $user1, User $user2): bool
    {
        return $this->blockRepository->findAllByUsersInvolved($user1, $user2);
    }
}
