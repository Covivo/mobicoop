<?php

namespace App\User\Service;

use App\User\Entity\User;

class BlockedCarpoolers
{
    /**
     * @var User
     */
    private $_user;

    /**
     * @var int[]
     */
    private $_carpoolers;

    public function __construct(User $user)
    {
        $this->_user = $user;
    }

    /**
     * @param int[] $carpoolers
     *
     * @return int[]
     */
    public function filterCarpoolers($carpoolers)
    {
        $this->_carpoolers = $carpoolers;

        // Filtrer les utilisateurs par lesquels j'ai été bloqué
        $this->_filterBlock();
        // Filtrer les utilisateurs que je bloque
        $this->_filterBlockBys();

        return $this->_carpoolers;
    }

    private function _filterBlock(): void
    {
        foreach ($this->_user->getBlocks() as $block) {
            $this->_removeCarpooler(array_search($block->getBlockedUser()->getId(), $this->_carpoolers));
        }
    }

    private function _filterBlockBys(): void
    {
        foreach ($this->_user->getBlockBys() as $block) {
            $this->_removeCarpooler(array_search($block->getUser()->getId(), $this->_carpoolers));
        }
    }

    /**
     * @param bool|int $key
     */
    private function _removeCarpooler($key): void
    {
        if (false !== $key) {
            unset($this->_carpoolers[$key]);
        }
    }
}
