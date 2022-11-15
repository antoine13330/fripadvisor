<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getShop', 'getAllShops'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['getShop', 'getAllShops'])]
    private ?Shop $idShop = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getShop', 'getAllShops'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['getShop', 'getAllShops'])]
    private ?int $price = null;

    #[ORM\Column(length: 20)]
    #[Groups(['getShop', 'getAllShops'])]
    private ?string $size = null;

    #[ORM\Column]
    #[Groups(['getShop', 'getAllShops'])]
    private ?int $stock = null;

    #[ORM\Column(length: 1)]
    private ?string $status = null;

    #[ORM\ManyToMany(targetEntity: Category::class, mappedBy: 'relation')]
    #[Groups(['getShop', 'getAllShops'])]
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
