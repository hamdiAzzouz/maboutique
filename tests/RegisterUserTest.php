<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegisterUserTest extends WebTestCase
{
    public function testSomething(): void
    {
        /*
        * 1. Créer un faux client (navigateur) de pointer vers une URL
        * 2. Remplir les champs de mon formulaire d'inscription
        * 3. Suivre la redirection
        * 4. Est-ce que tu peux regarder si dans ma page j'ai le message (alert) suivante: Votre compte est correctement créé, veuillez vous connectez!
        */


        // etape 1
        $client = static::createClient();
        $crawler = $client->request('GET', '/inscription');

        // etape 2
        $client->submitForm('Valider', [
            'register_user[email]' => 'sami@mail.fr',
            'register_user[plainPassword][first]' => '123456',
            'register_user[plainPassword][second]' => '123456',
            'register_user[firstname]' => 'Sami9',
            'register_user[lastname]' => 'Zamzoum'
        ]);
        // etape 3
        $this->assertResponseRedirects('/connexion');
        $client->followRedirect();
        // etape 4
        $this->assertSelectorExists('div:contains("Votre compte est correctement créé, veuillez vous connectez!")');
    }
}
