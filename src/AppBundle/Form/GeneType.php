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

use AppBundle\Form\DataTransformer\GeneSynonymsToListTransformer;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Go;

class GeneType extends AbstractType
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'label' => 'Generic Gene Name',
                'label_attr' => array(
                    'class' => 'external-data-label',
                ),
                'required' => false,
                'attr' => array(
                    'readonly' => true,
                    'class' => 'fetched-data',
                ),
            ))
            ->add('uniProtKbId', 'text', array(
                'label' => 'UniProtKB',
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'required' => false,
                'attr' => array(
                    'class' => 'fetched-data load-uniprot',
                ),
            ))
            ->add('organism', 'text', array(
                'label' => 'Model Organism',
                'label_attr' => array(
                    'class' => 'external-data-label',
                ),
                'required' => false,
                'attr' => array(
                    'readonly' => 'readonly',
                    'class' => 'fetched-data',
                ),
            ))
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $gene = $event->getData();
            $form = $event->getForm();

            $form->add('synonyms', 'synonym_list', array(
                'invalid_message' => 'Invalid textarea format. Synonyms must be semi-colon seperated.',
                'gene' => $gene,
                'label_attr' => array(
                    'class' => 'external-data-label',
                ),
                'required' => false,
                'attr' => array(
                    'class' => 'fetched-data',
                    'readonly' => 'readonly',
                    'rows' => '5',
                ),
            ));
            $form->add('goMolecular', 'go_area', array(
                'label' => 'Molecular Function',
                'label_attr' => array(
                    'class' => 'external-data-label',
                    'readonly' => 'readonly',
                ),
                'required' => false,
                'invalid_message' => 'Invalid text format.',
                'go_category' => Go::CATEGORY_MOLECULAR,
                'gene' => $gene,
                'attr' => array(
                    'class' => 'fetched-data',
                    'readonly' => 'readonly',
                    'rows' => '2',
                ),
            ));
            $form->add('goBiological', 'go_area', array(
                'label' => 'Biological Process',
                'label_attr' => array(
                    'class' => 'external-data-label',
                    'readonly' => 'readonly',
                    'rows' => '2',
                ),
                'required' => false,
                'invalid_message' => 'Invalid text format.',
                'go_category' => Go::CATEGORY_BIOLOGICAL,
                'gene' => $gene,
                'attr' => array(
                    'class' => 'fetched-data',
                    'readonly' => 'readonly',
                ),
            ));
            $form->add('goCellular', 'go_area', array(
                'label' => 'Cellular Component',
                'label_attr' => array(
                    'class' => 'external-data-label',
                    'readonly' => 'readonly',
                ),
                'required' => false,
                'invalid_message' => 'Invalid text format.',
                'go_category' => Go::CATEGORY_CELLULAR,
                'gene' => $gene,
                'attr' => array(
                    'class' => 'fetched-data',
                    'readonly' => 'readonly',
                    'rows' => '2',
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
            'data_class' => 'AppBundle\Entity\Gene'
        ));
    }

    public function getName()
    {
        return 'gene';
    }
}
