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

namespace AppBundle\Model\Page;

use AppBundle\Entity\Page;

/**
 * Manages Pages editable by curators and viewable in the frontend. A page is composed of a key identifying it, a name and contents.
 */
class PageManager
{
	const FAQ = 'faq';
    const TEAM = 'team';
    const DOCUMENTATION = 'documentation';
    const EVENTS = 'events';

	private $em;

	public function __construct($em)
	{
		$this->em = $em;
	}

	/**
	 * Looks for a page by key, creates it if it doesn't exist.
	 */
	public function findOrCreatePage($key)
	{
		$page = $this->em->getRepository('AppBundle:Page')->findOneByPageKey($key);

		if (!$page) {
			$page = new Page();
			$page->setPageKey($key);
			$page->setName(ucfirst($key));

			$this->persist($page);
		}

		return $page;
	}

	/**
	 * Persists a page entity
	 */
	public function persist($page, $andFlush = true)
	{
		$this->em->persist($page);

		if ($andFlush) {
			$this->em->flush($page);
		}
	}
}