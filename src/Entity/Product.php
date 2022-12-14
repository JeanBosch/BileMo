<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @Hateoas\Relation(
 *     "self",
 *    href = @Hateoas\Route(
 *         "app_detail_product",
 *        parameters = { "id" = "expr(object.getId())" },
 *    ),
 *   
 * 
 * 
 * )
 * 
 * @Hateoas\Relation(
 *    "delete",
 *   href = @Hateoas\Route(
 *       "app_delete_product",
 *      parameters = { "id" = "expr(object.getId())" },
 *  ),
 * 
 * 
 * )
 * 
 * @Hateoas\Relation(
 *   "update",
 * href = @Hateoas\Route(
 *    "app_update_product",
 *  parameters = { "id" = "expr(object.getId())" },
 * ),
 * 
 * 
 * )
 * 
 * 
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */


class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(min=3, max=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=2000)
     *  @Assert\NotBlank
     * @Assert\Length(min=3, max=2000)
     */
    private $description;

    /**
     * @ORM\Column(type="date")
     */
    private $creation_date;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $manufacturer;

    /**
     * @ORM\Column(type="float")
     * @Assert\GreaterThan(0)
     * @Assert\Type("float")
     */
    private $length;

    /**
     * @ORM\Column(type="float")
     * @Assert\GreaterThan(0)
     * @Assert\Type("float")
     */
    private $width;

    /**
     * @ORM\Column(type="float")
     * @Assert\GreaterThan(0)
     * @Assert\Type("float")
     */
    private $weight;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $modif_date;

    /**
     * @ORM\Column(type="float")
     * @Assert\GreaterThan(0)
     * @Assert\Type("float")
     */

    private $price;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $image;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    public function getManufacturer(): ?string
    {
        return $this->manufacturer;
    }

    public function setManufacturer(string $manufacturer): self
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function getLength(): ?float
    {
        return $this->length;
    }

    public function setLength(float $length): self
    {
        $this->length = $length;

        return $this;
    }

    public function getWidth(): ?float
    {
        return $this->width;
    }

    public function setWidth(float $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getModifDate(): ?\DateTimeInterface
    {
        return $this->modif_date;
    }

    public function setModifDate(?\DateTimeInterface $modif_date): self
    {
        $this->modif_date = $modif_date;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }
}
