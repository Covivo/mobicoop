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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\Form\Login;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * User Login form.
 *
 * @author Maxime Bardot <maxime.bardot@covivo.eu>
 */
class UserLoginForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('username', TextType::class, [
            'translation_domain' => 'login',
            'label' => 'username.label',
            'help' => 'username.placeholder',
            'attr' => [
                'placeholder' => 'username.placeholder'
            ]
        ])
        ->add('password', PasswordType::class, [
            'translation_domain' => 'login',
            'label' => 'password.label',
            'help' => 'password.placeholder',
            'attr' => [
                'placeholder' => 'password.placeholder'
            ]
        ])
        ->add('login', SubmitType::class, [
            'translation_domain' => 'ui',
            'label' => 'button.connexion',
            'attr' => [
                'title' => 'Se connecter au service Mobicoop'
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Login::class,
        ));
    }
}
