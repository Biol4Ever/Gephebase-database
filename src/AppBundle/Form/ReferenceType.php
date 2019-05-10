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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReferenceType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pmId','text',array(
                'label' => 'Pubmed Id / Ris Id',
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'required' => false,
                'attr' => array(
                    'class' => 'fetched-data pm-id',
                ),
            ))
            ->add('articleTitle','text', array(
                'label' => 'Title',
                'label_attr' => array(
                    'class' => 'external-data-label',
                ),
                'attr' => array(
                    'class' => 'fetched-data article-title',
                    'readonly' => 'readonly',
                ),
                'required' => false,

            ))
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $reference = $event->getData();
            $form = $event->getForm();

            $form->add('authors', 'author_list', array(
                'attr' => array(
                    'readonly' => 'readonly',
                    'class' => 'fetched-data authors',
                    'rows' => '2',
                ),
                'invalid_message' => 'Invalid textarea format. Authors must be semi-colon seperated.',
                'reference' => $reference,
                'label_attr' => array(
                    'class' => 'external-data-label',
                ),

            ));
        });
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Reference'
        ));
    }
}