<?php
/**
 * Event Entity.
 */

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Category entity.
 */
#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Category')]
    private ?Category $category = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\Type('datetime')]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    private ?string $place = null;

    #[ORM\Column(length: 255)]
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    private ?string $title = null;

    /**
     * @return int|null getter
     */
    public function getId(): ?int
    {
        return $this->id;
    }// end getId()

    /**
     * @return Category|null getter
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }// end getCategory()

    /**
     * @param Category|null $category setter
     *
     * @return $this object
     */
    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }// end setCategory()

    /**
     * @return \DateTimeInterface|null getter
     */
    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }// end getDate()

    /**
     * @param \DateTimeInterface $date setter
     *
     * @return $this object
     */
    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }// end setDate()

    /**
     * @return string|null getter
     */
    public function getPlace(): ?string
    {
        return $this->place;
    }// end getPlace()

    /**
     * @param string $place setter
     *
     * @return $this object
     */
    public function setPlace(string $place): self
    {
        $this->place = $place;

        return $this;
    }// end setPlace()

    /**
     * @return string|null getter
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }// end getTitle()

    /**
     * @param string $title setter
     *
     * @return $this object
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }// end setTitle()

    /**
     * @return string|null return ojb email
     */
    public function __toString()
    {
        return $this->title;
    }// end __toString()
}// end class
