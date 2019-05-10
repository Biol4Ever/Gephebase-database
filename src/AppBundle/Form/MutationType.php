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
use AppBundle\Entity\Entry;

class MutationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('presumptiveNull', 'choice', array(
                'choices'=> Entry::getPresumptiveNullList(),
                'label' => 'Presumptive Null?',
                'expanded'=> true,
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'attr' => array(
                    'class' => 'not-empty-list gephe-choice-list',
                ),
                'required' => false,
            ))
            ->add('snp', 'choice', array(
                'choices' => Entry::getSNPList(),
                'label' => 'SNP Coding Change',
                'expanded' => true,
                'multiple' => false,
                'required' => false,
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'attr' => array(
                    'class' => 'gephe-choice-list',
                ),
                'placeholder' => 'Not Applicable'
            ))
            ->add('aberrationSize', 'choice', array(
                'choices' => Entry::getAberrationSizeList(),
                'label' => 'Aberration Size',
                'expanded' => true,
                'required' => false,
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'attr' => array(
                    'class' => 'gephe-choice-list',
                ),
                'placeholder' => 'Not Curated',
            ))
            ->add('codonTaxonA', 'text', array(
                'label' => false,
                'required' => false,
            ))
            ->add('codonTaxonB', 'text', array(
                'label' => false,
                'required' => false,
            ))
            ->add('aaPosition', 'text', array(
                'label' => false,
                'required' => false,
            ))
            ->add('codonPosition', 'text', array(
                'label' => false,
                'required' => false,
            ))
            ->add('aminoAcidTaxonA', 'text', array(
                'label' => false,
                'required' => false,
            ))
            ->add('aminoAcidTaxonB', 'text', array(
                'label' => false,
                'required' => false,
            ))
            ->add('molecularDetails', 'textarea', array(
                'label' => 'Molecular Details of the Mutation(s)',
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'required' => false,
            ))
            ->add('experimentalEvidence', 'choice', array(
                'choices' => Entry::getExperimentalList(),
                'label' => 'Experimental Evidence',
                'expanded' => true,
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'attr' => array(
                    'class' => 'not-empty-list gephe-choice-list',
                ),
                'required' => false,
            ))
            ->add('mainReference', new ReferenceType(), array())
            ->add('otherReferences', 'collection', array(
                'type' => new MutationReferenceType(),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype_name' => '__reference_name__',
            ))
            ->add('addOtherReferencePmid', 'text', array(
                'mapped' => false,
                'required' => false,
                'attr' => array(
                    'class' => 'pm-id',
                ),
            ))
        ;

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $entity = $event->getData();
            $form = $event->getForm();

            $molecularOptions = array(
                'choices'=> Entry::getMolecularList(),
                'label' => 'Molecular Type',
                'expanded'=> true,
                'label_attr' => array('class' => 'gephebase-entry-label',),
                'attr' => array(
                    'class' => 'gephe-choice-list',
                ),
            );

            $aberrationOptions = array(
                'choices' => Entry::getAberrationList(),
                'label' => 'Aberration Type',
                'expanded' => true,
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'attr' => array(
                    'class' => 'gephe-choice-list',
                ),
            );

            // set default values when creating a new entry
            if (!$entity || null === $entity->getId()) {
                $molecularOptions['data'] = 'Coding';
                $aberrationOptions['data'] = 'SNP';
            }

            $form->add('molecularType', 'choice', $molecularOptions);
            $form->add('aberrationType', 'choice', $aberrationOptions);
        });
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Mutation'
        ));
    }
}
