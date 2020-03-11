<?php
/**
 * Created by PhpStorm.
 * User: mitalcoi
 * Date: 04.03.2018
 * Time: 20:31
 */

namespace App\Infrastructure\Persistence\Doctrine\DoctrineAware;
use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
class UserAware
{
    public $fieldName='user_id';
}
