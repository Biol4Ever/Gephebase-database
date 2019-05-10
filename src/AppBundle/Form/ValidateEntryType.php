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

class ValidateEntryType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entry = $builder->getData();

        $builder
            ->add('validate', 'choice', array(
                'choices'=> array(
                    true => 'Yes, all is OK. Validate.',
                    false => 'No, I don\'t validate. There are misspellings/errors',
                ),
                'label' => ' ',
                'expanded'=> true,
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'required' => true,
            ))
            ->add('comments', 'textarea', array(
                'label' => 'Comments',
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'required' => false,
            ))
            ->add('submit', 'submit', array(
                'attr' => array('class' => 'btn gephebase-btn'),
                'label' => 'Submit',
            ))
            ->add('return', 'button', array(
                'attr' => array('class' => 'btn gephebase-btn'),
                'label' => 'Cancel & Return to list',
            ))
        ;
    }

    public function getName()
    {
        return 'validateEntry';
    }
}
