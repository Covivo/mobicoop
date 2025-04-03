<?php

/**
 * Copyright (c) 2025, MOBICOOP. All rights reserved.
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

namespace App\DataProvider\Entity\Stripe\Entity;

use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

class File
{
    public const PURPOSE_IDENTITY_VALIDATION = 'identity_document';

    private const AUTHORIZED_PURPOSES = [
        self::PURPOSE_IDENTITY_VALIDATION,
    ];

    /**
     * @var string
     */
    private $purpose;

    /**
     * @var SymfonyFile
     */
    private $file;

    public function __construct(string $purpose, SymfonyFile $file)
    {
        $this->setPurpose($purpose);
        $this->file = $file;
    }

    public function getPurpose(): string
    {
        return $this->purpose;
    }

    public function getFile(): SymfonyFile
    {
        return $this->file;
    }

    public function setPurpose(string $purpose): self
    {
        if (!in_array($purpose, self::AUTHORIZED_PURPOSES)) {
            throw new \InvalidArgumentException(sprintf('The purpose "%s" is not supported.', $purpose));
        }
        $this->purpose = $purpose;

        return $this;
    }

    public function setFile(SymfonyFile $file): self
    {
        $this->file = $file;

        return $this;
    }
}
