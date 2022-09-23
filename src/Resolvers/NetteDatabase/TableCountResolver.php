<?php

namespace Efabrica\GraphQL\Nette\Resolvers\NetteDatabase;

use Efabrica\GraphQL\Schema\Definition\ResolveInfo;
use Nette\Database\Table\ActiveRow;

final class TableCountResolver extends DatabaseResolver
{
    /**
     * @param null $parentValue
     *
     * @return ActiveRow[]
     */
    public function __invoke($parentValue, array $args, ResolveInfo $resolveInfo): int
    {
        $selection = $this->explorer->table($resolveInfo->getField()->getSetting('table_name'));

        $this->applyConditionsToSelection($selection, $args);

        return $selection->count('*');
    }
}
