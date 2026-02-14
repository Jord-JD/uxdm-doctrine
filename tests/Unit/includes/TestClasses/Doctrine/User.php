<?php

namespace JordJD\uxdm\TestClasses\Doctrine;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=15, nullable=false)
     * @ORM\Id
     */
    protected $name;

    /**
     * @var int
     *
     * @ORM\Column(name="value", type="integer", length=15, nullable=false)
     */
    protected $value;

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }
}
