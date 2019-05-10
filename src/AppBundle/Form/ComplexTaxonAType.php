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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use AppBundle\Entity\Entry;

class ComplexTaxonAType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', 'textarea', array(
                'label' => 'Taxon A Description',
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'required' => false,
                'attr' => array(
                    'class' => 'taxon-description',
                )
            ))
            ->add('taxon', 'taxon_a')
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $entry = $event->getData();
            $form = $event->getForm();

            $isInfraspeciesOptions = array(
                'choices' => Entry::getYesNoList(),
                'label' => 'Is Taxon A an Infraspecies?',
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'expanded' => true,
                'multiple' => false,
                'attr' => array('class'=>'infraspecies-trigger'),
            );

            if ($entry && $entry->getIsInfraspecies() === null) {
                $isInfraspeciesOptions['data'] = 0;
            }

            $form->add('isInfraspecies', 'choice', $isInfraspeciesOptions);
        });
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\ComplexTaxon'
        ));
    }
}
