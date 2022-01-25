<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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

namespace App\User\Service;

use App\User\Entity\IdentityProof;
use App\User\Entity\User;
use App\User\Event\IdentityProofModeratedEvent;
use App\User\Repository\IdentityProofRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Security;

class IdentityProofManager
{
    private $admin;
    private $identityProofRepository;
    private $entityManager;
    private $eventDispatcher;
    private $uploadPath;
    private $urlPath;

    public function __construct(
        Security $security,
        IdentityProofRepository $identityProofRepository,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        string $uploadPath,
        string $urlPath
    ) {
        $this->admin = $security->getUser();
        $this->identityProofRepository = $identityProofRepository;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->uploadPath = $uploadPath;
        $this->urlPath = $urlPath;
    }

    public function createIdentityProof(User $user, File $file): IdentityProof
    {
        if ($this->userHasAcceptedProof($user)) {
            throw new Exception('This user already has an accepted identity proof.');
        }
        $pendingProof = $this->getPendingProofForUser($user);
        if ($pendingProof) {
            $pendingProof->setStatus(IdentityProof::STATUS_CANCELED);
            $this->removeProofFile($pendingProof);
            $this->entityManager->persist($pendingProof);
            $this->entityManager->flush();
        }
        $identityProof = new IdentityProof();
        $identityProof->setFile($file);
        $identityProof->setUser($user);
        $identityProof->setFileName($user->getId().'-'.time());

        return $identityProof;
    }

    public function patchIdentityProof(int $id, array $fields)
    {
        $identityProof = $this->identityProofRepository->find($id);

        if (!$identityProof) {
            throw new Exception('Identity proof not found');
        }

        if (array_key_exists('validate', $fields)) {
            if (IdentityProof::STATUS_PENDING != $identityProof->getStatus()) {
                throw new Exception('Identity proof status is not pending');
            }

            return $this->validateIdentityProof($identityProof, $fields['validate']);
        }

        return $identityProof;
    }

    public function getFileUrl(IdentityProof $identityProof)
    {
        if (IdentityProof::STATUS_PENDING == $identityProof->getStatus()) {
            return $this->urlPath.rawurlencode($identityProof->getFileName());
        }

        return null;
    }

    public function sendReminders()
    {
        $identityProofs = $this->identityProofRepository->findBy(['status' => IdentityProof::STATUS_PENDING]);
        foreach ($identityProofs as $identityProof) {
            echo $identityProof->getId()."\n";
        }
    }

    private function validateIdentityProof(IdentityProof $identityProof, bool $validate): IdentityProof
    {
        $identityProof->setAdmin($this->admin);
        $identityProof->setStatus($validate ? IdentityProof::STATUS_ACCEPTED : IdentityProof::STATUS_REFUSED);
        $this->entityManager->persist($identityProof);
        $this->entityManager->flush();
        $this->removeProofFile($identityProof);

        $event = new IdentityProofModeratedEvent($identityProof);
        $this->eventDispatcher->dispatch(IdentityProofModeratedEvent::NAME, $event);

        return $identityProof;
    }

    private function userHasAcceptedProof(User $user): bool
    {
        return null !== $this->identityProofRepository->findOneBy([
            'user' => $user,
            'status' => IdentityProof::STATUS_ACCEPTED,
        ]);
    }

    private function getPendingProofForUser(User $user): ?IdentityProof
    {
        return $this->identityProofRepository->findOneBy([
            'user' => $user,
            'status' => IdentityProof::STATUS_PENDING,
        ]);
    }

    private function removeProofFile(IdentityProof $identityProof)
    {
        if (file_exists($this->uploadPath.$identityProof->getFileName())) {
            unlink($this->uploadPath.$identityProof->getFileName());
        }
    }
}
