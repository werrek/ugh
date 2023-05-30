<?php
/**
 * User fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class UserFixtures.
 */
class UserFixtures extends AbstractBaseFixtures
{
    private UserPasswordHasherInterface $passwordHarsher;

    /**
     * UserFixtures constructor.
     *
     * @param UserPasswordHasherInterface $passwordHarsher hasr
     */
    public function __construct(UserPasswordHasherInterface $passwordHarsher)
    {
        $this->passwordHarsher = $passwordHarsher;
    }

    /**
     * Load data.
     *
     * @param ObjectManager $manager Persistence object manager
     */
    public function loadData(ObjectManager $manager): void
    {
        $this->createMany(1, 'users', function ($i) {
            $user = new User();
            $user->setEmail(sprintf('user%d@example.com', $i));
            $user->setRoles([UserRole::ROLE_USER, UserRole::ROLE_ADMIN]);
            $user->setPassword(
                $this->passwordHarsher->hashPassword(
                    $user,
                    'admin1234'
                )
            );

            return $user;
        });

        $manager->flush();
    }
}
