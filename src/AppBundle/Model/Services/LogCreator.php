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

namespace AppBundle\Model\Services;

use AppBundle\Entity\Log;
use AppBundle\Entity\Parameter;
use AppBundle\Entity\ErrorLog;

class LogCreator
{
	private $em;
	private $user;

	public function __construct($em, $securityContext)
	{
		if($securityContext->getToken() != null) {
			$this->user = $securityContext->getToken()->getUser();
		}
		$this->em = $em;
	}

	/**
	 * Creates a log based on an error message.
	 * The user will default to current user and date to now
	 */
	public function createGenericErrorLog($errorMessage, $user=null, $date=null)
	{
		if (!$user) {
			if ($this->user) {
				$user = $this->user->getUsername();
			} else {
				$user = 'Console Command';
			}
		}

		if (!$date) {
			$date = new \DateTime("now");
		}

		$log = new ErrorLog();
		$log->setLogin($user);
		$log->setDate($date);
		$log->setName($errorMessage);

		$this->em->persist($log);
		$this->em->flush();
	}

	function createLog($entry, $name, $description) {
		$em = $this->em;
		$log = new Log();
		$log->setUser($this->user);
		$log->setDate(new \DateTime("now"));
		$log->setDescription($description);
		$log->setName($name);
		$log->setEntry($entry);
		$em->persist($log);


		$parameter = $em->getRepository('AppBundle:Parameter')->find(1);
		$parameter->setLastEntry(new \DateTime("now"));
		$journalYear = $em->getRepository('AppBundle:Reference')->lastYearOfReference();
		if (isset($journalYear[0])) {
			$parameter->setLastReference($journalYear[0]['journalYear']);
		}
		$em->persist($parameter);
		$em->flush();

		return true;
	}

	function createLogError($login, $error, $date) {
		$em = $this->em;
		$log = new ErrorLog();
		$log->setLogin($login);
		$log->setDate($date);
		$log->setName($error);
		$em->persist($log);

		$em->flush();
		return true;
	}

	function CreateLogCommand($entry, $name, $description) {
		$em = $this->em;
		$log = new Log();
		$user = $em->getRepository('AppBundle:User')->find(1);
		$log->setUser($user);
		$log->setDate(new \DateTime("now"));
		$log->setDescription($description);
		$log->setName($name);
		$log->setEntry($entry);
		$em->persist($log);


		$parameter = $em->getRepository('AppBundle:Parameter')->find(1);
		$parameter->setLastEntry(new \DateTime("now"));
		$journalYear = $em->getRepository('AppBundle:Reference')->lastYearOfReference();
		if (isset($journalYear[0])) {
			$parameter->setLastReference($journalYear[0]['journalYear']);
		}
		$em->persist($parameter);
		$em->flush();

		return true;
	}
}