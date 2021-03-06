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

use AppBundle\Entity\Entry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaxonAType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('latinName', 'text', array(
                'label' => 'Latin Name',
                'label_attr' => array(
                    'class' => 'external-data-label',
                ),
                'required' => false,
                'attr' => array(
                    'readonly' => 'readonly',
                    'class' => 'fetched-data latin-name',
                ),
            ))
            ->add('commonName', 'text', array(
                'label' => 'Common Name',
                'label_attr' => array(
                    'class' => 'external-data-label',
                ),
                'required' => false,
                'attr' => array(
                    'readonly' => 'readonly',
                    'class' => 'fetched-data common-name',
                ),
            ))
            ->add('taxId', 'text', array(
                'label' => 'Taxon',
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'required' => false,
                'attr' => array(
                    'class' => 'fetched-data taxon-id',
                ),
            ))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Taxon'
        ));
    }

    public function getName()
    {
        return 'taxon_a';
    }
}