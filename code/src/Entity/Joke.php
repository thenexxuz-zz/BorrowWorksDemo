<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Joke
 * @package App\Entity
 *
 * @ORM\Table(name="joke")
 * @ORM\Entity()
 */
class Joke
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(name="punchline", type="text")
     */
    private $punchline;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getPunchline(): ?string
    {
        return $this->punchline;
    }

    /**
     * @param string|null $punchline
     */
    public function setPunchline(?string $punchline): void
    {
        $this->punchline = $punchline;
    }
}