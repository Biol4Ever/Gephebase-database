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

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SendMailCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('send:mail')
            ->setDescription('Send a mail to all validators')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $userManager = $this->getContainer()->get('fos_user.user_manager');
        $entries = $em->getRepository('AppBundle:Entry')->validatorsByEntries();
        $parameter = $em->getRepository('AppBundle:Parameter')->find(1);
        $router = $this->getContainer()->get('router');
        $this->getContainer()->enterScope('request');       
        $this->getContainer()->set('request', new \Symfony\Component\HttpFoundation\Request(), 'request');
        $lastuser = null;
        $servername = $this->getContainer()->getParameter('server_name');

        foreach($entries as $entry) {
            $users = $entry->getValidators();

            foreach ($users as $user) {
                if($user != $lastuser) {
                    $url = $router->generate('authenticate_validator', array('token' => $user->getToken()), true);

                    if( ( strpos('http://', $url) === false || strpos('https://', $url) === false ) && $servername !== null ) {
                        $url = $url;
                    }

                    $body = "<html><body><p>Dear colleague, <br /><br /> Are you interested in the genes and mutations that drive  phenotypic change? Our database ( ".$servername.") compiles from the literature our extensive knowledge of the genetic changes that have been linked to phenotypic variation in animals, plants and yeasts. Gephebase is thus an extensive catalogue of genetic evolution that covers all eukaryotes, including cases of domestication and experimental evolution in addition to natural cases. <br /><br /> Your research caught our attention and we included your findings into Gephebase. Please click on the link below to review the data from your work that we included in Gephebase: <br /><br /> <a href=".$url.">Access to the entries</a><br />".$url." <br /> It should only take a few minutes of your time. Your contribution to Gephebase will be of great help for community-based exploration of the relationship between genotype and phenotype, and will give a durable exposure to your work. <br /><br /> If you think that you are not the right person to review this data in Gephebase, please send us the email addresses of co-authors or colleagues with the relevant expertise. <br /><br /> Thank you so much, <br /><br /> The Gephebase Team <br /><br /> Arnaud Martin, Assistant Professor at the George Washington University (Washington, DC) <br /><br /> Virginie Courtier-Orgogozo, Directrice de Recherche CNRS at the Institut Jacques Monod (Paris)</p></body></html>";

                    $bodyText = "Dear colleague, \n \n Are you interested in the genes and mutations that drive  phenotypic change? Our database ( ".$servername.") compiles from the literature our extensive knowledge of the genetic changes that have been linked to phenotypic variation in animals, plants and yeasts. Gephebase is thus an extensive catalogue of genetic evolution that covers all eukaryotes, including cases of domestication and experimental evolution in addition to natural cases. \n \n Your research caught our attention and we included your findings into Gephebase. Please click on the link below to review the data from your work that we included in Gephebase: \n \n Access to the entries : ".$url." \n \n It should only take a few minutes of your time. Your contribution to Gephebase will be of great help for community-based exploration of the relationship between genotype and phenotype, and will give a durable exposure to your work. \n \n If you think that you are not the right person to review this data in Gephebase, please send us the email addresses of co-authors or colleagues with the relevant expertise. \n \n Thank you so much, \n \n The Gephebase Team \n \n Arnaud Martin, Assistant Professor at the George Washington University (Washington, DC) \n \n Virginie Courtier-Orgogozo, Directrice de Recherche CNRS at the Institut Jacques Monod (Paris)
";

                    $message = \Swift_Message::newInstance()
                        ->setSubject($parameter->getSubject())
                        ->setFrom($parameter->getFromMail())
                        ->setTo($user->getEmail())
                        ->setBody($body, 'text/html')
                        ->addPart($bodyText,'text/plain')
                    ;
                    $this->getContainer()->get('mailer')->send($message);
                    $lastuser = $user;
                }
            }

            $entry->setDateEmail(new \DateTime());
            $em->persist($entry);
        }
        $em->flush();

    }
}
