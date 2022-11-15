<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?shop $idShop = null;


    #[Assert\NotNull()]
    #[Assert\NotBlank(message: "Un produit doit avoir un nom")]
    #[Assert\NotNull(message: "Un produit doit avoir un nom")]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Un produit doit avoir un prix")]
    #[Assert\NotNull(message: "Un produit doit avoir un prix")]
    private ?int $price = null;

    #[ORM\Column(length: 20)]
    private ?string $size = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Il faut renseigner le nombre d'éléments en stock")]
    #[Assert\NotNull(message: "Il faut renseigner le nombre d'éléments en stock")]
    private ?int $stock = null;

    #[ORM\Column(length: 1)]
    private ?string $status = null;

    #[ORM\ManyToMany(targetEntity: Category::class, mappedBy: 'relation')]
    private Collection $idCategory;

    public function __construct()
    {
        $this->idCategory = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdShop(): ?shop
    {
        return $this->idShop;
    }

    public function setIdShop(?shop $idShop): self
    {
        $this->idShop = $idShop;

        return $this;
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

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(string $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;

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
     * @return Collection<int, Category>
     */
    public function getIdCategory(): Collection
    {
        return $this->idCategory;
    }

    public function addIdCategory(Category $idCategory): self
    {
        if (!$this->idCategory->contains($idCategory)) {
            $this->idCategory->add($idCategory);
            $idCategory->addRelation($this);
        }

        return $this;
    }

    public function removeIdCategory(Category $idCategory): self
    {
        if ($this->idCategory->removeElement($idCategory)) {
            $idCategory->removeRelation($this);
        }

        return $this;
    }
}
