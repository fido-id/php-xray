<?php

class Thrower
{
    public function __construct(string $anArg, int $another, \stdClass $aClass, array $anArray)
    {
        throw new \RuntimeException('nice try!');
    }
}
