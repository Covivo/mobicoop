<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
//use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

/**
 * User form.
 * 
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class UserForm extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('givenName')
        ->add('familyName')
        ->add('givenName')
        ->add('email')
        ->add('password',PasswordType::class)
        ->add('gender')
        ->add('nationality')
        ->add('birthDate',BirthdayType::class)
        ->add('telephone')
        ->add('maxDeviationTime')
        ->add('maxDeviationDistance')
        /*->add('userAddresses', CollectionType::class, [
                'entry_type' => UserAddressCreateForm::class,
                'allow_add' => true,
                'by_reference' => false,
                'error_bubbling' => false,
        ])*/
        ->add('submit', SubmitType::class)
        ;
        
    }

}