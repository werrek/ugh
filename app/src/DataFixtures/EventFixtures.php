<?php
/**
 * Category fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Event;
use Doctrine\Persistence\ObjectManager;

/**
 * Class CategoryFixtures.
 */
class EventFixtures extends AbstractBaseFixtures
{
    /**
     * Load data.
     *
     * @param ObjectManager $manager Persistence object manager
     */
    public function loadData(ObjectManager $manager): void
    {
        $this->createMany(40, 'event', function ($i) {
            $event = new Event();
            $event->setTitle($this->faker->word);
            $event->setCategory($this->getRandomReference('categories'));
            $event->setDate($this->faker->dateTimeBetween('-1 days', '+7 days'));
            $event->setPlace($this->faker->word);

            return $event;
        });

        $manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return array Array of dependencies
     */
    public function getDependencies(): array
    {
        return [Category::class];
    }
}
