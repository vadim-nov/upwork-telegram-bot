<?php
/**
 * Created by PhpStorm.
 * User: mitalcoi
 * Date: 04.03.2018
 * Time: 20:34
 */

namespace App\Infrastructure\Persistence\Doctrine\DoctrineAware;


use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

final class UserAwareFilter extends SQLFilter
{
    /**
     * @var Reader
     */
    private $reader;

    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if (null === $this->reader) {
            throw new \RuntimeException(sprintf('An annotation reader must be provided. Be sure to call "%s::setAnnotationReader()".',
                __CLASS__));
        }

        // The Doctrine filter is called for any query on any entity
        // Check if the current entity is "user aware" (marked with an annotation)
        $aware = $this->reader->getClassAnnotation($targetEntity->getReflectionClass(), UserAware::class);
        if (!$aware) {
            return '';
        }

        $fieldName = $aware->fieldName;
        try {
            // Don't worry, getParameter automatically escapes parameters
            $userId = $this->getParameter('id');
        } catch (\InvalidArgumentException $e) {
            // No user id has been defined
            return '';
        }

        if (empty($fieldName) || empty($userId)) {
            return '';
        }

        return sprintf("{$targetTableAlias}.{$fieldName} = {$userId}");
    }

    public function setAnnotationReader(Reader $reader): void
    {
        $this->reader = $reader;
    }
}
