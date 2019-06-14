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

namespace Mobicoop\Bundle\MobicoopBundle\User\Form;

use Mobicoop\Bundle\MobicoopBundle\Form\Type\AriaPasswordType;
use Mobicoop\Bundle\MobicoopBundle\Form\Type\RepeatedAriaPassType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Mobicoop\Bundle\MobicoopBundle\Form\Type\AriaTextType;
use Mobicoop\Bundle\MobicoopBundle\Form\Type\AriaChoiceType;

/**
 * User form.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class UserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $birthYears = [];
        $curYear = date('Y');
        for ($i=($curYear-getenv('USER_MIN_AGE'));$i>=($curYear-getenv('USER_MAX_AGE'));$i--) {
            $birthYears[$i] = $i;
        }

        $builder
        ->add('givenName', AriaTextType::class, [
            'translation_domain' => 'user',
            'label' => 'givenName.label',
            'aria-label' => 'givenName.aria_label',
            'help' => 'givenName.help',
            'attr' => [
                'placeholder' => 'givenName.placeholder',
            ]
        ])
        ->add('familyName', AriaTextType::class, [
            'translation_domain' => 'user',
            'label' => 'familyName.label',
            'aria-label' => 'familyName.label',
            'help' => 'familyName.help',
            'attr' => [
                'placeholder' => 'familyName.placeholder'
            ]
        ])
        ->add('email', AriaTextType::class, [
            'translation_domain' => 'user',
            'label' => 'email.label',
            'help' => 'email.help',
            'attr' => [
                'placeholder' => 'email.placeholder'
            ]
        ])
        ->add('password', RepeatedType::class, [
            'type' => AriaPasswordType::class,
            'translation_domain' => 'user',
            'invalid_message' => 'password.password_match',
            'first_options'  => [
                'label' => 'password.label',
                'help' => 'password.help',
                'attr' => [
                    'placeholder' => 'password.placeholder',
                ],
            ],
            'second_options' => [
                'label' => 'password.label_repeat',
                'help' => 'password.help',
                'attr' => [
                    'placeholder' => 'password.placeholder_repeat'
                ],
            ]
        ])
        ->add('gender', AriaChoiceType::class, [
            'placeholder' => 'gender.placeholder',
            'choices'  => User::GENDERS,
            'translation_domain' => 'user',
            'choice_translation_domain' => true,
            'help' => 'gender.placeholder',
            'label' => 'gender.label',
            'attr' => [
                'class' => 'ariaSelect'
            ]
        ])
        ->add('nationality', TextType::class, [
            'translation_domain' => 'user',
            'label' => 'nationality.label',
            'attr' => [
                'placeholder' => 'nationality.placeholder'
            ]
        ])
        ->add('birthYear', AriaChoiceType::class, [
            'placeholder' => 'birthYear.placeholder',
            'choices'  => $birthYears,
            'translation_domain' => 'user',
            'choice_translation_domain' => false,
            'label' => 'birthYear.label',
            'help' => 'birthYear.placeholder',
            'attr' => [
                'class' => 'ariaSelect'
            ]
        ])
        ->add('telephone', TextType::class, [
            'translation_domain' => 'user',
            'label' => 'telephone.label',
            'help' => 'telephone.help',
            'attr' => [
                'placeholder' => 'telephone.placeholder'
            ]
        ])
        ->add('maxDeviationTime', TextType::class, [
            'translation_domain' => 'user',
            'label' => 'maxDeviationTime.label',
            'attr' => [
                'placeholder' => 'maxDeviationTime.placeholder'
            ]
        ])
        ->add('maxDeviationDistance', TextType::class, [
            'translation_domain' => 'user',
            'label' => 'maxDeviationDistance.label',
            'attr' => [
                'placeholder' => 'maxDeviationDistance.placeholder'
            ]
        ])
        ->add('anyRouteAsPassenger', CheckboxType::class, [
            'required' => false,
            'translation_domain' => 'user',
            'label' => 'anyRouteAsPassenger.label'
        ])
        ->add('multiTransportMode', CheckboxType::class, [
            'required' => false,
            'translation_domain' => 'user',
            'label' => 'multiTransportMode.label'
        ])
        ->add('conditions', CheckboxType::class, [
            'translation_domain' => 'user',
            'label' => 'conditions.label'
        ])
        ->add('submit', SubmitType::class, [
            'translation_domain' => 'user',
            'label' => 'signup.label',
            'attr' => [
                'title' => 'Inscription au service de covoiturage Mobicoop'
            ]

        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
            'validation_groups' => array('signUp','update','password'),
        ));
    }
}
