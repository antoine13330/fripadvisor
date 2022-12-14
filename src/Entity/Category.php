<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation\Groups;
//use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

use Hateoas\Configuration\Annotation as Hateoas;
/**
 * @Hateoas\Relation(
 *      "self",
 *      href=@Hateoas\Route(
 *      "categories.getCategory",
 *      parameters={
 *      "idCategory" = "expr(object.getId())"
 *       }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getAllCategories")
 * )
 *
 */

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getCategory', 'getAllCategories'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Une catégorie doit avoir un nom")]
    #[Assert\NotNull()]
    #[Assert\Length(min: 3, minMessage: "Le nom de la catégorie doit faire plus de {{ limit }} lettres")]
    #[Groups(['getCategory', 'getAllCategories'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Une catégorie doit avoir un type")]
    #[Assert\NotNull()]
    #[Assert\Length(min: 1, minMessage: "Le type de la catégorie doit faire plus de {{ limit }} lettres")]
    #[Groups(['getCategory', 'getAllCategories'])]
    #[Assert\NotBlank(message: "Une categorie doit avoir un type")]
    #[Assert\NotNull(message: "Une categorie doit avoir un type")]
    private ?string $type = null;

    #[ORM\Column(length: 1)]
    private ?string $status = null;

    #[ORM\ManyToMany(targetEntity: Product::class, inversedBy: 'idCategory')]
    private Collection $relation;

    public function __construct()
    {
        $this->relation = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, product>
     */
    public function getRelation(): Collection
    {
        return $this->relation;
    }

    public function addRelation(Product $relation): self
    {
        if (!$this->relation->contains($relation)) {
            $this->relation->add($relation);
        }

        return $this;
    }

    public function removeRelation(Product $relation): self
    {
        $this->relation->removeElement($relation);

        return $this;
    }
}
