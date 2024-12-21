<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Commande;
use App\Entity\Client;
use App\Entity\LigneCommande;
use App\Entity\Article;
use App\Repository\CommandeRepository;
use App\Repository\ClientRepository;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CommandeController extends AbstractController
{

    #[Route('/commande', name: 'app_commande', methods: ['GET'])]
    public function index(): Response
    {
        return $this->redirectToRoute('nouvelle_commande');
    }
        /**
     * Affiche la vue principale pour gérer une commande.
     */
    #[Route('/commande/nouvelle', name: 'nouvelle_commande', methods: ['GET'])]
    public function nouvelleCommande(
        ArticleRepository $articleRepository,
        ClientRepository $clientRepository,
        EntityManagerInterface $em
    ): Response {
        // Récupérer un client (vous pouvez remplacer 1 par l'ID d'un client valide)
        $client = $clientRepository->find(1);
    
        if (!$client) {
            throw $this->createNotFoundException('Aucun client trouvé pour cette commande.');
        }
    
        // Créer une nouvelle commande pour ce client
        $commande = new Commande();
        $commande->setClient($client);
    
        $em->persist($commande);
        $em->flush();
    
        // Récupérer les articles disponibles
        $articles = $articleRepository->findAll();
    
        return $this->render('commande/nouvelle_commande.html.twig', [
            'articles' => $articles,
            'commandeId' => $commande->getId(),
        ]);
    }
    

    

    /**
     * Recherche un client par son numéro de téléphone.
     */
    #[Route('/commande/client/rechercher', name: 'rechercher_client_commande', methods: ['POST'])]
    public function rechercherClient(Request $request, ClientRepository $clientRepository): JsonResponse
    {
        $telephone = $request->request->get('telephone');

        if (!$telephone) {
            return new JsonResponse(['error' => 'Numéro de téléphone requis'], 400);
        }

        $client = $clientRepository->findOneBy(['telephone' => $telephone]);

        if (!$client) {
            return new JsonResponse(['error' => 'Client non trouvé'], 404);
        }

        return new JsonResponse([
            'id' => $client->getId(),
            'nom' => $client->getNom(),
            'prenom' => $client->getPrenom(),
            'adresse' => $client->getAdresse(),
        ]);
    }

    /**
     * Ajoute un article à une commande.
     */
    #[Route('/commande/{commandeId}/article/ajouter', name: 'ajouter_article', methods: ['POST'])]
    public function ajouterArticle(
        int $commandeId,
        Request $request,
        CommandeRepository $commandeRepository,
        ArticleRepository $articleRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $commande = $commandeRepository->find($commandeId);

        if (!$commande) {
            return new JsonResponse(['error' => 'Commande non trouvée'], 404);
        }

        $articleId = $request->request->get('article_id');
        $quantite = (int)$request->request->get('quantite');
        $prix = (float)$request->request->get('prix');

        $article = $articleRepository->find($articleId);
        if (!$article) {
            return new JsonResponse(['error' => 'Article non trouvé'], 404);
        }

        if ($quantite > $article->getQuantiteStock()) {
            return new JsonResponse(['error' => 'Quantité demandée non disponible en stock'], 400);
        }

        $ligneCommande = new LigneCommande();
        $ligneCommande->setArticle($article);
        $ligneCommande->setQuantite($quantite);
        $ligneCommande->setPrix($prix);
        $ligneCommande->setCommande($commande);

        $article->setQuantiteStock($article->getQuantiteStock() - $quantite);

        $em->persist($ligneCommande);
        $em->persist($article);
        $em->flush();

        return new JsonResponse(['message' => 'Article ajouté avec succès']);
    }

    /**
     * Valide une commande et enregistre les données.
     */
    #[Route('/commande/{commandeId}/valider', name: 'valider_commande', methods: ['POST'])]
    public function validerCommande(int $commandeId, CommandeRepository $commandeRepository): JsonResponse
    {
        $commande = $commandeRepository->find($commandeId);
        if (!$commande) {
            return new JsonResponse(['error' => 'Commande non trouvée'], 404);
        }

        return new JsonResponse(['message' => 'Commande validée avec succès']);
    }

}
