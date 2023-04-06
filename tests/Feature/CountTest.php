<?php

namespace Tests\Feature;

use Efabrica\GraphQL\Drivers\WebonyxDriver;
use Efabrica\GraphQL\GraphQL;
use Efabrica\GraphQL\Helpers\AdditionalResponseData;
use Efabrica\GraphQL\Helpers\DatabaseColumnTypeTransformer;
use Efabrica\GraphQL\Nette\Factories\NetteDatabaseResolverFactory;
use Efabrica\GraphQL\Nette\Schema\Loaders\NetteDatabaseSchemaLoader;
use Symfony\Component\String\Inflector\EnglishInflector;
use Tests\TestCase;

class CountTest extends TestCase
{
    private GraphQL $graphQL;

    protected function setUp(): void
    {
        $explorer = $this->createExplorer(__DIR__ . '/../Mock/data.sql');
        $additionalResponseData = new AdditionalResponseData();
        $schemaLoader = new NetteDatabaseSchemaLoader(
            $explorer,
            new NetteDatabaseResolverFactory($explorer, $additionalResponseData),
            new DatabaseColumnTypeTransformer(),
            new EnglishInflector()
        );
        $driver = new WebonyxDriver($schemaLoader, $additionalResponseData);
        $this->graphQL = new GraphQL($driver);
    }

    public function testCanCountTables(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                categories_count
                products_count
                orders_count
                order_product_count
            }
            GQL
        );

        $this->assertSame([
            'data' => [
                'categories_count' => 3,
                'products_count' => 10,
                'orders_count' => 4,
                'order_product_count' => 12,
            ],
        ], $result);
    }

    public function testCanCountWithConditionsTables(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                categories_count (
                    conditions: {
                        where: {
                            column: "id"
                            value: ["1", "3"]
                            comparator: IN
                        }
                    }
                )
            }
            GQL
        );

        $this->assertSame([
            'data' => [
                'categories_count' => 2,
            ],
        ], $result);
    }

    public function testCanCountRelations(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                categories {
                    id
                    products__category_id_count
                }
            }
            GQL
        );

        $this->assertSame([
            'data' => [
                'categories' => [
                    [
                        'id' => '1',
                        'products__category_id_count' => 4
                    ],
                    [
                        'id' => '2',
                        'products__category_id_count' => 2
                    ],
                    [
                        'id' => '3',
                        'products__category_id_count' => 2
                    ],
                ]
            ],
        ], $result);
    }

    public function testCanCountRelationsWithConditions(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                categories {
                    id
                    products__category_id_count (
                        conditions: {
                            where: {
                                column: "id"
                                value: ["1", "3", "5"]
                                comparator: IN
                            }
                        }
                    )
                }
            }
            GQL
        );

        $this->assertSame([
            'data' => [
                'categories' => [
                    [
                        'id' => '1',
                        'products__category_id_count' => 1
                    ],
                    [
                        'id' => '2',
                        'products__category_id_count' => 0
                    ],
                    [
                        'id' => '3',
                        'products__category_id_count' => 1
                    ],
                ]
            ],
        ], $result);
    }
}
