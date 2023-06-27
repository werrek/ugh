<?php
/**
 * Category Controller test.
 */

namespace App\Tests\Controller;

namespace Symfony\Bundle\FrameworkBundle\Test;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\Event;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Class CategoryControllerTest.
 */
class CategoryControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/category';

    /**
     * Set up tests.
     */
    public function setUp(): void
    {
        $this->httpClient = static::createClient();
    }

    /**
     * Simulate user log in.
     *
     * @param User $user User entity
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function logIn(User $user): void
    {
        $session = self::getContainer()->get('session');

        $firewallName = 'main';
        $firewallContext = 'main';

        $token = new UsernamePasswordToken($user, null, $firewallName, $user->getRoles());
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->httpClient->getCookieJar()->set($cookie);
    }

    /**
     * Create user.
     *
     * @return void User entity
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function createAndLoginUser(string $email): void
    {
        try {
            $passwordHasher = static::getContainer()->get('security.password_hasher');
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
        }
        $user = new User();
        $user->setEmail($email);
        $user->setRoles([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value]);
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                'p@55w0rd'
            )
        );
        $userRepository = null;
        try {
            $userRepository = static::getContainer()->get(UserRepository::class);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
        }
        $userRepository->save($user);
        $this->logIn($user);
    }

    /**
     * Test index route for admin user.
     */
    public function testIndexRouteAdminUser(): void
    {
        // given
        $this->createAndLoginUser('user_category1@example.com');
        $expectedStatusCode = 200;

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test index route for non-authorized user.
     */
    public function testIndexRouteNonAuthorizedUser(): void
    {
        // given
        $this->createAndLoginUser('user_category2@example.com');
        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals(200, $resultStatusCode);
    }

    /**
     * Test show single category.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testShowCategory(): void
    {
        // given
        $this->createAndLoginUser('user_category3@example.com');
        $expectedCategory = new Category();
        $expectedCategory->setTitle('Test category');
        $expectedCategory->setCreatedAt(new DateTime('now'));
        $expectedCategory->setUpdatedAt(new DateTime('now'));
        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($expectedCategory);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$expectedCategory->getId());
        $result = $this->httpClient->getResponse();

        // then
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertSelectorTextContains('td', $expectedCategory->getId());
    }

    // create category

    /**
     * @throws OptimisticLockException
     * @throws NotFoundExceptionInterface
     * @throws ORMException
     * @throws ContainerExceptionInterface
     */
    public function testCreateCategory(): void
    {
        // given
        $this->createAndLoginUser('user_category4@example.com');
        $categoryCategoryName = 'createdCategor';
        $categoryRepository = static::getContainer()->get(CategoryRepository::class);

        $this->httpClient->request('GET', self::TEST_ROUTE.'/create');
        // when
        $this->httpClient->submitForm(
            'Zapisz',
            ['category' => ['title' => $categoryCategoryName,
                ],
            ]
        );

        // then
        $savedCategory = $categoryRepository->findOneByTitle($categoryCategoryName);
        $this->assertEquals(
            $categoryCategoryName,
            $savedCategory->getTitle()
        );

        $result = $this->httpClient->getResponse();
        $this->assertEquals(302, $result->getStatusCode());
    }

    /**
     * @return void return
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testEditCategory(): void
    {
        // given
        $this->createAndLoginUser('user_category6@example.com');

        $categoryRepository =
            static::getContainer()->get(CategoryRepository::class);
        $testCategory = new Category();
        $testCategory->setTitle('TestCategory');
        $testCategory->setCreatedAt(new DateTime('now'));
        $testCategory->setUpdatedAt(new DateTime('now'));
        $categoryRepository->save($testCategory);
        $testCategoryId = $testCategory->getId();
        $expectedNewCategoryTitle = 'TestCategoryEdit';

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.
            $testCategoryId.'/edit');

        // when
        $this->httpClient->submitForm(
            'Edytuj',
            ['category' => ['title' => $expectedNewCategoryTitle]]
        );

        // then
        $savedCategory = $categoryRepository->findOneById($testCategoryId);
        $this->assertEquals(
            $expectedNewCategoryTitle,
            $savedCategory->getTitle()
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testNewRoutAdminUser(): void
    {
        $this->createAndLoginUser('user_category7@example.com');
        $this->httpClient->request('GET', self::TEST_ROUTE.'/');
        $this->assertEquals(301, $this->httpClient->getResponse()->getStatusCode());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testDeleteCategory(): void
    {
        // given
        $user = null;
        $this->createAndLoginUser('user_category8@example.com');

        $categoryRepository =
            static::getContainer()->get(CategoryRepository::class);

        $testCategory = new Category();
        $testCategory->setTitle('TestCategoryCreated');
        $testCategory->setCreatedAt(new DateTime('now'));
        $testCategory->setUpdatedAt(new DateTime('now'));
        $categoryRepository->save($testCategory);
        $testCategoryId = $testCategory->getId();

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$testCategoryId.'/delete');

        // when
        $this->httpClient->submitForm(
            'Usuń'
        );

        // then
        $this->assertNull($categoryRepository->findOneById($testCategoryId));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCantDeleteCategory(): void
    {
        // given
        $this->createAndLoginUser('user_category9@example.com');

        $categoryRepository =
            static::getContainer()->get(CategoryRepository::class);
        $testCategory = new Category();
        $testCategory->setTitle('TestCategoryCreated2');
        $testCategory->setUpdatedAt(new DateTime('now'));
        $testCategory->setCreatedAt(new DateTime('now'));
        $categoryRepository->save($testCategory);
        $testCategoryId = $testCategory->getId();

        try {
            $this->createEvent($testCategory);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
        }

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$testCategoryId.'/delete');

        // then
        $this->assertEquals(302, $this->httpClient->getResponse()->getStatusCode());
        $this->assertNotNull($categoryRepository->findOneByTitle('TestCategoryCreated2'));
    }

    /**
     * @param Category $category cokolwiek
     *
     * @return void cokolwiek
     *
     * @throws ContainerExceptionInterface cokolwiek
     * @throws NotFoundExceptionInterface  cokolwiek
     */
    private function createEvent(Category $category): void
    {
        $event = new Event();
        $event->setTitle('test title');
        $event->setCategory($category);
        $event->setPlace('test place');
        $event->setDate(new DateTime('now'));

        $transactionRepository = self::getContainer()->get(EventRepository::class);
        $transactionRepository->save($event);
    }
}
