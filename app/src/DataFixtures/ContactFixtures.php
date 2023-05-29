<?php
/**
 * Category fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Contact;
use Doctrine\Persistence\ObjectManager;

/**
 * Class CategoryFixtures.
 */
class ContactFixtures extends AbstractBaseFixtures
{
    /**
     * Load data.
     *
     * @param ObjectManager $manager Persistence object manager
     */
    public function loadData(ObjectManager $manager): void
    {
        $this->createMany(20, 'contact', function ($i) {
            $contact = new Contact();
            $contact->setName($this->faker->word);
            $contact->setAddress($this->faker->word);
            $contact->setPhone($this->faker->word);
            $contact->setSurname($this->faker->word);

            return $contact;
        });

        $manager->flush();
    }
}
