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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Entry;
use Doctrine\ORM\EntityManager;

class EntryComplexType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entry = $builder->getData();
        $mainCurator = null;
        if ($entry) {
            $mainCurator = $entry->getMainCurator();
        }

        $builder
            /* Gephebaase Summary fields */
            ->add('geneGephebase', 'text', array(
                    'label' => 'Gephebase Gene',
                    'required' => false,
                    'label_attr' => array(
                        'class' => 'gephebase-entry-label',
                    ),
            ))
            ->add('gepheId', 'text', array(
                    'label' => 'Gephe ID',
                    'label_attr' => array(
                        'class' => 'gephebase-entry-label',
                    ),
                    'attr' => array(
                        'readonly' => 'readonly',
                    ),
            ))
            ->add('mainCurator', 'text', array(
                'label' => 'Main Curator',
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'attr' => array(
                    'readonly' => 'readonly',
                ),
                'mapped' => false,
                'data' => $mainCurator,
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
            ->add('status', new StatusType(), array())

            ->add('genbankId', 'text', array(
                    'label' => 'GenebankID or UniProtKB',
                    'required' => false,
                    'label_attr' => array(
                        'class' => 'gephebase-entry-label',
                    ),
                    'attr' => array(
                        'class' => 'fetched-data',
                    ),
            ))
            ->add('genbankTaxonAOrB', 'text', array(
                    'label' => 'Taxon A or B',
                    'required' => false,
                    'label_attr' => array(
                        'class' => 'external-data-label',
                    ),
                    'attr' => array(
                        'readonly' => 'readonly',
                        'class' => 'fetched-data',
                    ),
            ))
            ->add('genbankOrganism', 'hidden', array(
                    'required' => false,
                    'attr' => array(
                        'readonly' => 'readonly',
                        'class' => 'fetched-data',
                    ),
            ))
            ->add('gene', 'gene')
            ->add('taxonA', 'taxon_a')
            ->add('taxonB', 'taxon_b')
            ->add('mainReference', new ReferenceType(), array())
            ->add('otherReferences', 'collection', array(
                'type' => new ReferenceType(),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ))
            ->add('ancestralState', 'choice', array(
                'choices' => Entry::getAncestralList(),
                'label' => 'Ancestral State ?',
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'attr' => array(
                    'class' => 'not-empty-list',
                ),
                'expanded' => true,
                'multiple' => false,
                'required' => false,
            ))
            ->add('taxonomicStatus', 'choice', array(
                'choices' => Entry::getTaxonomicList(),
                'label' => 'Taxonomic Status',
                'expanded' => true,
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'attr' => array(
                    'class' => 'not-empty-list',
                ),
                'required' => false,
            ))
            ->add('taxonADescription', 'textarea', array(
                'label' => 'Taxon A Description',
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'required' => false,            ))
            ->add('taxonBDescription', 'textarea', array(
                'label' => 'Taxon B Description',
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
                    'class' => 'not-empty-list',
                ),
                'required' => false,
            ))
            ->add('molecularDetails', 'textarea', array(
                'label' => 'Molecular Details of the Mutation(s)',
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'required' => false,
            ))
            ->add('presumptiveNull', 'choice', array(
                'choices'=> Entry::getPresumptiveNullList(),
                'label' => 'Presumptive Null?',
                'expanded'=> true,
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'attr' => array(
                    'class' => 'not-empty-list',
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
                'placeholder' => 'Not Applicable',
            ))
            ->add('comments', 'textarea', array(
                'label' => 'Comments',
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'required' => false,
            ))
            ->add('addOtherReferencePmid', 'text', array(
                'mapped' => false,
                'required' => false,
            ))
            ->add('addOtherValidator', 'text', array(
                'mapped' => false,
                'required' => false,
            ))
            /*->add('validator', new AddReviewerType(), array())  */
            ->add('save', 'submit', array(
                'attr' => array('class' => 'btn gephebase-btn'),
                'label' => 'Save & return to list',
            ))
            ->add('submit', 'submit', array(
                'attr' => array('class' => 'btn gephebase-btn'),
                'label' => 'Submit',
            ))
            ->add('save_and_next', 'submit', array(
                'attr' => array('class' => 'btn gephebase-btn'),
                'label' => 'Save & show next entry',
            ))
            ->add('saveEntry', 'submit', array(
                'attr' => array('class' => 'btn gephebase-btn', 'form' => 'entry_form'),
                'label' => 'Save Entry',
            ))
            ->add('saveEntryMobile', 'submit', array(
                'attr' => array('class' => 'btn gephebase-btn', 'form' => 'entry_form'),
                'label' => 'Save Entry',
            ))
            ->add('saveEntryReduce', 'submit', array(
                'attr' => array('class' => 'btn gephebase-btn', 'form' => 'entry_form'),
                'label' => 'Save Entry',
            ))
            ->add('submit_and_next', 'submit', array(
                'attr' => array('class' => 'btn gephebase-btn', 'form' => 'entry_form'),
                'label' => 'Submit & Show next entry',
            ))
            ->add('submit_and_list', 'submit', array(
                'attr' => array('class' => 'btn gephebase-btn', 'form' => 'entry_form'),
                'label' => 'Submit & return to list',
            ))
            ->add('save_draft', 'submit', array(
                'attr' => array('class' => 'btn gephebase-local-btn', 'form' => 'entry_form'),
                'label' => 'Save Draft',
            ))
            ->add('review_submit', 'submit', array(
                'attr' => array('class' => 'btn btn-success', 'form' => 'entry_form'),
                'label' => 'Review & Submit',
            ))
            ->add('delete', 'submit', array(
                'attr' => array('class' => 'btn btn-danger', 'form' => 'entry_form'),
                'label' => 'Delete',
            ))
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $entry = $event->getData();
            $form = $event->getForm();

            $molecularOptions = array(
                'choices'=> Entry::getMolecularList(),
                'label' => 'Molecular Type',
                'expanded'=> true,
                'label_attr' => array('class' => 'gephebase-entry-label',),
            );

            $aberrationOptions = array(
                'choices' => Entry::getAberrationList(),
                'label' => 'Aberration Type',
                'expanded' => true,
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
            );

            $aInfraspeciesOptions = array(
                'choices' => Entry::getYesNoList(),
                'label' => 'Is Taxon A an Infraspecies?',
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'expanded' => true,
                'multiple' => false,
                'attr' => array('class'=>'infraspecies-trigger'),
            );

            $bInfraspeciesOptions = array(
                'choices' => Entry::getYesNoList(),
                'label' => 'Is Taxon B an Infraspecies?',
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'expanded' => true,
                'multiple' => false,
                'attr' => array('class'=>'infraspecies-trigger'),
            );


            // set default values when creating a new entry
            if (!$entry || null === $entry->getId()) {
                $molecularOptions['data'] = 'Coding';
                $aberrationOptions['data'] = 'SNP';
            }

            if ($entry && $entry->getIsTaxonAInfraspecies() === null) {
                $aInfraspeciesOptions['data'] = 0;
            }

            if ($entry && $entry->getIsTaxonBInfraspecies() === null) {
                $bInfraspeciesOptions['data'] = 0;
            }

            $form->add('molecularType', 'choice', $molecularOptions);
            $form->add('aberrationType', 'choice', $aberrationOptions);
            $form->add('isTaxonAInfraspecies', 'choice', $aInfraspeciesOptions);
            $form->add('isTaxonBInfraspecies', 'choice', $bInfraspeciesOptions);
        });
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Entry'
        ));
    }

    public function getName()
    {
        return 'entryComplex';
    }
}
