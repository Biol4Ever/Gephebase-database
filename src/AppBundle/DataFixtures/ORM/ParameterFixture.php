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

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Parameter;

class ParameterFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {

        $entity = new Parameter();
        $entity->setId(1);
        $entity->setContact("contact@mail.com");
        $entity->setContactValidator("contactValidator@mail.com");
        $entity->setHomepageDescription("<p style='text-align: justify; font-size: 16px;'>Gephebase compiles genotype-phenotype relationships, i.e. associations between a mutation and a
phenotypic variation. Gephebase consolidates data from the scientific literature about the genes and
the mutations responsible for phenotypic variation in Eukaryotes (mostly animals, yeasts and plants).
We plan to include non Eukaryote species in the future. For now, genes responsible for human
disease and for aberrant mutant phenotypes in laboratory model organisms are excluded and can be
found in other databases (<a href='http://www.omim.org' target='_blank'>OMIM</a>, <a target='_blank' href='http://omia.angis.org.au/home/'>OMIA</a>, <a target='_blank' href='http://www.flybase.org'>FlyBase</a>, etc.). QTL mapping studies that did not identify
single genes are not included in Gephebase.<br />
If you use Gephebase for your publication, please cite: <a target='_blank' href='http://onlinelibrary.wiley.com/doi/10.1111/evo.12081/full'>Martin, A., & Orgogozo, V. (2013). The loci
of repeated evolution: a catalog of genetic hotspots of phenotypic variation. Evolution, 67(5), 1235-
1250.</a></p>");
        $entity->setWhatsNew("<p style='text-align: center; font-size: 16px;'><b>Gephebase was launched on May 2016 and it already contains more than 1000 entries! On 5-7 September 2016 in
Paris will occur <a target='_blank' href='http://www.normalesup.org/~vorgogoz/gephebase-conference.html'>our conference on Gephebase and the loci of evolution.</a></b></p>");
        $entity->setBase("base.html.twig");
        $entity->setLastEntry(new \DateTime("2001-01-01"));
        $entity->setLastReference("2000");
        $entity->setSubject("Gephebase - Validation request");
        $entity->setFromMail("fromMail@mail.com");
        $entity->setLoginMail("login@mail.com");
        $entity->setGreetings("<h3>The identification of the genetic basis of phenotypic variation is accelerating at a fast pace, and we developed Gephebase to allow a better integration of this important body of knowledge. Explore it, share it, and do not hesitate to get involved in its analysis. We welcome your feedback and comments, and thank you for your precious time!</h3>
<h3>The Gephebase Team :<br /> - Arnaud Martin, Assistant Professor at the George Washington University (Washington, DC)<br /> - Virginie Orgogozo, Directrice de Recherche CNRS at the Institut Jacques Monod (Paris, France)</h3>");
        $entity->setMail("mail");
        $manager->persist($entity);
        $metadata = $manager->getClassMetaData(get_class($entity));
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
        return 2;
    }
}