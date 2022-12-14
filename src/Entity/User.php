<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;
use Symfony\Component\Validator\Constraints as Assert;



/**
 * @Hateoas\Relation(
 *     "self",
 *    href = @Hateoas\Route(
 *         "app_user_client",
 *        parameters = { "id" = "expr(object.getId())" },
 *    ),
 *  exclusion = @Hateoas\Exclusion(groups={"getUsersList"}) 
 * 
 * 
 * )
 * 
 * 
 * @Hateoas\Relation(
 *    "delete",
 *   href = @Hateoas\Route(
 *       "app_delete_client",
 *      parameters = { "id" = "expr(object.getId())" },
 *  ),
 *  exclusion = @Hateoas\Exclusion(groups={"getUsersList"})
 * 
 * 
 * )
 * 
 * @Hateoas\Relation(
 *   "update",
 * href = @Hateoas\Route(
 *    "app_update_client",
 *  parameters = { "id" = "expr(object.getId())" },
 * ),
 * exclusion = @Hateoas\Exclusion(groups={"getUsersList"})
 * 
 * )
 * 
 * @Hateoas\Relation(
 *    "customers_list",
 *  href = @Hateoas\Route(
 *      "app_customers_by_user",
 *    parameters = { "id" = "expr(object.getId())" },
 * ),
 * exclusion = @Hateoas\Exclusion(groups={"getUsersList"})
 * 
 * )
 * 
 * 
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */


class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"getUsersList", "getCustomersList"})
     */
    private $id;

    /**
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups({"getUsersList", "getCustomersList"})
     */
    private $company;

    /**
     * @ORM\Column(type="json")
     * @Groups({"getUsersList", "getCustomersList"})
     */
    private $roles = [];

    /**
     * @Assert\NotBlank
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @Assert\NotBlank
     * @Assert\Email
     * @ORM\Column(type="string", length=255)
     * @Groups({"getUsersList", "getCustomersList"})
     */

    private $email;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"getUsersList"}) 
     */
    private $creation_date;

    /**
     * @ORM\OneToMany(targetEntity=Customer::class, mappedBy="vendor")
     */
    private $customers;


    public function __construct()
    {
        $this->customers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(string $company): self
    {
        $this->company = $company;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->company;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->company;
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
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creation_date;
    }

    public function setCreationDate(\DateTimeInterface $creation_date): self
    {
        $this->creation_date = $creation_date;

        return $this;
    }

    /**
     * @return Collection<int, Customer>
     */
    public function getCustomers(): Collection
    {
        return $this->customers;
    }

    public function addCustomer(Customer $customer): self
    {
        if (!$this->customers->contains($customer)) {
            $this->customers[] = $customer;
            $customer->setVendor($this);
        }

        return $this;
    }

    public function removeCustomer(Customer $customer): self
    {
        if ($this->customers->removeElement($customer)) {
            // set the owning side to null (unless already changed)
            if ($customer->getVendor() === $this) {
                $customer->setVendor(null);
            }
        }

        return $this;
    }
}
