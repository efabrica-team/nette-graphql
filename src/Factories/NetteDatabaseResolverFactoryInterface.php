<?php

namespace Efabrica\GraphQL\Nette\Factories;

use Efabrica\GraphQL\Resolvers\ResolverInterface;

interface NetteDatabaseResolverFactoryInterface
{
    public function createTableResolver(): ResolverInterface;

    public function createTableCountResolver(): ResolverInterface;

    public function createBelongsToResolver(): ResolverInterface;

    public function createHasManyResolver(): ResolverInterface;

    public function createHasManyCountResolver(): ResolverInterface;
}
