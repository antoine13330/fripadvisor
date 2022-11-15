<?php

namespace App\Entity;

use App\Repository\ShopRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;
/**
 * @Hateoas\Relation(
 *      "self",
 *      href=@Hateoas\Route(
 *      "shops.getShop",
 *      parameters= {
 *          "idShop" = "expr(object.getId())"
 *      }
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups="getAllShops")
 * )
 */

#[ORM\Entity(repositoryClass: ShopRepository::class)]
class Shop
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getShop', 'getAllShops'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Une boutique doit avoir un nom")]
    #[Assert\NotNull(message: "Une boutique doit avoir un nom")]
    #[Assert\Length(min: 3, minMessage: "Le nom de la boutique doit faire plus de {{ limit }} lettres")]
    #[Groups(['getShop', 'getAllShops'])]
    private ?string $name = null;

    #[Groups(['getShop', 'getAllShops'])]
    #[Assert\NotNull()]
    #[Assert\NotBlank(message: "Il faut renseigner le code postal")]
    #[ORM\Column(length: 5)]
    #[Groups(['getShop', 'getAllShops'])]
    private ?string $poastalCode = null;

    #[Groups(['getShop', 'getAllShops'])]
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getShop', 'getAllShops'])]
    private ?string $location = null;

    #[Groups(['getShop', 'getAllShops'])]
    #[ORM\Column(length: 1)]
    #[Groups(['getShop', 'getAllShops'])]
    private ?string $satus = null;

    #[ORM\OneToMany(mappedBy: 'idShop', targetEntity: Product::class)]
    private Collection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
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

    public function getPoastalCode(): ?string
    {
        return $this->poastalCode;
    }

    public function setPoastalCode(string $poastalCode): self
    {
        $this->poastalCode = $poastalCode;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getSatus(): ?string
    {
        return $this->satus;
    }

    public function setSatus(string $satus): self
    {
        $this->satus = $satus;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setIdShop($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getIdShop() === $this) {
                $product->setIdShop(null);
            }
        }

        return $this;
    }

//    public function getBoutiqueCategorie(): Collection
//    {
//        return $this->boutiqueCategorie;
//    }

//    public function addBoutiqueCategorie(Categorie $boutiqueCategorie): self
//    {
//        if (!$this->boutiqueCategorie->contains($boutiqueCategorie)) {
//            $this->boutiqueCategorie->add($boutiqueCategorie);
//            $boutiqueCategorie->addBoutiqueCategorie($this);
//        }

//        return $this;
//    }

//    public function removeBoutiqueCategorie(Categorie $boutiqueCategorie): self
//    {
//        if ($this->boutiqueCategorie->removeElement($boutiqueCategorie)) {
//            $boutiqueCategorie->removeBoutiqueCategorie($this);
//        }

//        return $this;
//    }

//
// #[ORM\Id]
//    #[ORM\GeneratedValue]
//    #[ORM\Column]
//    #[Groups(['getCategorie', 'getAllCategories'])]
//    private ?int $id = null;
//
//    #[ORM\ManyToMany(targetEntity: Boutique::class, inversedBy: 'boutiqueCategorie')]
//    #[Groups(['getCategorie', 'getAllCategories'])]
//    private Collection $boutiqueCategorie;
//
//    #[ORM\Column]
//    private ?bool $status = null;
//
//    #[ORM\Column(length: 255)]
//    #[Assert\NotBlank(message: "Une catégorie doit avoir un nom")]
//    #[Assert\NotNull()]
//    #[Assert\Length(min: 3, minMessage: "Le nom de la catégorie doit faire plus de {{ limit }} lettres")]
//    #[Groups(['getCategorie', 'getAllCategories', 'getBoutique', 'getAllBoutiques'])]
//    private ?string $categorieNom = null;
//

}
