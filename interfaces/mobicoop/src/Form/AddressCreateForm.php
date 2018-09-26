<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Create address form.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class AddressCreateForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('streetAddress')
        ->add('postalCode')
        ->add('addressLocality')
        ->add('addressCountry')
        ->add('latitude')
        ->add('longitude')
        ->add('elevation')
        ;
    }
}
