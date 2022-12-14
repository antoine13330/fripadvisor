<?php

namespace App\Entity;

use App\Repository\ShopRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;
use PhpParser\Node\Expr\Cast\Double;

/**
 * @Hateoas\Relation(
 *      "self",
 *      href=@Hateoas\Route(
 *      "shops.getShop",
 *      parameters= {
 *      "idShop" = "expr(object.getId())"
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
    #[Groups(['getShop', 'getAllShops', 'getProduct', 'getAllProducts'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Une boutique doit avoir un nom")]
    #[Assert\NotNull(message: "Une boutique doit avoir un nom")]
    #[Assert\Length(min: 3, minMessage: "Le nom de la boutique doit faire plus de {{ limit }} lettres")]
    #[Groups(['getShop', 'getAllShops'])]
    private ?string $name = null;

    #[Assert\NotNull()]
    #[Assert\NotBlank(message: "Il faut renseigner le code postal")]
    #[ORM\Column(length: 5)]
    #[Assert\NotBlank(message: "Une boutique doit avoir un code postal")]
    #[Assert\NotNull()]
    #[Assert\Length(min: 3, minMessage: "Le code postal de la boutique doit faire plus de {{ limit }} caractères")]
    #[Groups(['getShop', 'getAllShops'])]
    private ?string $poastalCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getShop', 'getAllShops'])]
    private ?string $location = null;

    #[Groups(['getShop', 'getAllShops'])]
    #[ORM\Column(length: 1)]
    private ?string $satus = null;

    #[Groups(['getShop', 'getAllShops'])]
    #[ORM\Column(length: 255, nullable: false)]
    #[Assert\NotBlank(message: "Une boutique doit avoir une longitude")]
    #[Assert\NotNull()]
    private ?float $longitude = null;

    #[Groups(['getShop', 'getAllShops'])]
    #[ORM\Column(length: 255, nullable: false)]
    #[Assert\NotBlank(message: "Une boutique doit avoir une latitude")]
    #[Assert\NotNull()]
    private ?float $latitude = null;


    #[Groups(['getShop', 'getAllShops'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?float $rayon = null;

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
    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }
    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }
    public function getRayon(): ?float
    {
        return $this->rayon;
    }

    public function setRayon(float $rayon): self
    {
        $this->rayon = $rayon;

        return $this;
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
}
