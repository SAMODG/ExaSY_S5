<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?float $prix = null;

    #[ORM\Column]
    private ?int $quantiteStock = null;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: LigneCommande::class, cascade: ['persist', 'remove'])]
    private Collection $ligneCommandes;

    public function __construct()
    {
        $this->ligneCommandes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        if (empty($nom)) {
            throw new \InvalidArgumentException('Le nom de l\'article ne peut pas être vide.');
        }
        $this->nom = $nom;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        if ($prix < 0) {
            throw new \InvalidArgumentException('Le prix ne peut pas être négatif.');
        }
        $this->prix = $prix;

        return $this;
    }

    public function getQuantiteStock(): ?int
    {
        return $this->quantiteStock;
    }

    public function setQuantiteStock(int $quantiteStock): static
    {
        if ($quantiteStock < 0) {
            throw new \InvalidArgumentException('La quantité en stock ne peut pas être négative.');
        }
        $this->quantiteStock = $quantiteStock;

        return $this;
    }

    /**
     * @return Collection<int, LigneCommande>
     */
    public function getLigneCommandes(): Collection
    {
        return $this->ligneCommandes;
    }

    public function addLigneCommande(LigneCommande $ligneCommande): static
    {
        if (!$this->ligneCommandes->contains($ligneCommande)) {
            $this->ligneCommandes->add($ligneCommande);
            $ligneCommande->setArticle($this);
        }

        return $this;
    }

    public function removeLigneCommande(LigneCommande $ligneCommande): static
    {
        if ($this->ligneCommandes->removeElement($ligneCommande)) {
            if ($ligneCommande->getArticle() === $this) {
                $ligneCommande->setArticle(null);
            }
        }

        return $this;
    }

    /**
     * Vérifie si l'article est disponible en stock pour une certaine quantité.
     */
    public function isAvailable(int $quantite): bool
    {
        return $this->quantiteStock >= $quantite;
    }

    /**
     * Réduit la quantité en stock après une commande.
     */
    public function reduceStock(int $quantite): void
    {
        if (!$this->isAvailable($quantite)) {
            throw new \InvalidArgumentException('Quantité insuffisante en stock.');
        }
        $this->quantiteStock -= $quantite;
    }
}
