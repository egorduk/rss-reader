<?php

namespace Acme\TestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Tests\Fixtures\Entity;


class ViewForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /*$builder->add('sourceId', 'entity', array('label'=>'Select source:', 'class' => 'Acme\\TestBundle\\Entity\\Source', 'property' => 'name','empty_value' => 'Choose a source', 'required' => false))
                ->add('Delete', 'submit', array(
                'attr' => array('class' => 'symfony-button-grey')));*/

        $builder->add('Delete', 'button', array(
                'attr' => array('class' => 'symfony-button-grey')))
                ->add('Save', 'submit', array(
                    'attr' => array('class' => 'symfony-button-grey')));
    }

    public function getName()
    {
        return 'viewForm';
    }
}