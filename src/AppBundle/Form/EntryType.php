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

class EntryType extends AbstractType
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
            ->add('status', new StatusType(), array())

            ->add('traits', 'collection', array(
                'type' => new ComplexTraitType(),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ))
            ->add('taxonAList', 'collection', array(
                'type' => new ComplexTaxonAType(),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ))
            ->add('taxonBList', 'collection', array(
                'type' => new ComplexTaxonBType(),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ))
            ->add('mutations', 'collection', array(
                'type' => new MutationType(),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ))

            /* Gephebaase Summary fields */
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
            ->add('addOtherValidator', 'text', array(
                'mapped' => false,
                'required' => false,
                'attr' => array(
                    'class' => 'reviewer',
                ),
            ))
            /*->add('validator', new AddReviewerType(), array())*/
            ->add('validators', 'collection', array(
                'type' => new AddReviewerType(),
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
            ->add('comments', 'textarea', array(
                'label' => 'Comments',
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'required' => false,
            ))
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
            /*->add('saveEntry', 'submit', array(
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
            ))*/
            ->add('submit_and_next', 'submit', array(
                'attr' => array('class' => 'btn btn-info', 'form' => 'entry_form'),
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
            ->add('save_draft_menu', 'submit', array(
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
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Entry',
            'complex' => false,
        ));
    }

    public function getName()
    {
        return 'entry';
    }
}
