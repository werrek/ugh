<?php
/**
 * AppFixtures.
 */

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * App Fixtures.
 */
class AppFixtures extends Fixture
{
    /**
     * @param ObjectManager $manager param
     *
     * @return void return
     */
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
