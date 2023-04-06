<?php

namespace Efabrica\GraphQL\Nette\Resolvers\NetteDatabase;

use Efabrica\GraphQL\Exceptions\ResolverException;
use Efabrica\GraphQL\Schema\Definition\ResolveInfo;
use Throwable;

final class TableCountResolver extends DatabaseResolver
{
    /**
     * @param null $parentValue
     *
     * @throws ResolverException
     */
    public function __invoke($parentValue, array $args, ResolveInfo $resolveInfo): int
    {
        $selection = $this->explorer->table($resolveInfo->getField()->getSetting('table_name'));

        $this->applyConditionsToSelection($selection, $args);

        try {
            return $selection->count('*');
        } catch (Throwable $e) {
            throw new ResolverException('There was an error while executing the query', 0, $e, $e);
        }
    }
}
