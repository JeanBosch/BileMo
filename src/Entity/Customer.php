<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *     "self",
 *    href = @Hateoas\Route(
 *         "app_detail_customer",
 *        parameters = { "id" = "expr(object.getId())" },
 *    ),
 *  exclusion = @Hateoas\Exclusion(groups={"getCustomersList"}) 
 * 
 * 
 * )
 * 
 * @Hateoas\Relation(
 *    "delete",
 *   href = @Hateoas\Route(
 *       "app_delete_customer",
 *      parameters = { "id_customer" = "expr(object.getId())", "id" = "expr(object.getVendor().getId())" },
 *  ),
 *  exclusion = @Hateoas\Exclusion(groups={"getCustomersList"})
 * 
 * 
 * )
 * 
 * @Hateoas\Relation(
 *   "update",
 * href = @Hateoas\Route(
 *    "app_update_customer",
 *  parameters = { "id_customer" = "expr(object.getId())", "id" = "expr(object.getVendor().getId())" },
 * ),
 * exclusion = @Hateoas\Exclusion(groups={"getCustomersList"})
 * 
 * )
 * 
 * 
 * 
 * @ORM\Entity(repositoryClass=CustomerRepository::class)
 */


class Customer
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"getCustomersList"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"getCustomersList"})
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"getCustomersList"})
     */
    private $name;



    /**
     * @ORM\Column(type="datetime")
     * @Groups({"getCustomersList"})
     */
    private $creation_date;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"getCustomersList"})
     */
    private $last_buy_date;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="customers")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"getCustomersList"})
     */
    private $vendor;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getLastBuyDate(): ?\DateTimeInterface
    {
        return $this->last_buy_date;
    }

    public function setLastBuyDate(?\DateTimeInterface $last_buy_date): self
    {
        $this->last_buy_date = $last_buy_date;

        return $this;
    }

    public function getVendor(): ?User
    {
        return $this->vendor;
    }

    public function setVendor(?User $vendor): self
    {
        $this->vendor = $vendor;

        return $this;
    }
}
