<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\CommentaireRepository;
use DateTimeInterface;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
#[ORM\Table(name: 'commentaire')] // Nom de la table dans la base de données
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Le contenu du commentaire ne peut pas être vide.')]
    private string $content;

    #[ORM\Column(type: 'datetime')]
    private ?DateTimeInterface $dateCommentaire;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private ?User $user;

    #[ORM\ManyToOne(targetEntity: Article::class)]
    #[ORM\JoinColumn(name: 'article_id', referencedColumnName: 'id')]
    private ?Article $article;

    public function __construct()
    {
        $this->dateCommentaire = new \DateTime(); // Par défaut, on initialise la date du commentaire à l'heure actuelle
    }

    // Getter et setter pour l'id
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    // Getter et setter pour le contenu du commentaire
    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    // Getter et setter pour la date du commentaire
    public function getDateCommentaire(): ?DateTimeInterface
    {
        return $this->dateCommentaire;
    }

    public function setDateCommentaire(DateTimeInterface $dateCommentaire): self
    {
        $this->dateCommentaire = $dateCommentaire;
        return $this;
    }

    // Getter et setter pour l'utilisateur (lié au commentaire)
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    // Getter et setter pour l'article (lié au commentaire)
    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;
        return $this;
    }
}
