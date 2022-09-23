<?php

namespace Tests\Feature;

use Efabrica\GraphQL\Drivers\WebonyxDriver;
use Efabrica\GraphQL\GraphQL;
use Efabrica\GraphQL\Helpers\DatabaseColumnTypeTransformer;
use Efabrica\GraphQL\Nette\Factories\NetteDatabaseResolverFactory;
use Efabrica\GraphQL\Nette\Schema\Loaders\NetteDatabaseSchemaLoader;
use Symfony\Component\String\Inflector\EnglishInflector;
use Tests\TestCase;

class OrderConditionsTest extends TestCase
{
    private GraphQL $graphQL;

    protected function setUp(): void
    {
        $explorer = $this->createExplorer(__DIR__ . '/../Mock/data.sql');
        $schemaLoader = new NetteDatabaseSchemaLoader(
            $explorer,
            new NetteDatabaseResolverFactory($explorer),
            new DatabaseColumnTypeTransformer(),
            new EnglishInflector()
        );
        $driver = new WebonyxDriver($schemaLoader);
        $this->graphQL = new GraphQL($driver);
    }

    public function testCanOrderResults(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                categories (
                    order: {
                        key: "id"
                        order: DESC
                    }
                ) {
                    id
                    name
                }
            }
            GQL
        );

        $this->assertSame([
            'data' => [
                'categories' => [
                    [
                        'id' => '3',
                        'name' => 'Category #3',
                    ],
                    [
                        'id' => '2',
                        'name' => 'Category #2',
                    ],
                    [
                        'id' => '1',
                        'name' => 'Category #1',
                    ],
                ],
            ],
        ], $result);
    }

    public function testCandOrderByMultipleKeys(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                products (
                    order: [
                        {
                            key: "category_id"
                        }
                        {
                            key: "id"
                            order: DESC
                        }
                    ]
                ) {
                    id
                    category_id
                    name
                    description
                    price
                }
            }
            GQL
        );

        $this->assertSame([
            'data' => [
                'products' => [
                    [
                        'id' => 7,
                        'category_id' => null,
                        'name' => 'Product #7',
                        'description' => 'Loras dore',
                        'price' => 15.0,
                    ],
                    [
                        'id' => 3,
                        'category_id' => null,
                        'name' => 'Product #3',
                        'description' => null,
                        'price' => 18.99,
                    ],
                    [
                        'id' => 9,
                        'category_id' => 1,
                        'name' => 'Product #9',
                        'description' => null,
                        'price' => 21.0,
                    ],
                    [
                        'id' => 6,
                        'category_id' => 1,
                        'name' => 'Product #6',
                        'description' => null,
                        'price' => 32.0,
                    ],
                    [
                        'id' => 2,
                        'category_id' => 1,
                        'name' => 'Product #2',
                        'description' => 'Dolor sit',
                        'price' => 5.69,
                    ],
                    [
                        'id' => 1,
                        'category_id' => 1,
                        'name' => 'Product #1',
                        'description' => 'Lorem ipsum',
                        'price' => 6.99,
                    ],
                    [
                        'id' => 8,
                        'category_id' => 2,
                        'name' => 'Product #8',
                        'description' => null,
                        'price' => 19.99,
                    ],
                    [
                        'id' => 4,
                        'category_id' => 2,
                        'name' => 'Product #4',
                        'description' => 'Dolor sit',
                        'price' => 12.49,
                    ],
                    [
                        'id' => 10,
                        'category_id' => 3,
                        'name' => 'Product #10',
                        'description' => 'Mora de sito',
                        'price' => 22.0,
                    ],
                    [
                        'id' => 5,
                        'category_id' => 3,
                        'name' => 'Product #5',
                        'description' => 'Aster mor',
                        'price' => 16.2,
                    ],
                ],
            ],
        ], $result);
    }
}
