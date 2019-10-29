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

namespace App\Image\Form;

use App\Image\Entity\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class ImageForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        // Configure each fields you want to be submitted here, like a classic form.
        ->add('eventFile', FileType::class, [
            'label' => 'label.file',
            'required' => false,
        ])
        ->add('userFile', FileType::class, [
            'label' => 'label.file',
            'required' => false,
        ])
        ->add('communityFile', FileType::class, [
            'label' => 'label.file',
            'required' => false,
        ])
        ->add('relayPointFile', FileType::class, [
            'label' => 'label.file',
            'required' => false,
        ])
        ->add('relayPointTypeFile', FileType::class, [
            'label' => 'label.file',
            'required' => false,
        ])
        ->add('name')
        ->add('originalName')
        ->add('title')
        ->add('alt')
        ->add('cropX1')
        ->add('cropY1')
        ->add('cropX2')
        ->add('cropY2')
        ->add('eventId', TextType::class, [
            'required' => false,
        ])
        ->add('userId', TextType::class, [
            'required' => false,
        ])
        ->add('communityId', TextType::class, [
            'required' => false,
        ])
        ->add('relayPointId', TextType::class, [
            'required' => false,
        ])
        ->add('relayPointTypeId', TextType::class, [
            'required' => false,
        ])
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
            'csrf_protection' => false,
        ]);
    }
    
    public function getBlockPrefix()
    {
        return '';
    }
}
