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

class GeneGephebaseSeeAllType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gepheId', 'text', array(
                    'label' => 'GepheID',
                    'label_attr' => array(
                        'class' => 'gephebase-entry-label',
                    ),
                    'required'=> false,
            ))
            ->add('geneGephebase', 'text', array(
                    'label' => 'Gene-Gephebase',
                    'label_attr' => array(
                        'class' => 'gephebase-entry-label',
                    ),
                    'required'=> false,
            ))
            ->add('taxonADescription', 'text', array(
                    'label' => 'Taxon A',
                    'label_attr' => array(
                        'class' => 'gephebase-entry-label',
                    ),
                    'required'=> false,
            ))
            ->add('taxonBDescription', 'text', array(
                    'label' => 'Taxon B',
                    'label_attr' => array(
                        'class' => 'gephebase-entry-label',
                    ),
                    'required'=> false,
            ))
            ->add('gene', 'gene')
            ->add('phenotypeTrait', 'phenotype_trait')
        ; 
    }

    public function getName()
    {
        return 'geneGephebaseSeeAll';
    }
}
