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

class ParameterType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('contact', 'text', array(
                    'label' => 'Contact :',
                    'label_attr' => array(
                        'class' => 'gephebase-entry-label',
                    ),
                    'required'=> true,
            ))
            ->add('contactValidator', 'text', array(
                    'label' => 'Return of validators :',
                    'label_attr' => array(
                        'class' => 'gephebase-entry-label',
                    ),
                    'required'=> true,
            ))
            ->add('homepageDescription', 'textarea', array(
                'attr' => array(
                    'class' => 'tinymce',
                    //'data-theme' => 'bbcode' // Skip it if you want to use default theme
                ),
                'label' => 'Homepage description :'
            ))
            ->add('whatsNew', 'textarea', array(
                'attr' => array(
                    'class' => 'tinymce',
                    //'data-theme' => 'bbcode' // Skip it if you want to use default theme
                ),
                'label' => 'What\'s new ? : ',
            ))
            ->add('greetings', 'textarea', array(
                'attr' => array(
                    'class' => 'tinymce',
                    //'data-theme' => 'bbcode' // Skip it if you want to use default theme
                ),
                'label' => 'Greetings : ',
            ))
            ->add('fromMail', 'text', array(
                    'label' => 'All mails from :',
                    'label_attr' => array(
                        'class' => 'gephebase-entry-label',
                    ),
                    'required'=> true,
            ))
            ->add('loginMail', 'text', array(
                    'label' => 'Return of login\'s connections :',
                    'label_attr' => array(
                        'class' => 'gephebase-entry-label',
                    ),
                    'required'=> true,
            ))
            ->add('subject', 'text', array(
                    'label' => 'Subject\'s mail to validator :',
                    'label_attr' => array(
                        'class' => 'gephebase-entry-label',
                    ),
                    'required'=> true,
            ))
            ->add('submit', 'submit', array(
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
            'data_class' => 'AppBundle\Entity\Parameter'
        ));
    }

    public function getName()
    {
        return 'parameter';
    }
}
