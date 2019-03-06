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
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ad;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Mobicoop\Bundle\MobicoopBundle\Form\Type\GeocompleteType;
use Mobicoop\Bundle\MobicoopBundle\Geography\Form\AddressForm;
use Mobicoop\Bundle\MobicoopBundle\Form\Type\DatepickerType;
use Mobicoop\Bundle\MobicoopBundle\Form\Type\TimepickerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

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
        ->add('origin', GeocompleteType::class, [
            'url' => '/geosearch?search=',
            'address' => 'originAddress',
            'translation_domain' => 'carpool',
            'label' => 'ad.origin.label',
            'attr' => [
                'placeholder' => 'ad.origin.placeholder'
            ]
        ])
        ->add('destination', GeocompleteType::class, [
            'url' => '/geosearch?search=',
            'address' => 'destinationAddress',
            'translation_domain' => 'carpool',
            'label' => 'ad.destination.label',
            'attr' => [
                'placeholder' => 'ad.destination.placeholder'
            ]
        ])
        ->add('originAddress', AddressForm::class)      // not displayed directly => displayed by geocomplete; must be present here for validation
        ->add('destinationAddress', AddressForm::class) // not displayed directly => displayed by geocomplete; must be present here for validation
        ->add('role', ChoiceType::class, [
            'choices'  => Ad::ROLES,
            'expanded' => true,
            'placeholder' => 'ad.role.placeholder',
            'translation_domain' => 'carpool',
            'choice_translation_domain' => true,
            'label' => 'ad.role.label'
        ])
        ->add('type', ChoiceType::class, [
            'choices'  => Ad::TYPES,
            'expanded' => false,
            'placeholder' => 'ad.type.placeholder',
            'translation_domain' => 'carpool',
            'choice_translation_domain' => true,
            'label' => 'ad.type.label',
            'attr' => [
                'v-model' => 'type',
                'erjhoei' => 'nnonon'
            ]
        ])
        ->add('frequency', ChoiceType::class, [
            'choices'  => Ad::FREQUENCIES,
            'expanded' => true,
            'placeholder' => 'ad.frequency.placeholder',
            'translation_domain' => 'carpool',
            'choice_translation_domain' => true,
            'label' => 'ad.frequency.label',
            'attr' => [
                'v-model' => 'frequency'
            ]
        ])

        // PUNCTUAL
        ->add('outwardDate', DatepickerType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.outward_date.label'
        ])
        ->add('outwardTime', TimepickerType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.outward_time.label'
        ])
        ->add('outwardMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'placeholder' => 'ad.margin.placeholder',
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('returnDate', DatepickerType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.return_date.label'
        ])
        ->add('returnTime', TimepickerType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.return_time.label'
        ])
        ->add('returnMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'placeholder' => 'ad.margin.placeholder',
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        // REGULAR
        ->add('fromDate', DatepickerType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.from_date.label'
        ])
        ->add('toDate', DatepickerType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.to_date.label'
        ])
        ->add('outwardMon', CheckboxType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.outward_mon.label'
        ])
        ->add('outwardMonTime', TimepickerType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.outward_mon_time.label'
        ])
        ->add('outwardMonMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('outwardTue', CheckboxType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.outward_tue.label'
        ])
        ->add('outwardTueTime', TimepickerType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.outward_tue_time.label'
        ])
        ->add('outwardTueMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('outwardWed', CheckboxType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.outward_wed.label'
        ])
        ->add('outwardWedTime', TimepickerType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.outward_wed_time.label'
        ])
        ->add('outwardWedMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('outwardThu', CheckboxType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.outward_thu.label'
        ])
        ->add('outwardThuTime', TimepickerType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.outward_thu_time.label'
        ])
        ->add('outwardThuMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('outwardFri', CheckboxType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.outward_fri.label'
        ])
        ->add('outwardFriTime', TimepickerType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.outward_fri_time.label'
        ])
        ->add('outwardFriMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('outwardSat', CheckboxType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.outward_sat.label'
        ])
        ->add('outwardSatTime', TimepickerType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.outward_sat_time.label'
        ])
        ->add('outwardSatMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('outwardSun', CheckboxType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.outward_sun.label'
        ])
        ->add('outwardSunTime', TimepickerType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.outward_sun_time.label'
        ])
        ->add('outwardSunMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('returnMon', CheckboxType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.return_mon.label'
        ])
        ->add('returnMonTime', TimepickerType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.return_mon_time.label'
        ])
        ->add('returnMonMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('returnTue', CheckboxType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.return_tue.label'
        ])
        ->add('returnTueTime', TimepickerType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.return_tue_time.label'
        ])
        ->add('returnTueMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('returnWed', CheckboxType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.return_wed.label'
        ])
        ->add('returnWedTime', TimepickerType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.return_wed_time.label'
        ])
        ->add('returnWedMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('returnThu', CheckboxType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.return_thu.label'
        ])
        ->add('returnThuTime', TimepickerType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.return_thu_time.label'
        ])
        ->add('returnThuMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('returnFri', CheckboxType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.return_fri.label'
        ])
        ->add('returnFriTime', TimepickerType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.return_fri_time.label'
        ])
        ->add('returnFriMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('returnSat', CheckboxType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.return_sat.label'
        ])
        ->add('returnSatTime', TimepickerType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.return_sat_time.label'
        ])
        ->add('returnSatMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('returnSun', CheckboxType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.return_sun.label'
        ])
        ->add('returnSunTime', TimepickerType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.return_sun_time.label'
        ])
        ->add('returnSunMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
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
