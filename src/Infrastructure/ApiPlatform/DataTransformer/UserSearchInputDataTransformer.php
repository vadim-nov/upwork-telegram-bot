<?php


namespace App\Infrastructure\ApiPlatform\DataTransformer;


use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Domain\Core\Dto\UserSearchInput;
use App\Domain\Core\Entity\UserSearch;
use App\Domain\Upwork\Exception\InvalidUpworkUrlException;
use App\Domain\Upwork\SearchUrlParser;
use App\Infrastructure\Persistence\UuidGenerator;
use Symfony\Component\Security\Core\Security;

class UserSearchInputDataTransformer implements DataTransformerInterface
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param UserSearchInput $object
     * @param string $to
     * @param array $context
     * @return UserSearch|object
     */
    public function transform($object, string $to, array $context = [])
    {
        $parser = new SearchUrlParser();
        try {
            $parsed = $parser->parse($object->getSearchUrl());
            if (!$object->getSearchName()) {
                if ($parsed->getQuery()) {
                    $object->setSearchName(urldecode($parsed->getQuery()));
                } else {
                    $object->setSearchName('default');
                }
            }
            $object->setSearchUrl($parsed->getOriginalSearch());
        } catch (InvalidUpworkUrlException $exception) {

        }

        return UserSearch::createFromUserSearchInput(UuidGenerator::generate(), $this->security->getUser(), $object);
    }

    public function supportsTransformation($object, string $to, array $context = []): bool
    {
        if ($object instanceof UserSearch) {
            return false;
        }

        return UserSearch::class === $to && null !== ($context['input']['class'] ?? null);
    }


}
