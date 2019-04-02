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
        ->add('origin', TextType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.origin.label',
            'required' => true,
            'attr' => [
                'placeholder' => 'ad.origin.placeholder'
            ]
        ])
        ->add('destination', TextType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.destination.label',
            'required' => true,
            'attr' => [
                'placeholder' => 'ad.destination.placeholder'
            ]
        ])
        ->add('originAddress', AddressForm::class)      // not displayed directly => displayed by geocomplete; must be present here for validation
        ->add('destinationAddress', AddressForm::class) // not displayed directly => displayed by geocomplete; must be present here for validation
        ->add('role', ChoiceType::class, [
            // 'model' => 'role',
            'expanded' => true,
            'choices'  => Ad::ROLES,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => true,
            'label' => 'ad.role.label',
        ])
        ->add('type', ChoiceType::class, [
            // 'model' => 'type',
            'choices'  => Ad::TYPES,
            'expanded' => true,
            'placeholder' => 'ad.type.placeholder',
            'translation_domain' => 'carpool',
            'choice_translation_domain' => true,
            'label' => 'ad.type.label',
        ])
        ->add('frequency', ChoiceType::class, [
            // 'model' => 'frequency',
            'choices'  => Ad::FREQUENCIES,
            'expanded' => true,
            'placeholder' => 'ad.frequency.placeholder',
            'translation_domain' => 'carpool',
            'choice_translation_domain' => true,
            'label' => 'ad.frequency.label'
        ])

        // PUNCTUAL
        ->add('outwardDate', DateType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.outward_date.label',
            'required' => false
        ])
        ->add('outwardTime', TimeType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.outward_time.label',
            'required' => false
        ])
        ->add('outwardMargin', ChoiceType::class, [
            'placeholder' => 'ad.margin.placeholder',
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label',
            'required' => false
        ])
        ->add('returnDate', DateType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.return_date.label',
            'required' => false
        ])
        ->add('returnTime', TimeType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.return_time.label',
            'required' => false
        ])
        ->add('returnMargin', ChoiceType::class, [
            'placeholder' => 'ad.margin.placeholder',
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label',
            'required' => false
        ])
        // REGULAR
        ->add('fromDate', DateType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.from_date.label',
            'required' => false
        ])
        ->add('toDate', DateType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.to_date.label',
            'required' => false
        ])
        ->add('outwardMonTime', TimeType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.outward_mon_time.label',
            'required' => false
        ])
        ->add('outwardMonMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('outwardTueTime', TimeType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.outward_tue_time.label'
        ])
        ->add('outwardTueMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('outwardWedTime', TimeType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.outward_wed_time.label'
        ])
        ->add('outwardWedMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('outwardThuTime', TimeType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.outward_thu_time.label'
        ])
        ->add('outwardThuMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('outwardFriTime', TimeType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.outward_fri_time.label'
        ])
        ->add('outwardFriMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('outwardSatTime', TimeType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.outward_sat_time.label'
        ])
        ->add('outwardSatMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('outwardSunTime', TimeType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.outward_sun_time.label'
        ])
        ->add('outwardSunMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('returnMonTime', TimeType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.return_mon_time.label'
        ])
        ->add('returnMonMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('returnTueTime', TimeType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.return_tue_time.label'
        ])
        ->add('returnTueMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('returnWedTime', TimeType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.return_wed_time.label'
        ])
        ->add('returnWedMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('returnThuTime', TimeType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.return_thu_time.label'
        ])
        ->add('returnThuMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('returnFriTime', TimeType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.return_fri_time.label'
        ])
        ->add('returnFriMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('returnSatTime', TimeType::class, [
            'translation_domain' => 'carpool',
            'label' => 'ad.return_sat_time.label'
        ])
        ->add('returnSatMargin', ChoiceType::class, [
            'choices'  => Ad::MARGIN_TIME,
            'translation_domain' => 'carpool',
            'choice_translation_domain' => false,
            'label' => 'ad.margin.label'
        ])
        ->add('returnSunTime', TimeType::class, [
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
        ));
    }
}
