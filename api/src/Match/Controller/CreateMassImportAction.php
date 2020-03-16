<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace App\Match\Controller;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use App\TranslatorTrait;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Match\Service\MassImportManager;
use App\Match\Entity\Mass;
use App\Match\Form\MassImportForm;
use Psr\Log\LoggerInterface;

final class CreateMassImportAction
{
    use TranslatorTrait;
    private $validator;
    private $doctrine;
    private $factory;
    private $massImportManager;
    private $logger;

    public function __construct(RegistryInterface $doctrine, FormFactoryInterface $factory, ValidatorInterface $validator, MassImportManager $massImportManager, LoggerInterface $logger)
    {
        $this->validator = $validator;
        $this->doctrine = $doctrine;
        $this->factory = $factory;
        $this->massImportManager = $massImportManager;
        $this->logger = $logger;
    }

    public function __invoke(Request $request): ?Mass
    {
        if (is_null($request)) {
            throw new \InvalidArgumentException($this->translator->trans("bad request id is provided"));
        }
        /** @var Mass $mass */
        $mass = new Mass();
        $mass->setStatus(Mass::STATUS_INCOMING);

        $form = $this->factory->create(MassImportForm::class, $mass);
        $form->handleRequest($request);

        $originalName = null;

        // we search the user of the file
        if ($user = $this->massImportManager->getUser($mass)) {
            // we associate the user and the mass
            $user->addMass($mass);
            // we rename the file
            $mass->setFileName($this->massImportManager->generateFilename($mass));
            // we check if an originalName has been sent
            if ($mass->getOriginalName()) {
                $originalName = $mass->getOriginalName();
            }
        }
        if ($form->isSubmitted() && $form->isValid()) {
            // the form is valid and the image has a valid user
            // we persist the file to fill the fields automatically (size, mimetype...)
            $em = $this->doctrine->getManager();
            $em->persist($mass);

            if ($originalName) {
                $mass->setOriginalName($originalName);
            }

            // Prevent the serialization of the file property
            $mass->preventSerialization();

            $em->persist($mass);
            $em->flush();

            // the file is uploaded, we treat it and return it
            $mass = $this->massImportManager->treatMass($mass);

            // if we have an error we remove the file
            if (count($mass->getErrors()) > 0) {
                $mass->setStatus(Mass::STATUS_INVALID);
                $originalMass = clone $mass;
                $em->remove($mass);
                $em->flush();
                return $originalMass;
            }

            $mass->setStatus(Mass::STATUS_VALID);
            $em->persist($mass);
            $em->flush();

            return $mass;
        }

        // This will be handled by API Platform and returns a validation error.
        throw new ValidationException($this->validator->validate($mass));
    }
}
