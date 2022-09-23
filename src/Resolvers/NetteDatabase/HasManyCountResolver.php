<?php

namespace Efabrica\GraphQL\Nette\Resolvers\NetteDatabase;

use Efabrica\GraphQL\Schema\Definition\ResolveInfo;
use Nette\Database\Table\ActiveRow;

final class HasManyCountResolver extends DatabaseResolver
{
    /**
     * @param ActiveRow $parentValue
     */
    public function __invoke($parentValue, array $args, ResolveInfo $resolveInfo): int
    {
        $selection = $parentValue->related(
            $resolveInfo->getField()->getSetting('table_name'),
            $resolveInfo->getField()->getSetting('referencing_column')
        );

        $this->applyConditionsToSelection($selection, $args);

        return $selection->count('*');
    }
}
