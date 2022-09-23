<?php

namespace Efabrica\GraphQL\Nette\Resolvers\NetteDatabase;

use Efabrica\GraphQL\Schema\Definition\ResolveInfo;
use Nette\Database\Table\ActiveRow;

final class BelongsToResolver extends DatabaseResolver
{
    /**
     * @param ActiveRow $parentValue
     */
    public function __invoke($parentValue, array $args, ResolveInfo $resolveInfo): ?ActiveRow
    {
        return $parentValue->ref(
            $resolveInfo->getField()->getSetting('table_name'),
            $resolveInfo->getField()->getSetting('referencing_column')
        );
    }
}
