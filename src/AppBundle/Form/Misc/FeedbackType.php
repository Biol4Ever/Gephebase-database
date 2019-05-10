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


namespace AppBundle\Form\Misc;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use AppBundle\Validator\Constraints\RecaptchaIsTrue;


class FeedbackType extends AbstractType
{
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($this->router->generate('feedback_submit'))
            ->add('feedback', 'textarea', array(
                'label' => 'Feedback',
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'required'=> true,
            ))
            ->add('email', 'email', array(
                    'label' => 'Your E-mail',
                    'label_attr' => array(
                        'class' => 'gephebase-entry-label',
                    ),
                    'required'=> false,
            ))
            ->add('recaptcha', 'ewz_recaptcha', array(
                'attr'        => array(
                    'options' => array(
                        'theme' => 'light',
                        'type'  => 'image',
                        'size'  => 'normal'
                    )
                ),
                'label_attr' => array(
                    'class' => 'gephebase-entry-label',
                ),
                'constraints' => array(
                    new RecaptchaIsTrue(),
                ),
                'mapped'      => false,
            ))
            ->add('submit', 'submit', array(
                'attr' => array('class' => 'btn gephebase-btn'),
                'label' => 'Send my Feedback',
            ))
            ->add('entry', 'entity', array(
                'class' => 'AppBundle:Entry',
                'choice_label' => 'gepheId',
            ))
        ; 
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Feedback',
            'validation_groups' => 'feedback',
        ));
    }

    public function getName()
    {
        return 'feedback';
    }
}
