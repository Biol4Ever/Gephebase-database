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

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AppBundle\Entity\User;
use AppBundle\Entity\OldPassword;

class UserFixture implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->createUser();
        $user->setId(1);
        $user->setPlainPassword("admin");
        $user->setUsername("admin");
        $user->setEmail("admin");
        $user->setSurname("admin");
        $user->setName("admin");
        $user->setEnabled(true);
        $user->addRole('ROLE_CURATOR');
        $user->addRole('ROLE_ADMIN');
        $userManager->updateUser($user);
                
        $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
        $oldPassword = $encoder->encodePassword("admin", $user->getSalt());

        $oP = new OldPassword();
        $oP->setName($oldPassword);
        $oP->setUser($user);
        $manager->persist($oP);
        $metadata = $manager->getClassMetaData(get_class($user));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 3;
    }
}