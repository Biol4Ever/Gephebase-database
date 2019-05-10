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


namespace AppBundle;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppBundle extends Bundle
{
	public function boot()
    {
    	$em = $this->container->get('doctrine.orm.entity_manager');
        $parameters = $em->getRepository('AppBundle:Parameter')->find(1);
        $this->container->get('twig')->addGlobal('base', $parameters->getBase());
    }
}
