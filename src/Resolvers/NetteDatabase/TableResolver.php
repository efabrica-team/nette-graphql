<?php

namespace Efabrica\GraphQL\Nette\Resolvers\NetteDatabase;

use Efabrica\GraphQL\Exceptions\ResolverException;
use Efabrica\GraphQL\Schema\Definition\ResolveInfo;
use Nette\Database\Table\ActiveRow;
use Throwable;

final class TableResolver extends DatabaseResolver
{
    /**
     * @param null $parentValue
     *
     * @return ActiveRow[]
     * @throws ResolverException
     */
    public function __invoke($parentValue, array $args, ResolveInfo $resolveInfo): array
    {
        $selection = $this->explorer->table($resolveInfo->getField()->getSetting('table_name'));

        $this->applyPaginationToSelection($selection, $args);
        $this->applyOrderToSelection($selection, $args);
        $this->applyConditionsToSelection($selection, $args);

        try {
            return $selection->fetchAll();
        } catch (Throwable $e) {
            throw new ResolverException('There was an error while executing the query', 0, $e, $e);
        }
    }
}
