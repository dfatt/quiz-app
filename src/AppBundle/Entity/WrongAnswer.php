<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WrongAnswer
 *
 * @ORM\Table(name="wrong_answers")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\WrongAnswerRepository")
 */
class WrongAnswer
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Entity
     * @ORM\ManyToOne(targetEntity="Question")
     */
    protected $question;

    /**
     * @var Entity
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $user;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


}

