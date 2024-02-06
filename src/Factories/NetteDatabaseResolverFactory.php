<?php

namespace Efabrica\GraphQL\Nette\Factories;

use Efabrica\GraphQL\Helpers\AdditionalResponseData;
use Efabrica\GraphQL\Nette\Resolvers\NetteDatabase\BelongsToResolver;
use Efabrica\GraphQL\Nette\Resolvers\NetteDatabase\HasManyCountResolver;
use Efabrica\GraphQL\Nette\Resolvers\NetteDatabase\HasManyResolver;
use Efabrica\GraphQL\Nette\Resolvers\NetteDatabase\MorphToResolver;
use Efabrica\GraphQL\Nette\Resolvers\NetteDatabase\TableCountResolver;
use Efabrica\GraphQL\Nette\Resolvers\NetteDatabase\TableResolver;
use Nette\Database\Explorer;

class NetteDatabaseResolverFactory implements NetteDatabaseResolverFactoryInterface
{
    private Explorer $explorer;

    private AdditionalResponseData $additionalResponseData;

    private bool $firstParty = false;

    public function __construct(Explorer $explorer, AdditionalResponseData $additionalResponseData)
    {
        $this->explorer = $explorer;
        $this->additionalResponseData = $additionalResponseData;
    }

    public function isFirstParty(): bool
    {
        return $this->firstParty;
    }

    public function setFirstParty(bool $firstParty = true): self
    {
        $this->firstParty = $firstParty;
        return $this;
    }

    public function createTableResolver(): TableResolver
    {
        return new TableResolver($this->explorer, $this->additionalResponseData, $this->firstParty);
    }

    public function createTableCountResolver(): TableCountResolver
    {
        return new TableCountResolver($this->explorer, $this->additionalResponseData, $this->firstParty);
    }

    public function createBelongsToResolver(): BelongsToResolver
    {
        return new BelongsToResolver($this->explorer, $this->additionalResponseData, $this->firstParty);
    }

    public function createHasManyResolver(): HasManyResolver
    {
        return new HasManyResolver($this->explorer, $this->additionalResponseData, $this->firstParty);
    }

    public function createHasManyCountResolver(): HasManyCountResolver
    {
        return new HasManyCountResolver($this->explorer, $this->additionalResponseData, $this->firstParty);
    }

    public function createMorphToResolver(): MorphToResolver
    {
        return new MorphToResolver($this->explorer, $this->additionalResponseData, $this->firstParty);
    }
}
