<?php

namespace Tests\Feature;

use Efabrica\GraphQL\Drivers\WebonyxDriver;
use Efabrica\GraphQL\GraphQL;
use Efabrica\GraphQL\Helpers\DatabaseColumnTypeTransformer;
use Efabrica\GraphQL\Nette\Factories\NetteDatabaseResolverFactory;
use Efabrica\GraphQL\Nette\Schema\Loaders\NetteDatabaseSchemaLoader;
use Symfony\Component\String\Inflector\EnglishInflector;
use Tests\TestCase;

class FetchAllTest extends TestCase
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

    public function testCanFetchAllCategories(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                categories {
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
                        'id' => '1',
                        'name' => 'Category #1',
                    ],
                    [
                        'id' => '2',
                        'name' => 'Category #2',
                    ],
                    [
                        'id' => '3',
                        'name' => 'Category #3',
                    ],
                ],
            ],
        ], $result);
    }

    public function testCanFetchAllProducts(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                products {
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
                        'id' => 1,
                        'category_id' => 1,
                        'name' => 'Product #1',
                        'description' => 'Lorem ipsum',
                        'price' => 6.99,
                    ],
                    [
                        'id' => 2,
                        'category_id' => 1,
                        'name' => 'Product #2',
                        'description' => 'Dolor sit',
                        'price' => 5.69,
                    ],
                    [
                        'id' => 3,
                        'category_id' => null,
                        'name' => 'Product #3',
                        'description' => null,
                        'price' => 18.99,
                    ],
                    [
                        'id' => 4,
                        'category_id' => 2,
                        'name' => 'Product #4',
                        'description' => 'Dolor sit',
                        'price' => 12.49,
                    ],
                    [
                        'id' => 5,
                        'category_id' => 3,
                        'name' => 'Product #5',
                        'description' => 'Aster mor',
                        'price' => 16.2,
                    ],
                    [
                        'id' => 6,
                        'category_id' => 1,
                        'name' => 'Product #6',
                        'description' => null,
                        'price' => 32.0,
                    ],
                    [
                        'id' => 7,
                        'category_id' => null,
                        'name' => 'Product #7',
                        'description' => 'Loras dore',
                        'price' => 15.0,
                    ],
                    [
                        'id' => 8,
                        'category_id' => 2,
                        'name' => 'Product #8',
                        'description' => null,
                        'price' => 19.99,
                    ],
                    [
                        'id' => 9,
                        'category_id' => 1,
                        'name' => 'Product #9',
                        'description' => null,
                        'price' => 21.0,
                    ],
                    [
                        'id' => 10,
                        'category_id' => 3,
                        'name' => 'Product #10',
                        'description' => 'Mora de sito',
                        'price' => 22.0,
                    ],
                ],
            ],
        ], $result);
    }

    public function testCanFetchAllOrders(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                orders {
                    id
                    customer
                    address
                    created_at
                }
            }
            GQL
        );

        $this->assertSame([
            'data' => [
                'orders' => [
                    [
                        'id' => '1',
                        'customer' => 'John Doe',
                        'address' => '9998 High Street',
                        'created_at' => '2022-09-01 16:04:21',
                    ],
                    [
                        'id' => '2',
                        'customer' => 'Moe Lester',
                        'address' => '88 New Street',
                        'created_at' => '2022-09-11 17:16:43',
                    ],
                    [
                        'id' => '3',
                        'customer' => 'Jane Dane',
                        'address' => '96 Victoria Street',
                        'created_at' => '2022-09-16 07:38:18',
                    ],
                    [
                        'id' => '4',
                        'customer' => 'Kelly J Lozano',
                        'address' => '13 West Street',
                        'created_at' => '2022-09-06 09:20:15',
                    ],
                ],
            ],
        ], $result);
    }

    public function testCanFetchAllOrderProduct(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                order_product {
                    order_id
                    product_id
                    price
                }
            }
            GQL
        );

        $this->assertSame([
            'data' => [
                'order_product' => [
                    [
                        'order_id' => 1,
                        'product_id' => 1,
                        'price' => 6.99,
                    ],
                    [
                        'order_id' => 1,
                        'product_id' => 4,
                        'price' => 12.49,
                    ],
                    [
                        'order_id' => 1,
                        'product_id' => 7,
                        'price' => 15.00,
                    ],
                    [
                        'order_id' => 2,
                        'product_id' => 10,
                        'price' => 22.00,
                    ],
                    [
                        'order_id' => 2,
                        'product_id' => 6,
                        'price' => 32.00,
                    ],
                    [
                        'order_id' => 3,
                        'product_id' => 2,
                        'price' => 5.69,
                    ],
                    [
                        'order_id' => 3,
                        'product_id' => null,
                        'price' => 7.00,
                    ],
                    [
                        'order_id' => 3,
                        'product_id' => 6,
                        'price' => 32.00,
                    ],
                    [
                        'order_id' => 4,
                        'product_id' => 9,
                        'price' => 21.00,
                    ],
                    [
                        'order_id' => 4,
                        'product_id' => 7,
                        'price' => 15.00,
                    ],
                    [
                        'order_id' => 4,
                        'product_id' => 1,
                        'price' => 6.99,
                    ],
                    [
                        'order_id' => 4,
                        'product_id' => 2,
                        'price' => 5.69,
                    ],
                ],
            ],
        ], $result);
    }

    public function testCanLimitResults(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                products (
                    pagination: {
                        limit: 3
                        offset: 2
                    }
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
                        'id' => 3,
                        'category_id' => null,
                        'name' => 'Product #3',
                        'description' => null,
                        'price' => 18.99,
                    ],
                    [
                        'id' => 4,
                        'category_id' => 2,
                        'name' => 'Product #4',
                        'description' => 'Dolor sit',
                        'price' => 12.49,
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
