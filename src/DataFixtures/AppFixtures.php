<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Client;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Créer des clients
        for ($i = 1; $i <= 5; $i++) {
            $client = new Client();
            $client->setNom("Client$i")
                   ->setPrenom("Prenom$i")
                   ->setTelephone("77000000$i")
                   ->setAdresse("Dakar, Quartier $i, Villa $i");
            $manager->persist($client);

            // Créer des commandes pour chaque client
            for ($j = 1; $j <= 3; $j++) {
                $commande = new Commande();
                $commande->setClient($client)
                         ->setDate(new \DateTime());

                // Ajouter des lignes de commande à la commande
                for ($k = 1; $k <= 2; $k++) {
                    $article = new Article();
                    $article->setNom("Article-$i$j$k")
                            ->setPrix(rand(100, 1000))
                            ->setQuantiteStock(rand(10, 50));
                    $manager->persist($article);

                    $ligneCommande = new LigneCommande();
                    $ligneCommande->setArticle($article)
                                  ->setCommande($commande)
                                  ->setQuantite(rand(1, 5))
                                  ->setPrix($article->getPrix());
                    $manager->persist($ligneCommande);

                    $commande->addLigneCommande($ligneCommande);
                }

                $manager->persist($commande);
            }
        }

        // Finaliser l'insertion
        $manager->flush();
    }
}
