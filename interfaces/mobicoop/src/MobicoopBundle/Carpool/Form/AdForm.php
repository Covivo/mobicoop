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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ad;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Mobicoop\Bundle\MobicoopBundle\Geography\Form\AddressForm;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;

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
        ->add('origin')
        ->add('destination')
        ->add('originAddress')      // not displayed directly => displayed by geocomplete; must be present here for validation
        ->add('originLatitude')      // not displayed directly => displayed by geocomplete; must be present here for validation
        ->add('originLongitude')      // not displayed directly => displayed by geocomplete; must be present here for validation
        ->add('destinationLatitude')      // not displayed directly => displayed by geocomplete; must be present here for validation
        ->add('destinationLongitude')      // not displayed directly => displayed by geocomplete; must be present here for validation
        ->add('destinationAddress') // not displayed directly => displayed by geocomplete; must be present here for validation
        ->add('role')
        ->add('type')
        ->add('frequency')
        // PUNCTUAL
        ->add('outwardDate')
        ->add('outwardTime')
        ->add('outwardMargin')
        ->add('returnDate')
        ->add('returnTime')
        ->add('returnMargin')
        // REGULAR
        ->add('fromDate')
        ->add('toDate')
        ->add('outwardMonTime')
        ->add('outwardMonMargin')
        ->add('outwardTueTime')
        ->add('outwardTueMargin')
        ->add('outwardWedTime')
        ->add('outwardWedMargin')
        ->add('outwardThuTime')
        ->add('outwardThuMargin')
        ->add('outwardFriTime')
        ->add('outwardFriMargin')
        ->add('outwardSatTime')
        ->add('outwardSatMargin')
        ->add('outwardSunTime')
        ->add('outwardSunMargin')
        ->add('returnMonTime')
        ->add('returnMonMargin')
        ->add('returnTueTime')
        ->add('returnTueMargin')
        ->add('returnWedTime')
        ->add('returnWedMargin')
        ->add('returnThuTime')
        ->add('returnThuMargin')
        ->add('returnFriTime')
        ->add('returnFriMargin')
        ->add('returnSatTime')
        ->add('returnSatMargin')
        ->add('returnSunTime')
        ->add('returnSunMargin')
        ->add('comment');
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ad::class,
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                $groups[] = 'Default';
                if (Ad::FREQUENCY_PUNCTUAL == $data->getFrequency()) {
                    if (Ad::TYPE_RETURN_TRIP == $data->getType()) {
                        $groups[] = 'punctualReturnTrip';
                    } else {
                        $groups[] = 'punctual';
                    }
                } else {
                    if (Ad::TYPE_RETURN_TRIP == $data->getType()) {
                        $groups[] = 'regular';
                    } else {
                        $groups[] = 'regular';
                    }
                }
                
                return $groups;
            },
        ]);
    }
}
