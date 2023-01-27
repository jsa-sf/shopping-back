<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 * @ApiResource(
 *     normalizationContext={"groups"={"Read:Product"}},
 *     denormalizationContext={"groups"={"Write:Product"}},
 *     paginationClientItemsPerPage=true,
 *     itemOperations={
 *          "get"={
 *              "security"="is_granted('PRODUCT_VIEW', object)",
 *              "openapi_context"={"security"={{"bearerAuth"={}}}}
 *          },
 *          "patch"={
 *              "security"="is_granted('PRODUCT_EDIT', object)",
 *              "openapi_context"={"security"={{"bearerAuth"={}}}}
 *          },
 *          "delete"={
 *              "security"="is_granted('PRODUCT_EDIT', object)",
 *              "openapi_context"={"security"={{"bearerAuth"={}}}}
 *          }
 *     },
 *     collectionOperations={
 *          "get"={
 *              "security"="is_granted('ROLE_USER')",
 *              "openapi_context"={"security"={{"bearerAuth"={}}}}
 *          },
 *          "post"={
 *              "security"="is_granted('ROLE_USER')",
 *              "openapi_context"={"security"={{"bearerAuth"={}}}}
 *          }
 *     }
 * )
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"Read:Product"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\Length(max=100)
     * @Groups({"Write:Product","Read:Product"})
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(min="1")
     * @Groups({"Write:Product","Read:Product"})
     */
    private $quantity;

    /**
     * @ORM\ManyToOne(targetEntity=Basket::class, inversedBy="products")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $basket;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="sharedProducts")
     * @Groups({"Write:Product","Read:Product"})
     */
    private $sharedUser;

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

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getBasket(): ?Basket
    {
        return $this->basket;
    }

    public function setBasket(?Basket $basket): self
    {
        $this->basket = $basket;

        return $this;
    }

    public function getSharedUser(): ?User
    {
        return $this->sharedUser;
    }

    public function setSharedUser(?User $sharedUser): self
    {
        $this->sharedUser = $sharedUser;

        return $this;
    }

    /**
     * @return User|null
     * @Groups({"Read:Product"})
     */
    public function getOwner(): ?User
    {
        return $this->getBasket()->getUser();
    }
}
