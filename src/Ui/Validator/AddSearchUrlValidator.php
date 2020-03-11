<?php


namespace App\Ui\Validator;


use App\Domain\Core\Entity\User;
use App\Domain\Upwork\SearchUrlParser;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;


class AddSearchUrlValidator extends ConstraintValidator
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param mixed $value
     * @param AddSearchUrl $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        try {
            $parser = new SearchUrlParser();
            $parser->assertValidSearchUrl($value);
            /** @var User $user */
            $user = $this->security->getUser();
            $user->assertCanAddSearch();
        } catch (\DomainException $exception) {
            $this->context->buildViolation($exception->getMessage())->addViolation();
        }
    }
}
