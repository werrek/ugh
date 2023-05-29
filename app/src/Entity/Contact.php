<?php
/**
 * Contact Entity.
 */

namespace App\Entity;

use App\Repository\ContactRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Category entity.
 */
#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64)]
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 64)]
    private ?string $name = null;

    #[ORM\Column(length: 64)]
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 64)]
    private ?string $surname = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Type('string')]
    #[Assert\Length(max: 255)]
    private ?string $address = null;

    #[ORM\Column(length: 32, nullable: true)]
    #[Assert\Type('string')]
    #[Assert\Length(max: 32)]
    private ?string $phone = null;

    /**
     * @return int|null id
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name new name
     *
     * @return $this obj
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null surname
     */
    public function getSurname(): ?string
    {
        return $this->surname;
    }

    /**
     * @param string $surname new surname
     *
     * @return $this obj
     */
    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * @return string|null address as string
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $address new adrees
     *
     * @return $this this object
     */
    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return string|null phone number as string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone phone number
     *
     * @return $this $this object
     */
    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string|null return ojb email
     */
    public function __toString()
    {
        return $this->name;
    }
}
