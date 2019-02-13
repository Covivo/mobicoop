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

namespace Mobicoop\Bundle\MobicoopBundle\Carpool\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ad;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Mobicoop\Bundle\MobicoopBundle\Form\Type\AutocompleteType;

/**
 * Ad form.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class AdForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('origin', AutocompleteType::class, [
            'url' => '/geosearch?search=',
            'translation_domain' => 'carpool',
            'label' => 'ad.origin.label',
            'attr' => [
                'placeholder' => 'ad.origin.placeholder'
            ]
        ])
        ->add('destination', AutocompleteType::class, [
            'url' => '/geosearch?search=',
            'translation_domain' => 'carpool',
            'label' => 'ad.destination.label',
            'attr' => [
                'placeholder' => 'ad.destination.placeholder'
            ]
        ])
        ->add('role', ChoiceType::class, [
            'choices'  => Ad::ROLES,
            'placeholder' => 'ad.role.placeholder',
            'translation_domain' => 'carpool',
            'choice_translation_domain' => true,
            'label' => 'ad.role.label'
        ])
        ->add('outwardDate', DateTimeType::class, [
            'translation_domain' => 'carpool',
            'years' => range(date('Y'), date('Y')+3),
            'months' => range(1, 12),
            'days' => range(1, 31),
            'label' => 'ad.outward_date.label'
        ])
        ->add('outwardMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'placeholder' => 'ad.outward_margin.placeholder',
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.outward_margin.label'
        ])
        ->add('frequency', ChoiceType::class, [
            'choices'  => Ad::FREQUENCIES,
            'placeholder' => 'ad.frequency.placeholder',
            'translation_domain' => 'carpool',
            'choice_translation_domain' => true,
            'label' => 'ad.frequency.label',
        ])
        ->add('comment', TextareaType::class, [
            'required' => false,
            'translation_domain' => 'carpool',
            'label' => 'ad.comment.label',
            'attr' => [
                'placeholder' => 'ad.comment.placeholder'
            ]
        ])
        ->add('price', TextType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.price.label',
            'attr' => [
                'placeholder' => 'ad.price.placeholder'
            ]
        ])
        ->add('submit', SubmitType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.submit.label'
        ])
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => Ad::class,
        ));
    }
}
