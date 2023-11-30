<?php

namespace Efabrica\GraphQL\Nette\Resolvers\NetteDatabase;

use Efabrica\GraphQL\Exceptions\ResolverException;
use Efabrica\GraphQL\Nette\Schema\Loaders\Helpers\MorphRelationDefinition;
use Efabrica\GraphQL\Schema\Definition\ResolveInfo;
use Nette\Database\Table\ActiveRow;
use Throwable;

final class MorphToResolver extends DatabaseResolver
{
    /**
     * @param ActiveRow $parentValue
     *
     * @throws ResolverException
     */
    public function __invoke($parentValue, array $args, ResolveInfo $resolveInfo): ?ActiveRow
    {
        /** @var MorphRelationDefinition $morphRelationDefinition */
        $morphRelationDefinition = $resolveInfo->getField()->getSetting('morph_relation_definition');
        try {
            return $parentValue->ref(
                $parentValue[$morphRelationDefinition->getTypeColumn()],
                $morphRelationDefinition->getIdColumn()
            );
        } catch (Throwable $e) {
            throw new ResolverException('There was an error while executing the query', 0, $e, $e);
        }
    }
}
