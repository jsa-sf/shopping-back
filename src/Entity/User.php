<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\MeController;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ApiResource(
 *     normalizationContext={"groups"={"Read:User"}},
 *     denormalizationContext={"groups"={"Write:User"}},
 *     collectionOperations={
 *          "get"={
 *              "security"="is_granted('ROLE_USER')",
 *              "openapi_context"={"security"={{"bearerAuth"={}}}}
 *          },
 *          "post"
 *     },
 *     itemOperations={
 *          "get"={
 *              "security"="is_granted('ROLE_USER')",
 *              "openapi_context"={"security"={{"bearerAuth"={}}}}
 *          },
 *          "me"={
 *              "path"="/me",
 *              "method"="get",
 *              "controller"=MeController::class,
 *              "openapi_context"={"security"={{"bearerAuth"={}}}},
 *              "read"=false
 *          }
 *     }
 * )
 */
class User implements UserInterface, JWTUserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"Read:User","Read:Product"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups({"Read:User","Read:Product","Write:User"})
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     * @Groups({"Read:User","Read:Product"})
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @SerializedName("password")
     * @Groups("Write:User")
     */
    private $plainPassword;

    /**
     * @ORM\OneToOne(targetEntity=Basket::class, mappedBy="user", cascade={"persist", "remove"})
     */
    private $basket;

    /**
     * @ORM\OneToMany(targetEntity=Product::class, mappedBy="sharedUser")
     */
    private $sharedProducts;

    public function __construct()
    {
        $this->sharedProducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): User
    {
        $this->id = $id;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public static function createFromPayload($id, array $payload)
    {
        $user = new User();
        $user
            ->setEmail($payload['username'] ?? '')
            ->setId(intval($id));

        return $user;
    }

    public function getBasket(): ?Basket
    {
        return $this->basket;
    }

    public function setBasket(Basket $basket): self
    {
        // set the owning side of the relation if necessary
        if ($basket->getUser() !== $this) {
            $basket->setUser($this);
        }

        $this->basket = $basket;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getSharedProducts(): Collection
    {
        return $this->sharedProducts;
    }

    public function addSharedProduct(Product $sharedProduct): self
    {
        if (!$this->sharedProducts->contains($sharedProduct)) {
            $this->sharedProducts[] = $sharedProduct;
            $sharedProduct->setSharedUser($this);
        }

        return $this;
    }

    public function removeSharedProduct(Product $sharedProduct): self
    {
        if ($this->sharedProducts->removeElement($sharedProduct)) {
            // set the owning side to null (unless already changed)
            if ($sharedProduct->getSharedUser() === $this) {
                $sharedProduct->setSharedUser(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param mixed $plainPassword
     * @return User
     */
    public function setPlainPassword($plainPassword): User
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }
}
