<?php

namespace Acme\TestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Tests\Fixtures\Entity;


class ControlForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('sourceId', 'entity', array('label'=>'Select source:', 'class' => 'Acme\\TestBundle\\Entity\\Source', 'property' => 'name','empty_value' => 'Choose a source', 'required' => false))
                ->add('fieldName', 'text', array('label'=>'Name:', 'required' => false, 'attr' => array('size' => 30)))
                ->add('fieldUrl', 'text', array('label'=>'Url:', 'required' => false, 'attr' => array('size' => 50)))
                ->add('Add', 'submit', array(
                'attr' => array('class' => 'symfony-button-grey')))
                ->add('Delete', 'submit', array(
                'attr' => array('class' => 'symfony-button-grey')))
                ->add('Edit', 'submit', array(
                'attr' => array('class' => 'symfony-button-grey')));
    }

    public function getName()
    {
        return 'controlForm';
    }
}
