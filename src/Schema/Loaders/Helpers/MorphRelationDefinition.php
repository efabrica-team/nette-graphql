<?php

namespace Efabrica\GraphQL\Nette\Schema\Loaders\Helpers;

final class MorphRelationDefinition
{
    private string $table;

    private string $idColumn;

    private string $typeColumn;

    private string $relationName;

    public function __construct(string $table, string $idColumn, string $typeColumn, string $relationName)
    {
        $this->table = $table;
        $this->idColumn = $idColumn;
        $this->typeColumn = $typeColumn;
        $this->relationName = $relationName;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getIdColumn(): string
    {
        return $this->idColumn;
    }

    public function getTypeColumn(): string
    {
        return $this->typeColumn;
    }

    public function getRelationName(): string
    {
        return $this->relationName;
    }
}
