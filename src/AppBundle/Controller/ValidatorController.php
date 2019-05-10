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


namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use AppBundle\Entity\Entry;
use AppBundle\Entity\EntryStatus;

class ValidatorController extends Controller
{
	/** 
     * @Route("/validator/preview-validator", name="preview_validator")
     */
    public function previewAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
    	$user = $this->getUser();
        $entries = $em->getRepository('AppBundle:Entry')->listValidatorEntries($user);

        return $this->render('validator/validatorEntries.html.twig', array(
        	'entries' => $entries,
        ));
   	}

   	/** 
     * @Route("/authenticate-validator/{token}", name="authenticate_validator")
     */
   	public function authenticateValidatorAction($token)
	{
		$userManager = $this->container->get('fos_user.user_manager');
	    $user = $userManager->findUserBy(array('token' => $token));

    	if (!$user) {
   	    	throw $this->createNotFoundException('No user found!');
    	}

    	$token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());

    	$context = $this->container->get('security.context');
    	$context->setToken($token);

    	$router = $this->get('router');
	    $url = $router->generate('preview_validator');
	
    	return $this->redirect($url);
	}

	/**
     * @Route("/validator/edit-entry-validator/{id}", defaults={"id" = 0}, name="edit_entry_validator")
     */
    public function editEntryValidatorAction(Request $request, $id, $info_message = null)
    {
        $em = $this->getDoctrine()->getManager();
        $entry = $em->getRepository('AppBundle:Entry')->findOneById($id);
        if($entry == null) {
            return $this->redirectToRoute('preview_validator', array(
            	'info_message' => 'No entry found.',
            ));
        }

        $form = $this->createForm('validateEntry');
        $form->handleRequest($request);

        if ($form->isValid()) {
        	if($form['validate']->getData()) {
        		$accepted = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::ACCEPTED_VALIDATOR);
            	$entry->setStatus($accepted);
        	} else {
        		$refused = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::REFUSED_VALIDATOR);
            	$entry->setStatus($refused);
        	}
        	$entry->setCommentsValidator($form['comments']->getData());
        	$entry->setTempDateValidation(NULL);

            $em->persist($entry);
            $em->flush();

            $validator = $this->getUser();
            $entries = $em->getRepository('AppBundle:Entry')->listValidatorEntries($validator);
        	$router = $this->get('router');

        	$parameter = $em->getRepository('AppBundle:Parameter')->findOneById(1);

            $mail = $parameter->getContactValidator();
	        $validator = $this->getUser();
        	$url = $this->getRequest()->getScheme()."://";
        	$url .= $this->getRequest()->getHost();
        	$url .= $router->generate('view_entry', array('id' => $entry->getId()));

	        $body = "<html><body> 
					Dear Administrator,<br /><br />
					A new (un)validation has been done.<br /><br />
					First Name: ".$validator->getSurname()."<br /><br />
					Last Name: ".$validator->getName()."<br /><br />
					E-mail: ".$validator->getEmail()."<br /><br />
				";

			$body .= "
						Gephe ID : <a href='".$url."'>".$entry->getGepheId()."</a><br /><br />
						Validation of entry : ".$entry->getStatus()->getName()."<br /><br /> 
						Comments : ".$entry->getCommentsValidator()."<br /><br />
					 </body></html>";

            $body_text = "
                    Dear Administrator, \n \n
                    A new (un)validation has been done. \n \n
                    First Name: ".$validator->getSurname()." \n \n
                    Last Name: ".$validator->getName()." \n \n
                    E-mail: ".$validator->getEmail()." \n \n ";

            $body_text .= "
                        Gephe ID : ".$entry->getGepheId()." \n \n
                        Validation of entry : ".$entry->getStatus()->getName()." \n \n
                        Comments : ".$entry->getCommentsValidator()." \n \n ";
	
	
            $message = \Swift_Message::newInstance()
            	->setSubject('GEPHEBASE - new (un)validation')
            	->setFrom($parameter->getFromMail())
            	->setTo($mail)
            	->setBody($body, 'text/html')
                ->addPart($body_text, 'text/plain')
        	;
        	$this->get('mailer')->send($message);

            if(count($entries) == 0) { // So it's the last one

        		$userManager = $this->container->get('fos_user.user_manager');
                if(!$validator->hasRole('ROLE_CURATOR')) {
                    $validator->setEnabled(false);
                }
            	$userManager->updateUser($validator);

            	$this->get('security.token_storage')->setToken(null);
				$this->get('request')->getSession()->invalidate();
	
        		return $this->render('validator/greetings.html.twig', array('parameter' => $parameter));
            } else {
            	$url = $router->generate('edit_entry_validator', array('id' => $entries[0]->getId(),'info_message' => $info_message ));
            	return $this->redirect($url);
            }
        }
        return $this->render('validator/viewValidator.html.twig', array(
            'form' => $form->createView(),
            'id' => $id,
            'entry' => $entry,
            'info_message' => $info_message,
            'traitList' => Entry::getTraitCategoryList(),
            'experimentalList' => Entry::getExperimentalList(),
            'ancestralList' => Entry::getAncestralList(),
        ));
   	}
}
