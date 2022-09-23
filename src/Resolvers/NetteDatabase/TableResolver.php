<?php

namespace Efabrica\GraphQL\Nette\Resolvers\NetteDatabase;

use Efabrica\GraphQL\Schema\Definition\ResolveInfo;
use Nette\Database\Table\ActiveRow;

final class TableResolver extends DatabaseResolver
{
    /**
     * @param null $parentValue
     *
     * @return ActiveRow[]
     */
    public function __invoke($parentValue, array $args, ResolveInfo $resolveInfo): array
    {
        $selection = $this->explorer->table($resolveInfo->getField()->getSetting('table_name'));

        $this->applyPaginationToSelection($selection, $args);
        $this->applyOrderToSelection($selection, $args);
        $this->applyConditionsToSelection($selection, $args);

        return $selection->fetchAll();
    }
}
