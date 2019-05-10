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

use AppBundle\Form\DataTransformer\TraitTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ComplexTraitType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('stateInTaxonA', 'textarea', array(
                'label' => 'Trait State in Taxon A',
                'label_attr' => array(
                    'class' => 'gephebase-entry-label count-element',
                    'data-prefix' => 'Trait',
                    'data-suffix' => 'State in Taxon A',
                ),
                'required' => false,
            ))
            ->add('stateInTaxonB', 'textarea', array(
                'label' => 'Trait State in Taxon B',
                'label_attr' => array(
                    'class' => 'gephebase-entry-label count-element',
                    'data-prefix' => 'Trait',
                    'data-suffix' => 'State in Taxon B',
                ),
                'required' => false,
            ))
            ->add('phenotypeTrait', 'phenotype_trait')
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\ComplexTrait'
        ));
    }

    public function getName()
    {
        return 'complex_trait';
    }
}
