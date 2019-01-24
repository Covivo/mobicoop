<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

namespace App\Image\Controller;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use App\Image\Form\ImageForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Image\Service\ImageManager;
use App\Image\Entity\Image;

final class CreateImageAction
{
    private $validator;
    private $doctrine;
    private $factory;
    private $imageManager;
    
    public function __construct(RegistryInterface $doctrine, FormFactoryInterface $factory, ValidatorInterface $validator, ImageManager $imageManager)
    {
        $this->validator = $validator;
        $this->doctrine = $doctrine;
        $this->factory = $factory;
        $this->imageManager = $imageManager;
    }
    
    /**
     * @IsGranted("ROLE_USER")
     */
    public function __invoke(Request $request): Image
    {
        $image = new Image();
        
        $form = $this->factory->create(ImageForm::class, $image);
        $form->handleRequest($request);
        
        // TODO : check if the following code (before submit) could be managed by VichUploaderBundle events
        // see https://github.com/dustin10/VichUploaderBundle/blob/master/Resources/doc/events.md
        
        // we search the future owner of the image (user ? event ?...)
        if ($owner = $this->imageManager->getOwner($image)) {
            // we associate the event and the image
            $owner->addImage($image);
            // we get the image type
            $image->setImageType($this->imageManager->getImageType($image, $owner));
            // we search the position of the image
            $image->setPosition($this->imageManager->getNextPosition($image, $owner));
            // we rename the image depending on the owner
            $image->setFileName($this->imageManager->generateFilename($image, $owner));
        }
        if ($form->isSubmitted() && $form->isValid()) {
            // the form is valid and the image has a valid owner
            // we persist the image to fill the fields automatically (size, dimensions, mimetype...)
            $em = $this->doctrine->getManager();
            $em->persist($image);
            
            // we apply treatments to the image
            $image = $this->imageManager->treat($image, $owner);
            $em->persist($image);
            $em->flush();
            
            return $image;
        }
        
        // This will be handled by API Platform and returns a validation error.
        throw new ValidationException($this->validator->validate($image));
    }
}
