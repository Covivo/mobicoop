<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Create user address form.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class UserAddressCreateForm extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('name')
        ->add('address', CollectionType::class, [
                'entry_type' => AddressCreateForm::class,
                'allow_add' => true,
                'by_reference' => false,
                'error_bubbling' => false,
        ])
        ;
        
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'App\Entity\UserAddress',
        ));
    }
    
}