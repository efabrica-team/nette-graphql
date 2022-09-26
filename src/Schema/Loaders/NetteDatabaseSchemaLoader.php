<?php

namespace Efabrica\GraphQL\Nette\Schema\Loaders;

use Efabrica\GraphQL\Helpers\DatabaseColumnTypeTransformer;
use Efabrica\GraphQL\Nette\Factories\NetteDatabaseResolverFactory;
use Efabrica\GraphQL\Schema\Custom\Arguments\ConditionsArgument;
use Efabrica\GraphQL\Schema\Custom\Arguments\OrderArgument;
use Efabrica\GraphQL\Schema\Custom\Arguments\PaginationArgument;
use Efabrica\GraphQL\Schema\Definition\Fields\Field;
use Efabrica\GraphQL\Schema\Definition\Schema;
use Efabrica\GraphQL\Schema\Definition\Types\ObjectType;
use Efabrica\GraphQL\Schema\Definition\Types\Scalar\IDType;
use Efabrica\GraphQL\Schema\Definition\Types\Scalar\IntType;
use Efabrica\GraphQL\Schema\Loaders\SchemaLoaderInterface;
use Nette\Database\Explorer;
use Nette\Database\IStructure;
use Symfony\Component\String\Inflector\InflectorInterface;

final class NetteDatabaseSchemaLoader implements SchemaLoaderInterface
{
    private Explorer $explorer;

    private NetteDatabaseResolverFactory $resolverFactory;

    private DatabaseColumnTypeTransformer $databaseColumnTypeTransformer;

    private InflectorInterface $inflector;

    private ?array $exceptTables = null;

    private ?array $onlyTables = null;

    private array $exceptTableColumns = [];

    private array $onlyTableColumns = [];

    private string $belongsToReplacePattern = '/_(?!.*_).*/';

    private bool $forcedHasManyLongName = true;

    public function __construct(
        Explorer $explorer,
        NetteDatabaseResolverFactory $resolverFactory,
        DatabaseColumnTypeTransformer $databaseColumnTypeTransformer,
        InflectorInterface $inflector
    ) {
        $this->explorer = $explorer;
        $this->resolverFactory = $resolverFactory;
        $this->databaseColumnTypeTransformer = $databaseColumnTypeTransformer;
        $this->inflector = $inflector;
    }

    public function getSchema(): Schema
    {
        $structure = $this->explorer->getStructure();

        $query = new ObjectType('query');

        $paginationArgument = new PaginationArgument();
        $orderArgument = new OrderArgument();
        $conditions = new ConditionsArgument();

        $baseObjectTypes = [];
        foreach ($this->getTables($structure) as $table) {
            $objectName = $this->inflector->singularize($table);
            $objectType = new ObjectType(reset($objectName) ?: $table);

            foreach ($this->getColumns($structure, $table) as $column) {
                $columnType = $column['primary'] ? new IDType() : $this->databaseColumnTypeTransformer->handle(
                    $column['nativetype']
                );

                $field = (new Field($column['name'], $columnType))
                    ->setDescription($column['vendor']['comment'] ?? null)
                    ->setSetting('column_name', $column['name']);

                if ($column['nullable']) {
                    $field->setNullable();
                }

                $objectType->addField($field);
            }

            $query->addField(
                (new Field($table, $objectType))
                    ->setMulti()
                    ->addArgument($paginationArgument)
                    ->addArgument($orderArgument)
                    ->addArgument($orderArgument)
                    ->addArgument($conditions)
                    ->setResolver($this->resolverFactory->createTableResolver())
                    ->setSetting('table_name', $table)
            );

            $query->addField(
                (new Field($table . '_count', new IntType()))
                    ->addArgument($orderArgument)
                    ->addArgument($conditions)
                    ->setResolver($this->resolverFactory->createTableCountResolver())
                    ->setSetting('table_name', $table)
            );

            $baseObjectTypes[$table] = $objectType;
        }

        foreach ($baseObjectTypes as $tableName => $objectType) {
            foreach ($structure->getBelongsToReference($tableName) as $columnName => $relatedTable) {
                if (!in_array($columnName, array_column($this->getColumns($structure, $tableName), 'name'), true)) {
                    continue;
                }

                if (!$relatedObject = $baseObjectTypes[$relatedTable] ?? null) {
                    continue;
                }

                $isNullable = false;
                foreach ($this->getColumns($structure, $tableName) as $column) {
                    if ($column['name'] === $columnName) {
                        $isNullable = $column['nullable'];
                    }
                }

                $objectType->addField(
                    (new Field(preg_replace($this->belongsToReplacePattern, '', $columnName), $relatedObject))
                        ->setNullable($isNullable)
                        ->setResolver($this->resolverFactory->createBelongsToResolver())
                        ->setSetting('table_name', $relatedTable)
                        ->setSetting('referencing_column', $columnName)
                );
            }

            foreach ($structure->getHasManyReference($tableName) as $relatedTable => $referencingColumns) {
                foreach ($referencingColumns as $referencingColumn) {
                    if (!in_array(
                        $referencingColumn,
                        array_column($this->getColumns($structure, $relatedTable), 'name'),
                        true
                    )) {
                        continue;
                    }

                    if (!$relatedObject = $baseObjectTypes[$relatedTable] ?? null) {
                        continue;
                    }

                    $fieldName = $this->forcedHasManyLongName || count($referencingColumns) > 1
                        ? $relatedTable . '__' . $referencingColumn
                        : $relatedTable;

                    $objectType->addField(
                        (new Field($fieldName, $relatedObject))
                            ->setMulti()
                            ->addArgument($paginationArgument)
                            ->addArgument($orderArgument)
                            ->addArgument($conditions)
                            ->setResolver($this->resolverFactory->createHasManyResolver())
                            ->setSetting('table_name', $relatedTable)
                            ->setSetting('referencing_column', $referencingColumn)
                    );

                    $objectType->addField(
                        (new Field($fieldName . '_count', new IntType()))
                            ->addArgument($orderArgument)
                            ->addArgument($conditions)
                            ->setResolver($this->resolverFactory->createHasManyCountResolver())
                            ->setSetting('table_name', $relatedTable)
                            ->setSetting('referencing_column', $referencingColumn)
                    );
                }
            }
        }

        return (new Schema())
            ->setQuery($query);
    }

    public function getExceptTables(): ?array
    {
        return $this->exceptTables;
    }

    public function addExceptTable(string $exceptTable): self
    {
        $this->exceptTables[] = $exceptTable;
        return $this;
    }

    public function setExceptTables(?array $exceptTables): self
    {
        $this->exceptTables = $exceptTables;
        return $this;
    }

    public function getOnlyTables(): ?array
    {
        return $this->onlyTables;
    }

    public function addOnlyTable(string $onlyTable): self
    {
        $this->onlyTables[] = $onlyTable;
        return $this;
    }

    public function setOnlyTables(?array $onlyTables): self
    {
        $this->onlyTables = $onlyTables;
        return $this;
    }

    public function getExceptTableColumns(string $table): ?array
    {
        return $this->exceptTableColumns[$table] ?? null;
    }

    public function addExceptTableColumn(string $table, string $exceptTableColumn): self
    {
        $this->exceptTableColumns[$table][] = $exceptTableColumn;
        return $this;
    }

    public function setExceptTableColumns(string $table, ?array $exceptTableColumns): self
    {
        $this->exceptTableColumns[$table] = $exceptTableColumns;
        return $this;
    }

    public function getOnlyTableColumns(string $table): ?array
    {
        return $this->onlyTableColumns[$table] ?? null;
    }

    public function addOnlyTableColumn(string $table, string $onlyTableColumn): self
    {
        $this->onlyTableColumns[$table][] = $onlyTableColumn;
        return $this;
    }

    public function setOnlyTableColumns(string $table, ?array $onlyTableColumns): self
    {
        $this->onlyTableColumns[$table] = $onlyTableColumns;
        return $this;
    }

    public function isForcedHasManyLongName(): bool
    {
        return $this->forcedHasManyLongName;
    }

    public function setForcedHasManyLongName(bool $forcedHasManyLongName = true): self
    {
        $this->forcedHasManyLongName = $forcedHasManyLongName;
        return $this;
    }

    public function getBelongsToReplacePattern(): string
    {
        return $this->belongsToReplacePattern;
    }

    public function setBelongsToReplacePattern(string $belongsToReplacePattern): self
    {
        $this->belongsToReplacePattern = $belongsToReplacePattern;
        return $this;
    }

    private function getTables(IStructure $structure): array
    {
        $tables = array_column($structure->getTables(), 'name');

        if ($this->getExceptTables()) {
            $tables = array_diff($tables, $this->getExceptTables());
        }

        if ($this->getOnlyTables()) {
            $tables = array_intersect($tables, $this->getOnlyTables());
        }

        return $tables;
    }

    private function getColumns(IStructure $structure, string $table): array
    {
        $columns = $structure->getColumns($table);

        $exceptTableColumns = $this->getExceptTableColumns($table);
        if ($exceptTableColumns) {
            foreach ($columns as $key => $column) {
                if (in_array($column['name'], $exceptTableColumns, true)) {
                    unset($columns[$key]);
                }
            }
        }

        $onlyTableColumns = $this->getOnlyTableColumns($table);
        if ($onlyTableColumns) {
            foreach ($columns as $key => $column) {
                if (!in_array($column['name'], $onlyTableColumns, true)) {
                    unset($columns[$key]);
                }
            }
        }

        return $columns;
    }
}
