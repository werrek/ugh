<?php
/**
 * Category Controller test.
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use App\{Entity\Category,
    Entity\Enum\UserRole,
    Entity\Event,
    Entity\User,
    Kernel,
    Repository\CategoryRepository,
    Repository\EventRepository,
    Repository\UserRepository};
use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;


/**
 * Class CategoryControllerTest.
 */
class CategoryControllerTest extends WebTestCase
{

    protected static function createKernel(array $options = []): Kernel
    {
        return new Kernel('test', true);
    }
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/category';

    /**
     * Test client.
     */
    private KernelBrowser $httpClient;

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
     */
    protected function logIn(User $user): void
    {
        $session = self::getContainer()->get('session');

        $firewallName = 'main';
        $firewallContext = 'main';

        $token = new UsernamePasswordToken($user, null, $firewallName, $user->getRoles());
        $session->set('_security_' . $firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->httpClient->getCookieJar()->set($cookie);
    }

    /**
     * Create user.
     *
     * @param string $email
     * @return Void User entity
     */
    protected function createAndLoginUser(string $email): Void
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
     *
     */
    public function testIndexRouteAdminUser(): void
    {
        // given
        $this->createAndLoginUser("user_category1@example.com");
        $expectedStatusCode = 200;


        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test index route for non-authorized user.
     *
     */
    public function testIndexRouteNonAuthorizedUser(): void
    {
        // given
        $this->createAndLoginUser("user_category2@example.com");
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
        $this->createAndLoginUser("user_category3@example.com");
        $expectedCategory = new Category();
        $expectedCategory->setTitle('Test category');
        $expectedCategory->setCreatedAt(new DateTime('now'));
        $expectedCategory->setUpdatedAt(new DateTime('now'));
        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($expectedCategory);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $expectedCategory->getId());
        $result = $this->httpClient->getResponse();

        // then
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertSelectorTextContains('td', $expectedCategory->getId());
    }

    //create category

    /**
     * @throws OptimisticLockException
     * @throws NotFoundExceptionInterface
     * @throws ORMException
     * @throws ContainerExceptionInterface
     */
    public function testCreateCategory(): void
    {
        // given
        $this->createAndLoginUser("user_category4@example.com");
        $categoryCategoryName = "createdCategor";
        $categoryRepository = static::getContainer()->get(CategoryRepository::class);

        $this->httpClient->request('GET', self::TEST_ROUTE . '/create');
        // when
        $this->httpClient->submitForm(
            'Zapisz',
            ['category' =>
                ['title' => $categoryCategoryName
                ]
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
     */
    public function testEditCategory(): void
    {
        // given
        $this->createAndLoginUser("user_category6@example.com");

        $categoryRepository =
            static::getContainer()->get(CategoryRepository::class);
        $testCategory = new Category();
        $testCategory->setTitle('TestCategory');
        $testCategory->setCreatedAt(new DateTime('now'));
        $testCategory->setUpdatedAt(new DateTime('now'));
        $categoryRepository->save($testCategory);
        $testCategoryId = $testCategory->getId();
        $expectedNewCategoryTitle = 'TestCategoryEdit';

        $this->httpClient->request('GET', self::TEST_ROUTE . '/' .
            $testCategoryId . '/edit');

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
     * @return void
     */
    public function testNewRoutAdminUser(): void
    {
        $this->createAndLoginUser("user_category7@example.com");
        $this->httpClient->request('GET', self::TEST_ROUTE . '/');
        $this->assertEquals(301, $this->httpClient->getResponse()->getStatusCode());
    }

    /**
     * @return void
     */
    public function testDeleteCategory(): void
    {
        // given
        $user = null;
        $this->createAndLoginUser("user_category8@example.com");

        $categoryRepository =
            static::getContainer()->get(CategoryRepository::class);

        $testCategory = new Category();
        $testCategory->setTitle('TestCategoryCreated');
        $testCategory->setCreatedAt(new DateTime('now'));
        $testCategory->setUpdatedAt(new DateTime('now'));
        $categoryRepository->save($testCategory);
        $testCategoryId = $testCategory->getId();

        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $testCategoryId . '/delete');

        //when
        $this->httpClient->submitForm(
            'Usuń'
        );

        // then
        $this->assertNull($categoryRepository->findOneById($testCategoryId));
    }

    /**
     * @return void
     */
    public function testCantDeleteCategory(): void
    {
        // given
        $this->createAndLoginUser("user_category9@example.com");

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

        //when
        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $testCategoryId . '/delete');

        // then
        $this->assertEquals(302, $this->httpClient->getResponse()->getStatusCode());
        $this->assertNotNull($categoryRepository->findOneByTitle('TestCategoryCreated2'));
    }

    /**
     * @param Category $category cokolwiek
     * @return void cokolwiek
     * @throws ContainerExceptionInterface cokolwiek
     * @throws NotFoundExceptionInterface cokolwiek
     */
    private function createEvent(Category $category)
    {
        $event = new Event();
        $event->setTitle("test title");
        $event->setCategory($category);
        $event->setPlace("test place");
        $event->setDate(new \DateTime('now'));

        $transactionRepository = self::getContainer()->get(EventRepository::class);
        $transactionRepository->save($event);
    }
}
