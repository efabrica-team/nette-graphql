<?php

namespace Efabrica\GraphQL\Nette\Resolvers\NetteDatabase;

use Efabrica\GraphQL\Exceptions\ResolverException;
use Efabrica\GraphQL\Helpers\AdditionalResponseData;
use Efabrica\GraphQL\Resolvers\ResolverInterface;
use Efabrica\GraphQL\Schema\Custom\Arguments\ConditionsArgument;
use Efabrica\GraphQL\Schema\Custom\Arguments\OrderArgument;
use Efabrica\GraphQL\Schema\Custom\Arguments\PaginationArgument;
use Efabrica\GraphQL\Schema\Custom\Fields\GroupField;
use Efabrica\GraphQL\Schema\Custom\Fields\WhereAndField;
use Efabrica\GraphQL\Schema\Custom\Fields\WhereOrField;
use Efabrica\GraphQL\Schema\Custom\Types\OrderDirectionEnum;
use Efabrica\GraphQL\Schema\Custom\Types\WhereComparatorEnum;
use Efabrica\GraphQL\Schema\Custom\Types\WhereType;
use Nette\Database\Connection;
use Nette\Database\Explorer;
use Nette\Database\ResultSet;
use Nette\Database\Table\Selection;
use PDOException;

abstract class DatabaseResolver implements ResolverInterface
{
    protected Explorer $explorer;

    protected AdditionalResponseData $additionalResponseData;

    protected bool $firstParty;

    public function __construct(Explorer $explorer, AdditionalResponseData $additionalResponseData, $firstParty)
    {
        $this->explorer = $explorer;
        $this->additionalResponseData = $additionalResponseData;
        $this->firstParty = $firstParty;

        $this->explorer->getConnection()->onQuery['log_executed_graphql_queries'] = function (Connection $connection, $result) {
            if ($result instanceof ResultSet) {
                $this->additionalResponseData->debugData['sql'][] = '(' . sprintf('%0.3f', $result->getTime() * 1000) . ' ms) ' . $result->getQueryString();
            } elseif ($result instanceof PDOException) {
                $this->additionalResponseData->debugData['sql'][] = $result->queryString ?? '';
            }
        };
    }

    protected function applyPaginationToSelection(Selection $selection, array $args): void
    {
        $limit = $args[PaginationArgument::NAME][PaginationArgument::FIELD_LIMIT] ?? null;
        $offset = $args[PaginationArgument::NAME][PaginationArgument::FIELD_OFFSET] ?? null;
        $selection->limit($limit, $offset);
    }

    protected function applyOrderToSelection(Selection $selection, array $args): void
    {
        foreach ($args[OrderArgument::NAME] ?? [] as $orderArgs) {
            $orderBy = $orderArgs[OrderArgument::FIELD_KEY] ?? null;
            if ($orderBy) {
                $order = $orderArgs[OrderArgument::FIELD_ORDER] ?? null;

                if ($order === OrderDirectionEnum::RAND) {
                    $selection->order('RAND()');
                } elseif ($order === OrderDirectionEnum::DESC) {
                    $selection->order('?name DESC', $orderBy);
                } else {
                    $selection->order('?name ASC', $orderBy);
                }
            }
        }
    }

    protected function applyConditionsToSelection(Selection $selection, array $args): void
    {
        [$whereAndQuery, $whereAndParameters] = $this->buildWhereQuery(
            $selection,
            $args[ConditionsArgument::NAME][WhereAndField::NAME] ?? [],
        );

        [$whereOrQuery, $whereOrParameters] = $this->buildWhereQuery(
            $selection,
            $args[ConditionsArgument::NAME][WhereOrField::NAME] ?? [],
            'OR'
        );

        $whereQuery = implode(' AND ', array_filter([$whereAndQuery, $whereOrQuery]));
        $whereParameters = array_merge($whereAndParameters, $whereOrParameters);

        if ($whereQuery) {
            $selection->where($whereQuery, ...$whereParameters);
        }
        $this->applyGroupToSelection($selection, $args[ConditionsArgument::NAME][GroupField::NAME] ?? []);
    }

    private function buildWhereQuery(Selection $selection, array $conditions, string $type = 'AND'): array
    {
        $whereQuery = '';
        $parameters = [];

        foreach ($conditions as $condition) {
            $conditionWhereQuery = '';
            if (!empty($whereQuery)) {
                $conditionWhereQuery .= ' ' . $type;
            }

            $andSubWhere = $condition[WhereAndField::NAME] ?? [];
            $orSubWhere = $condition[WhereOrField::NAME] ?? [];

            if (count($andSubWhere) || count($orSubWhere)) {
                $conditionAndWhereQuery = null;
                $conditionOrWhereQuery = null;

                [$subquery, $subparameters] = $this->buildWhereQuery($selection, $andSubWhere);
                if ($subquery) {
                    $conditionAndWhereQuery .= ' (' . $subquery . ')';
                    $parameters = array_merge($parameters, $subparameters);
                }

                [$subquery, $subparameters] = $this->buildWhereQuery($selection, $orSubWhere, 'OR');
                if ($subquery) {
                    $conditionOrWhereQuery .= ' (' . $subquery . ')';
                    $parameters = array_merge($parameters, $subparameters);
                }

                $conditionWhereQuery .= implode(
                    ' AND ',
                    array_filter([$conditionAndWhereQuery, $conditionOrWhereQuery])
                );
            } elseif ($condition[WhereType::FIELD_COLUMN] !== null) {
                if ($this->firstParty) {
                    // SQL INJECTION
                    $conditionWhereQuery .= ' ' . $condition[WhereType::FIELD_COLUMN];
                } else {
                    // Will not join tables automaticaly when using variables.
                    $conditionWhereQuery .= ' ?name';
                    $parameters[] = $condition[WhereType::FIELD_COLUMN];
                }

                $comparator = $condition[WhereType::FIELD_COMPARATOR];
                $value = $condition[WhereType::FIELD_VALUE];

                if (in_array($comparator, [WhereComparatorEnum::IN, WhereComparatorEnum::NOT_IN], true)) {
                    switch ($comparator) {
                        case WhereComparatorEnum::IN:
                            $conditionWhereQuery .= ' IN (?)';
                            break;
                        case WhereComparatorEnum::NOT_IN:
                            if (!$value || !count($value = array_filter($value))) {
                                continue 2;
                            }
                            $conditionWhereQuery .= ' NOT IN (?)';
                            break;
                    }
                    $parameters[] = $value;
                } else {
                    $value = $value ? reset($value) : null;
                    switch ($comparator) {
                        case WhereComparatorEnum::EQUAL:
                            $conditionWhereQuery .= ' =';
                            break;
                        case WhereComparatorEnum::NOT_EQUAL:
                            $conditionWhereQuery .= ' !=';
                            break;
                        case WhereComparatorEnum::LESS_THAN:
                            $conditionWhereQuery .= ' <';
                            break;
                        case WhereComparatorEnum::LESS_THAN_EQUAL:
                            $conditionWhereQuery .= ' <=';
                            break;
                        case WhereComparatorEnum::MORE_THAN:
                            $conditionWhereQuery .= ' >';
                            break;
                        case WhereComparatorEnum::MORE_THAN_EQUAL:
                            $conditionWhereQuery .= ' >=';
                            break;
                        case WhereComparatorEnum::LIKE:
                            $conditionWhereQuery .= ' LIKE';
                            break;
                        case WhereComparatorEnum::NOT_LIKE:
                            $conditionWhereQuery .= ' NOT LIKE';
                            break;
                        default:
                            throw new ResolverException("'$comparator' is not a valid comparator.");
                    }

                    $conditionWhereQuery .= ' ?';
                    $parameters[] = $value ?? 'NULL';
                }
            }

            $whereQuery .= $conditionWhereQuery;
        }

        return [$whereQuery, $parameters];
    }

    private function applyGroupToSelection(Selection $selection, array $conditions): void
    {
        $groupBy = [];
        foreach ($conditions as $condition) {
            $groupBy[] = $condition[GroupField::FIELD_COLUMN];
        }

        if ($this->firstParty) {
            // SQL INJECTION
            $selection->group(implode(', ', $groupBy));
        } else {
            // Will not join tables automaticaly when using variables.
            $selection->group(implode(', ', array_fill(0, count($groupBy), '?name')), ...$groupBy);
        }
    }
}
