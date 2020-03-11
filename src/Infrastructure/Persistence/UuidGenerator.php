<?php


namespace App\Infrastructure\Persistence;


class UuidGenerator

{
    public static $id;

    public static function generate()
    {
        if (self::$id !== null) {
            return self::$id;
        }

        return \Ramsey\Uuid\Uuid::uuid4()->toString();
    }
}
