<?php
/**
 * Contact Controller test.
 */

namespace App\Tests\Controller;

namespace Symfony\Bundle\FrameworkBundle\Test;

use App\Entity\Contact;
use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Repository\ContactRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Class ContactControllerTest.
 */
class ContactControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/contact';


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
        $this->createAndLoginUser("user_contact1@example.com");
        $expectedStatusCode = 301;


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
        $this->createAndLoginUser("user_contact2@example.com");
        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals(301, $resultStatusCode);
    }



    /**
     * Test show single contact.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testShowContact(): void
    {
        // given
        $this->createAndLoginUser("user_contact3@example.com");
        $expectedContact = new Contact();
        $expectedContact->setName('TestContact');
        $expectedContact->setSurname('TestContact');
        $expectedContact->setPhone("testPhone");
        $expectedContact->setAddress("testAddress");
        $contactRepository = static::getContainer()->get(ContactRepository::class);
        $contactRepository->save($expectedContact);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $expectedContact->getId());
        $result = $this->httpClient->getResponse();

        // then
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertSelectorTextContains('td', $expectedContact->getId());
        // ... more assertions...
    }

    //create contact

    /**
     * @throws OptimisticLockException
     * @throws NotFoundExceptionInterface
     * @throws ORMException
     * @throws ContainerExceptionInterface
     */
    public function testCreateContact(): void
    {
        // given
        $this->createAndLoginUser("user_contact4@example.com");
        $contactContactName = "createdContact";
        $contactRepository = static::getContainer()->get(ContactRepository::class);

        $this->httpClient->request('GET', self::TEST_ROUTE . '/new');
        // when
        $this->httpClient->submitForm(
            'Zapisz',
            ['contact' =>
                [   'name' => $contactContactName,
                    'surname' => $contactContactName,
                    'phone' =>$contactContactName,
                    'address' => $contactContactName
                ]
            ]
        );

        // then
        $savedContact = $contactRepository->findOneByName($contactContactName);
        $this->assertEquals(
            $contactContactName,
            $savedContact->getName()
        );


        $result = $this->httpClient->getResponse();
        $this->assertEquals(303, $result->getStatusCode());
    }

    /**
     * @return void
     */
    public function testEditContactUnauthorizedUser(): void
    {
        // given
        $expectedHttpStatusCode = 200;
        $this->createAndLoginUser("user_contact5@example.com");
        $contact = new Contact();
        $contact->setName('TestContact');
        $contact->setSurname('TestContact');
        $contact->setPhone("testPhone");
        $contact->setAddress("testAddress");
        $contactRepository =
            static::getContainer()->get(ContactRepository::class);
        $contactRepository->save($contact);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE . '/' .
            $contact->getId() . '/edit');
        $actual = $this->httpClient->getResponse();

        // then

        $this->assertEquals(
            $expectedHttpStatusCode,
            $actual->getStatusCode()
        );
    }


    /**
     * @return void
     */
    public function testEditContact(): void
    {
        // given
        $this->createAndLoginUser("user_contact6@example.com");

        $contactRepository =
            static::getContainer()->get(ContactRepository::class);
        $testContact = new Contact();
        $testContact->setName('TestContact');
        $testContact->setSurname('TestContact');
        $testContact->setPhone("new DateTime('now')");
        $testContact->setAddress("new DateTime('now')");
        $contactRepository->save($testContact);
        $testContactId = $testContact->getId();
        $expectedNewContactTitle = 'TestContactEdit';

        $this->httpClient->request('GET', self::TEST_ROUTE . '/' .
            $testContactId . '/edit');

        // when
        $this->httpClient->submitForm(
            'Edytuj',
            ['contact' =>
                [   'name' => $expectedNewContactTitle,
                'surname' => $expectedNewContactTitle,
                'phone' =>$expectedNewContactTitle,
                'address' => $expectedNewContactTitle
                ]
            ]
        );

        // then
        $savedContact = $contactRepository->findOneById($testContactId);
        $this->assertEquals(
            $expectedNewContactTitle,
            $savedContact->getName()
        );
    }


    /**
     * @return void
     */
    public function testNewRoutAdminUser(): void
    {
        $this->createAndLoginUser("user_contact7@example.com");
        $this->httpClient->request('GET', self::TEST_ROUTE . '/');
        $this->assertEquals(200, $this->httpClient->getResponse()->getStatusCode());
    }

    /**
     * @return void
     */
    public function testDeleteContact(): void
    {
        // given
        $user = null;
        $this->createAndLoginUser("user_contact8@example.com");

        $contactRepository =
            static::getContainer()->get(ContactRepository::class);
        $testContact = new Contact();
        $testContact->setAddress('TestContactCreated');
        $testContact->setPhone('TestContactCreated');
        $testContact->setSurname("new DateTime('now')");
        $testContact->setName("new DateTime('now')");
        $contactRepository->save($testContact);
        $testContactId = $testContact->getId();

        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $testContactId . '/delete');

        //when
        $this->httpClient->submitForm(
            'Usuń'
        );

        // then
        $this->assertNull($contactRepository->findOneByName('TestContactCreated'));
    }
}
