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
 **************************/

namespace App\Paginator;

use ApiPlatform\Core\DataProvider\PaginatorInterface;

/**
 * Custom paginator class, used to wrap paginator objects that need to be overloaded
 */
class MobicoopPaginator implements \IteratorAggregate, PaginatorInterface
{
    private $itemIterator;
    private $items;
    private $currentPage;
    private $itemsPerPage;
    private $totalItems;

    public function __construct(array $items, int $currentPage, int $itemsPerPage, int $totalItems)
    {
        $this->items = $items;
        $this->currentPage = $currentPage;
        $this->itemsPerPage = $itemsPerPage;
        $this->totalItems = $totalItems;
    }

    public function getLastPage(): float
    {
        return ceil($this->getTotalItems() / $this->getItemsPerPage()) ?: 1.;
    }

    public function getTotalItems(): float
    {
        return $this->totalItems;
    }

    public function getCurrentPage(): float
    {
        return $this->currentPage;
    }

    public function getItemsPerPage(): float
    {
        return $this->itemsPerPage;
    }

    public function getIterator(): \Traversable
    {
        if ($this->itemIterator === null) {
            $this->itemIterator = new \ArrayIterator($this->items);
        }
        return $this->itemIterator;
    }

    public function count(): int
    {
        return iterator_count($this->getIterator());
    }
}
