<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Hateoas\Configuration\Annotation as Hateoas;
/**
 * @Hateoas\Relation(
 *      "self",
 *      href=@Hateoas\Route(
 *      "categories.getCategory",
 *      parameters= {
 *          "idCategory" = "expr(object.getId())"
 *      }
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups="getAllCategories")
 * )
 */

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getAllCategories', 'getCategory'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getAllCategories', 'getCategory'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getAllCategories', 'getCategory'])]
    private ?string $type = null;

    #[ORM\Column(length: 1)]
    private ?string $status = null;

    #[ORM\ManyToMany(targetEntity: Product::class, inversedBy: 'idCategory')]
    #[Groups(['getAllCategories', 'getCategory'])]
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

    public function addRelation(product $relation): self
    {
        if (!$this->relation->contains($relation)) {
            $this->relation->add($relation);
        }

        return $this;
    }

    public function removeRelation(product $relation): self
    {
        $this->relation->removeElement($relation);

        return $this;
    }
}