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

use AppBundle\Form\DataTransformer\TraitCategoryTransformer;
use AppBundle\Form\DataTransformer\TraitTransformer;
use Symfony\Component\Form\AbstractType;
use AppBundle\Entity\Entry;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManager;

class PhenotypeTraitType extends AbstractType
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', 'text', array(
                'label' => 'Trait',
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'attr' => array(
                    'class' => 'trait-description',
                ),
                'required' => false,
            ))
            ->add('category', 'choice', array(
                'choices' => Entry::getTraitCategoryList(),
                'multiple' => true,
                'expanded' => true,
                'label' => 'Trait Category',
                'label_attr' => array(
                    'class' => 'gephebase-entry-select gephebase-entry-label'
                ),
                'attr' => array(
                    'class' => 'gephe-choice-list',
                ),
                'invalid_message' => 'Invalid checkboxes format. Categories must be an array.',
            ))
            ->get('category')->addModelTransformer(new TraitCategoryTransformer($this->em))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\PhenotypeTrait'
        ));
    }

    public function getName()
    {
        return 'phenotype_trait';
    }
}
