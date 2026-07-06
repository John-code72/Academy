<?php

namespace App\Services\PeopleSeek\Contracts;

interface InterpreterSourceAdapter
{
    /**
     * @param  array<string, mixed>  $source
     * @param  array<string, mixed>  $options
     * @return array<int, array<string, mixed>>
     */
    public function fetchProfiles(array $source, array $options = []): array;
}
