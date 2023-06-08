<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\DBAL\TransactionIsolationLevel;
use Doctrine\ORM\EntityManagerInterface;

class RegistrationTest extends WebTestCase
{
    private static ?EntityManagerInterface $entityManager = null;
    private static ?KernelBrowser $client = null;

    protected function setUp(): void
    {
        if (null === self::$client) {
            self::$client = static::createClient();
        }
        if(null === self::$entityManager){
            self::$entityManager = self::$client->getContainer()
                ->get('doctrine')
                ->getManager();
        }

        self::$entityManager->getConnection()->beginTransaction();
        self::$entityManager->getConnection()->setTransactionIsolation(TransactionIsolationLevel::READ_UNCOMMITTED);

        $tables = ['user'];
        foreach ($tables as $table) {
            self::$entityManager->getConnection()->executeQuery("DELETE FROM $table");
        }
    }

    /**
     * @dataProvider providetestUserRegistration_OK
     */
    public function testUserRegistration_OK($email, $pwd, $expected): void
    {
        $crawler = self::$client->request('GET', '/register');
        $form = $crawler->selectButton('Register')->form();
        $form['registration_form[email]'] = $email;
        $form['registration_form[plainPassword]'] = $pwd;
        $form['registration_form[agreeTerms]'] = true;

        self::$client->submit($form);
        $this->assertEquals(302, self::$client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /login ".self::$client->getResponse()->getStatusCode());

        $this->assertTrue(self::$client->getResponse()->isRedirect('/login'), "Unexpected HTTP status code for GET /login ".self::$client->getResponse()->getTargetUrl());

        self::$client->followRedirect();
        $this->assertResponseStatusCodeSame($expected, "Unexpected HTTP status code for GET /login ".self::$client->getResponse()->getStatusCode());
    }

    public function providetestUserRegistration_OK()
    {
        return [
            ["yannis@mail.fr","Azerty05*", 200],
            ["tarek@mail.fr","Azerty05*", 200],
            ["alain@mail.fr","Azerty05*", 200],
            ["zach@mail.fr","Azerty05*", 200]
        ];
    }

    protected function tearDown(): void
    {
        $tables = ['user'];
        foreach ($tables as $table) {
            self::$entityManager->getConnection()->executeQuery("DELETE FROM $table");
        }

        parent::tearDown();
    }
}
