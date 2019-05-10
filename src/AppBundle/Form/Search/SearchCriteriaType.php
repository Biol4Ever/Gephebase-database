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


namespace AppBundle\Form\Search;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use AppBundle\Entity\Entry;

class SearchCriteriaType extends AbstractType
{

    private $sc;

    public function __construct($sc)
    {
        $this->sc = $sc;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $operatorChoices = array('and' => 'AND', 'not' => 'AND NOT', 'or' => 'OR');

        $builder
            ->add('operator', 'choice', array(
                'choices' => $operatorChoices,
            ))
            ->add('field', 'choice', array(
                'choices' => Entry::getCriteriaList(),
                'attr' => array('class' => 'field-choice'),
            ))
            ->add('term', 'text', array(
                'label' => false,
                'attr' => array('class' => 'field-term main-field-term '),
            ))
            ->add('term2', 'text', array(
                'label' => false,
                'attr' => array('class' => 'field-term '),
                'required' => false,
            ))
        ;

        $formModifier = function (FormInterface $form, $field = null, $curator = false) {
            $listFields = array(
                1 => 'getStatusList',
                7 => 'getTraitCategoryList',
                10 => 'getTaxonomicList',
                11 => 'getExperimentalList',
                13 => 'getMolecularList',
                14 => 'getSNPList',
                15 => 'getAberrationList',
                26 => 'getPresumptiveNullList',
                31 => 'getAberrationSizeList',
            );
            if($curator === false) {
                $listFields[1] = 'getStatusUserList';
            }
            if ($field && array_key_exists($field, $listFields)) {
                $listGetter = $listFields[$field];
                $form->add('term', 'choice', array(
                    'label' => false,
                    'choices' => Entry::$listGetter(),
                    'attr' => array('class' => 'field-term main-field-term')
                ));
            } else {
                $form->add('term', 'text', array(
                    'label' => false,
                    'attr' => array('class' => 'field-term main-field-term')
                ));
            }
        };

        $builder->get('field')->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $sc = $this->sc;
                $field = $event->getForm()->getData();
                if($sc->isGranted('ROLE_CURATOR')) {
                    $formModifier($event->getForm()->getParent(), $field, true);
                } else {
                    $formModifier($event->getForm()->getParent(), $field, false);
                }
            }
        );


        $builder->get('field')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $sc = $this->sc;
                $field = $event->getForm()->getData();

                if($sc->isGranted('ROLE_CURATOR')) {
                    $formModifier($event->getForm()->getParent(), $field, true);
                } else {
                    $formModifier($event->getForm()->getParent(), $field, false);
                }
            }
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Model\Search\SearchCriteria'
        ));
    }

    public function getName()
    {
        return 'searchCriteria';
    }
}
