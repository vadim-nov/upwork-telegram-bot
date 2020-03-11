<?php


namespace App\Domain\Core\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class NewUserDto
 * @package App\Domain\Core\Dto
 */
class NewUserDto
{
    private $email;
    private $plainPassword;
    private $terms;

    /**
     * @Assert\Email()
     * @Assert\NotBlank()
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
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
     */
    public function setPlainPassword($plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @return mixed
     */
    public function getTerms()
    {
        return $this->terms;
    }

    /**
     * @param mixed $terms
     */
    public function setTerms($terms): void
    {
        $this->terms = $terms;
    }
}
