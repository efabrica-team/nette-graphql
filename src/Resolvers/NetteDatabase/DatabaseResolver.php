<?php

namespace Efabrica\GraphQL\Nette\Resolvers\NetteDatabase;

use Efabrica\GraphQL\Exceptions\ResolverException;
use Efabrica\GraphQL\Helpers\AdditionalResponseData;
use Efabrica\GraphQL\Nette\Schema\Custom\Types\LiteralType;
use Efabrica\GraphQL\Resolvers\ResolverInterface;
use Efabrica\GraphQL\Schema\Custom\Arguments\ConditionsArgument;
use Efabrica\GraphQL\Schema\Custom\Arguments\OrderArgument;
use Efabrica\GraphQL\Schema\Custom\Arguments\PaginationArgument;
use Efabrica\GraphQL\Schema\Custom\Fields\GroupField;
use Efabrica\GraphQL\Schema\Custom\Fields\HavingAndField;
use Efabrica\GraphQL\Schema\Custom\Fields\HavingOrField;
use Efabrica\GraphQL\Schema\Custom\Fields\WhereAndField;
use Efabrica\GraphQL\Schema\Custom\Fields\WhereOrField;
use Efabrica\GraphQL\Schema\Custom\Types\GroupType;
use Efabrica\GraphQL\Schema\Custom\Types\HavingType;
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
            $order = $orderArgs[OrderArgument::FIELD_ORDER] ?? null;

            if ($order === OrderDirectionEnum::RAND) {
                $selection->order('RAND()');
                continue;
            }

            if ($orderBy === null) {
                continue;
            }

            $orderQuery = '';
            $parameters = [];

            if ($this->firstParty) {
                $orderQuery = $orderBy;
            } else {
                $orderQuery .= ' ?name';
                $parameters[] = $orderBy;
            }

            if ($order === OrderDirectionEnum::DESC) {
                $orderQuery .= ' DESC';
            } else {
                $orderQuery .= ' ASC';
            }
            $selection->order($orderQuery, ...$parameters);
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

        [$havingAndQuery, $havingAndParameters] = $this->buildHavingQuery(
            $selection,
            $args[ConditionsArgument::NAME][HavingAndField::NAME] ?? [],
        );

        [$havingOrQuery, $havingOrParameters] = $this->buildHavingQuery(
            $selection,
            $args[ConditionsArgument::NAME][HavingOrField::NAME] ?? [],
            'OR'
        );

        $havingQuery = implode(' AND ', array_filter([$havingAndQuery, $havingOrQuery]));
        $havingParameters = array_merge($havingAndParameters, $havingOrParameters);

        if ($havingQuery) {
            $selection->having($havingQuery, ...$havingParameters);
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
                $value = $condition[WhereType::FIELD_VALUE] ?? null;

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
                } elseif(in_array($comparator, [WhereComparatorEnum::NULL, WhereComparatorEnum::NOT_NULL], true)) {
                    switch ($comparator) {
                        case WhereComparatorEnum::NULL:
                            $conditionWhereQuery .= ' IS NULL';
                            break;
                        case WhereComparatorEnum::NOT_NULL:
                            $conditionWhereQuery .= ' IS NOT NULL';
                            break;
                    }
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

                    if (LiteralType::isLiteral($value)) {
                        if (!$this->firstParty) {
                            throw new ResolverException("Literal values are not allowed when not in first party mode.");
                        }
                        $conditionWhereQuery .= ' ' . LiteralType::getLiteralValue($value);
                    } else {
                        $conditionWhereQuery .= ' ?';
                        $parameters[] = $value ?? 'NULL';
                    }
                }
            }

            $whereQuery .= $conditionWhereQuery;
        }

        return [$whereQuery, $parameters];
    }

    private function buildHavingQuery(Selection $selection, array $conditions, string $type = 'AND'): array
    {
        $havingQuery = '';
        $parameters = [];

        foreach ($conditions as $condition) {
            $conditionHavingQuery = '';
            if (!empty($havingQuery)) {
                $conditionHavingQuery .= ' ' . $type;
            }

            $andSubHaving = $condition[HavingAndField::NAME] ?? [];
            $orSubHaving = $condition[HavingOrField::NAME] ?? [];

            if (count($andSubHaving) || count($orSubHaving)) {
                $conditionAndHavingQuery = null;
                $conditionOrHavingQuery = null;

                [$subquery, $subparameters] = $this->buildHavingQuery($selection, $andSubHaving);
                if ($subquery) {
                    $conditionAndHavingQuery .= ' (' . $subquery . ')';
                    $parameters = array_merge($parameters, $subparameters);
                }

                [$subquery, $subparameters] = $this->buildHavingQuery($selection, $orSubHaving, 'OR');
                if ($subquery) {
                    $conditionOrHavingQuery .= ' (' . $subquery . ')';
                    $parameters = array_merge($parameters, $subparameters);
                }

                $conditionHavingQuery .= implode(
                    ' AND ',
                    array_filter([$conditionAndHavingQuery, $conditionOrHavingQuery])
                );
            } elseif ($condition[HavingType::FIELD_COLUMN] !== null) {
                if ($this->firstParty) {
                    // SQL INJECTION
                    $conditionHavingQuery .= ' ' . $condition[HavingType::FIELD_COLUMN];
                } else {
                    // Will not join tables automaticaly when using variables nor will allow aggregate functions.
                    $conditionHavingQuery .= ' ?name';
                    $parameters[] = $condition[HavingType::FIELD_COLUMN];
                }

                $comparator = $condition[HavingType::FIELD_COMPARATOR];
                $value = $condition[HavingType::FIELD_VALUE] ?? null;

                if (in_array($comparator, [WhereComparatorEnum::IN, WhereComparatorEnum::NOT_IN], true)) {
                    switch ($comparator) {
                        case WhereComparatorEnum::IN:
                            $conditionHavingQuery .= ' IN (?)';
                            break;
                        case WhereComparatorEnum::NOT_IN:
                            if (!$value || !count($value = array_filter($value))) {
                                continue 2;
                            }
                            $conditionHavingQuery .= ' NOT IN (?)';
                            break;
                    }
                    $parameters[] = $value;
                } elseif(in_array($comparator, [WhereComparatorEnum::NULL, WhereComparatorEnum::NOT_NULL], true)) {
                    switch ($comparator) {
                        case WhereComparatorEnum::NULL:
                            $conditionHavingQuery .= ' IS NULL';
                            break;
                        case WhereComparatorEnum::NOT_NULL:
                            $conditionHavingQuery .= ' IS NOT NULL';
                            break;
                    }
                } else {
                    $value = $value ? reset($value) : null;
                    switch ($comparator) {
                        case WhereComparatorEnum::EQUAL:
                            $conditionHavingQuery .= ' =';
                            break;
                        case WhereComparatorEnum::NOT_EQUAL:
                            $conditionHavingQuery .= ' !=';
                            break;
                        case WhereComparatorEnum::LESS_THAN:
                            $conditionHavingQuery .= ' <';
                            break;
                        case WhereComparatorEnum::LESS_THAN_EQUAL:
                            $conditionHavingQuery .= ' <=';
                            break;
                        case WhereComparatorEnum::MORE_THAN:
                            $conditionHavingQuery .= ' >';
                            break;
                        case WhereComparatorEnum::MORE_THAN_EQUAL:
                            $conditionHavingQuery .= ' >=';
                            break;
                        case WhereComparatorEnum::LIKE:
                            $conditionHavingQuery .= ' LIKE';
                            break;
                        case WhereComparatorEnum::NOT_LIKE:
                            $conditionHavingQuery .= ' NOT LIKE';
                            break;
                        default:
                            throw new ResolverException("'$comparator' is not a valid comparator.");
                    }

                    if (LiteralType::isLiteral($value)) {
                        if (!$this->firstParty) {
                            throw new ResolverException('Literal values are not allowed when not in first party mode.');
                        }
                        $conditionHavingQuery .= ' ' . LiteralType::getLiteralValue($value);
                    } else {
                        $conditionHavingQuery .= ' ?';
                        $parameters[] = $value ?? 'NULL';
                    }
                }
            }

            $havingQuery .= $conditionHavingQuery;
        }

        return [$havingQuery, $parameters];
    }

    private function applyGroupToSelection(Selection $selection, array $conditions): void
    {
        $groupBy = [];
        foreach ($conditions as $condition) {
            $groupBy[] = $condition[GroupType::FIELD_COLUMN];
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
