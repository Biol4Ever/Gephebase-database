<?php

/*

 Copyright 2019 CNRS.

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 3 of the License,
 or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/


namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CuratorType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('surname', 'text', array(
                    'label' => 'First Name',
                    'label_attr' => array(
                        'class' => 'gephebase-entry-label',
                    ),
                    'required'=> true,
            ))
            ->add('name', 'text', array(
                    'label' => 'Last Name',
                    'label_attr' => array(
                        'class' => 'gephebase-entry-label',
                    ),
                    'required'=> true,
            ))
            ->add('email', 'email', array(
                    'label' => 'Email',
                    'label_attr' => array(
                        'class' => 'gephebase-entry-label',
                    ),
                    'required'=> true,
            ))
            ->add('username', 'text', array(
                    'label' => 'Username',
                    'label_attr' => array(
                        'class' => 'gephebase-entry-label',
                    ),
                    'required'=> true,
            ))
            ->add('password', 'password', array(
                    'label' => 'Password',
                    'label_attr' => array(
                        'class' => 'gephebase-entry-label',
                    ),
                    'required'=> false,
                    'mapped' => false,
            ))
            ->add('confirmPassword', 'password', array(
                    'label' => 'Confirm Password',
                    'label_attr' => array(
                        'class' => 'gephebase-entry-label',
                    ),
                    'required'=> false,
                    'mapped' => false,
            ))
            ->add('roleUser', 'choice', array(
                'label' => 'Is the user an administrator ?',
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'choices' => array(
                    true => 'Yes',
                    'no' => 'No',
                ),
                'empty_value' => false,
                'expanded' => true,
                'multiple' => false,
                'mapped' => false,
                'required' => false,
            ))
            ->add('submit', 'button', array(
                'attr' => array('class' => 'btn gephebase-btn'),
                'label' => 'Submit',
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User'
        ));
    }

    public function getName()
    {
        return 'curator';
    }
}
