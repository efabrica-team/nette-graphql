<?php

namespace Tests\Feature;

use Efabrica\GraphQL\Drivers\WebonyxDriver;
use Efabrica\GraphQL\GraphQL;
use Efabrica\GraphQL\Helpers\DatabaseColumnTypeTransformer;
use Efabrica\GraphQL\Nette\Factories\NetteDatabaseResolverFactory;
use Efabrica\GraphQL\Nette\Schema\Loaders\NetteDatabaseSchemaLoader;
use Symfony\Component\String\Inflector\EnglishInflector;
use Tests\TestCase;

class FetchAllWithRelationsTest extends TestCase
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
                    products__category_id {
                        id
                        name
                        description
                        price
                    }
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
                        'products__category_id' => [
                            [
                                'id' => 1,
                                'name' => 'Product #1',
                                'description' => 'Lorem ipsum',
                                'price' => 6.99,
                            ],
                            [
                                'id' => 2,
                                'name' => 'Product #2',
                                'description' => 'Dolor sit',
                                'price' => 5.69,
                            ],
                            [
                                'id' => 6,
                                'name' => 'Product #6',
                                'description' => null,
                                'price' => 32.0,
                            ],
                            [
                                'id' => 9,
                                'name' => 'Product #9',
                                'description' => null,
                                'price' => 21.0,
                            ],
                        ],
                    ],
                    [
                        'id' => '2',
                        'name' => 'Category #2',
                        'products__category_id' => [
                            [
                                'id' => 4,
                                'name' => 'Product #4',
                                'description' => 'Dolor sit',
                                'price' => 12.49,
                            ],
                            [
                                'id' => 8,
                                'name' => 'Product #8',
                                'description' => null,
                                'price' => 19.99,
                            ],
                        ],
                    ],
                    [
                        'id' => '3',
                        'name' => 'Category #3',
                        'products__category_id' => [
                            [
                                'id' => 5,
                                'name' => 'Product #5',
                                'description' => 'Aster mor',
                                'price' => 16.2,
                            ],
                            [
                                'id' => 10,
                                'name' => 'Product #10',
                                'description' => 'Mora de sito',
                                'price' => 22.0,
                            ],
                        ],
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
                    name
                    description
                    price
                    category {
                        id
                        name
                    }
                    order_product__product_id {
                        order_id
                        price
                    }
                }
            }
            GQL
        );

        $this->assertSame([
            'data' => [
                'products' => [
                    [
                        'id' => 1,
                        'name' => 'Product #1',
                        'description' => 'Lorem ipsum',
                        'price' => 6.99,
                        'category' => [
                            'id' => '1',
                            'name' => 'Category #1',
                        ],
                        'order_product__product_id' => [
                            [
                                'order_id' => 1,
                                'price' => 6.99,
                            ],
                            [
                                'order_id' => 4,
                                'price' => 6.99,
                            ],
                        ],
                    ],
                    [
                        'id' => 2,
                        'name' => 'Product #2',
                        'description' => 'Dolor sit',
                        'price' => 5.69,
                        'category' => [
                            'id' => '1',
                            'name' => 'Category #1',
                        ],
                        'order_product__product_id' => [
                            [
                                'order_id' => 3,
                                'price' => 5.69,
                            ],
                            [
                                'order_id' => 4,
                                'price' => 5.69,
                            ],
                        ],
                    ],
                    [
                        'id' => 3,
                        'name' => 'Product #3',
                        'description' => null,
                        'price' => 18.99,
                        'category' => null,
                        'order_product__product_id' => [],
                    ],
                    [
                        'id' => 4,
                        'name' => 'Product #4',
                        'description' => 'Dolor sit',
                        'price' => 12.49,
                        'category' => [
                            'id' => '2',
                            'name' => 'Category #2',
                        ],
                        'order_product__product_id' => [
                            [
                                'order_id' => 1,
                                'price' => 12.49,
                            ],
                        ],
                    ],
                    [
                        'id' => 5,
                        'name' => 'Product #5',
                        'description' => 'Aster mor',
                        'price' => 16.2,
                        'category' => [
                            'id' => '3',
                            'name' => 'Category #3',
                        ],
                        'order_product__product_id' => [],
                    ],
                    [
                        'id' => 6,
                        'name' => 'Product #6',
                        'description' => null,
                        'price' => 32.0,
                        'category' => [
                            'id' => '1',
                            'name' => 'Category #1',
                        ],
                        'order_product__product_id' => [
                            [
                                'order_id' => 2,
                                'price' => 32.00,
                            ],
                            [
                                'order_id' => 3,
                                'price' => 32.00,
                            ],
                        ],
                    ],
                    [
                        'id' => 7,
                        'name' => 'Product #7',
                        'description' => 'Loras dore',
                        'price' => 15.0,
                        'category' => null,
                        'order_product__product_id' => [
                            [
                                'order_id' => 1,
                                'price' => 15.00,
                            ],
                            [
                                'order_id' => 4,
                                'price' => 15.00,
                            ],
                        ],
                    ],
                    [
                        'id' => 8,
                        'name' => 'Product #8',
                        'description' => null,
                        'price' => 19.99,
                        'category' => [
                            'id' => '2',
                            'name' => 'Category #2',
                        ],
                        'order_product__product_id' => [],
                    ],
                    [
                        'id' => 9,
                        'name' => 'Product #9',
                        'description' => null,
                        'price' => 21.0,
                        'category' => [
                            'id' => '1',
                            'name' => 'Category #1',
                        ],
                        'order_product__product_id' => [
                            [
                                'order_id' => 4,
                                'price' => 21.00,
                            ],
                        ],
                    ],
                    [
                        'id' => 10,
                        'name' => 'Product #10',
                        'description' => 'Mora de sito',
                        'price' => 22.0,
                        'category' => [
                            'id' => '3',
                            'name' => 'Category #3',
                        ],
                        'order_product__product_id' => [
                            [
                                'order_id' => 2,
                                'price' => 22.00,
                            ],
                        ],
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
                    order_product__order_id {
                        product_id
                        price
                    }
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
                        'order_product__order_id' => [
                            [
                                'product_id' => 1,
                                'price' => 6.99,
                            ],
                            [
                                'product_id' => 4,
                                'price' => 12.49,
                            ],
                            [
                                'product_id' => 7,
                                'price' => 15.00,
                            ],
                        ],
                    ],
                    [
                        'id' => '2',
                        'customer' => 'Moe Lester',
                        'address' => '88 New Street',
                        'created_at' => '2022-09-11 17:16:43',
                        'order_product__order_id' => [
                            [
                                'product_id' => 10,
                                'price' => 22.00,
                            ],
                            [
                                'product_id' => 6,
                                'price' => 32.00,
                            ],
                        ],
                    ],
                    [
                        'id' => '3',
                        'customer' => 'Jane Dane',
                        'address' => '96 Victoria Street',
                        'created_at' => '2022-09-16 07:38:18',
                        'order_product__order_id' => [
                            [
                                'product_id' => 2,
                                'price' => 5.69,
                            ],
                            [
                                'product_id' => null,
                                'price' => 7.00,
                            ],
                            [
                                'product_id' => 6,
                                'price' => 32.00,
                            ],
                        ],
                    ],
                    [
                        'id' => '4',
                        'customer' => 'Kelly J Lozano',
                        'address' => '13 West Street',
                        'created_at' => '2022-09-06 09:20:15',
                        'order_product__order_id' => [
                            [
                                'product_id' => 9,
                                'price' => 21.00,
                            ],
                            [
                                'product_id' => 7,
                                'price' => 15.00,
                            ],
                            [
                                'product_id' => 1,
                                'price' => 6.99,
                            ],
                            [
                                'product_id' => 2,
                                'price' => 5.69,
                            ],
                        ],
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
                    price
                    order {
                        id
                        customer
                        address
                        created_at
                    }
                    product {
                        id
                        name
                        description
                        price
                    }
                }
            }
            GQL
        );

        $this->assertSame([
            'data' => [
                'order_product' => [
                    [
                        'price' => 6.99,
                        'order' => [
                            'id' => '1',
                            'customer' => 'John Doe',
                            'address' => '9998 High Street',
                            'created_at' => '2022-09-01 16:04:21',
                        ],
                        'product' => [
                            'id' => 1,
                            'name' => 'Product #1',
                            'description' => 'Lorem ipsum',
                            'price' => 6.99,
                        ],
                    ],
                    [
                        'price' => 12.49,
                        'order' => [
                            'id' => '1',
                            'customer' => 'John Doe',
                            'address' => '9998 High Street',
                            'created_at' => '2022-09-01 16:04:21',
                        ],
                        'product' => [
                            'id' => 4,
                            'name' => 'Product #4',
                            'description' => 'Dolor sit',
                            'price' => 12.49,
                        ],
                    ],
                    [
                        'price' => 15.00,
                        'order' => [
                            'id' => '1',
                            'customer' => 'John Doe',
                            'address' => '9998 High Street',
                            'created_at' => '2022-09-01 16:04:21',
                        ],
                        'product' => [
                            'id' => 7,
                            'name' => 'Product #7',
                            'description' => 'Loras dore',
                            'price' => 15.0,
                        ],
                    ],
                    [
                        'price' => 22.00,
                        'order' => [
                            'id' => '2',
                            'customer' => 'Moe Lester',
                            'address' => '88 New Street',
                            'created_at' => '2022-09-11 17:16:43',
                        ],
                        'product' => [
                            'id' => 10,
                            'name' => 'Product #10',
                            'description' => 'Mora de sito',
                            'price' => 22.00,
                        ],
                    ],
                    [
                        'price' => 32.00,
                        'order' => [
                            'id' => '2',
                            'customer' => 'Moe Lester',
                            'address' => '88 New Street',
                            'created_at' => '2022-09-11 17:16:43',
                        ],
                        'product' => [
                            'id' => 6,
                            'name' => 'Product #6',
                            'description' => null,
                            'price' => 32.0,
                        ],
                    ],
                    [
                        'price' => 5.69,
                        'order' => [
                            'id' => '3',
                            'customer' => 'Jane Dane',
                            'address' => '96 Victoria Street',
                            'created_at' => '2022-09-16 07:38:18',
                        ],
                        'product' => [
                            'id' => 2,
                            'name' => 'Product #2',
                            'description' => 'Dolor sit',
                            'price' => 5.69,
                        ],
                    ],
                    [
                        'price' => 7.00,
                        'order' => [
                            'id' => '3',
                            'customer' => 'Jane Dane',
                            'address' => '96 Victoria Street',
                            'created_at' => '2022-09-16 07:38:18',
                        ],
                        'product' => null,
                    ],
                    [
                        'price' => 32.00,
                        'order' => [
                            'id' => '3',
                            'customer' => 'Jane Dane',
                            'address' => '96 Victoria Street',
                            'created_at' => '2022-09-16 07:38:18',
                        ],
                        'product' => [
                            'id' => 6,
                            'name' => 'Product #6',
                            'description' => null,
                            'price' => 32.0,
                        ],
                    ],
                    [
                        'price' => 21.00,
                        'order' => [
                            'id' => '4',
                            'customer' => 'Kelly J Lozano',
                            'address' => '13 West Street',
                            'created_at' => '2022-09-06 09:20:15',
                        ],
                        'product' => [
                            'id' => 9,
                            'name' => 'Product #9',
                            'description' => null,
                            'price' => 21.0,
                        ],
                    ],
                    [
                        'price' => 15.00,
                        'order' => [
                            'id' => '4',
                            'customer' => 'Kelly J Lozano',
                            'address' => '13 West Street',
                            'created_at' => '2022-09-06 09:20:15',
                        ],
                        'product' => [
                            'id' => 7,
                            'name' => 'Product #7',
                            'description' => 'Loras dore',
                            'price' => 15.0,
                        ],
                    ],
                    [
                        'price' => 6.99,
                        'order' => [
                            'id' => '4',
                            'customer' => 'Kelly J Lozano',
                            'address' => '13 West Street',
                            'created_at' => '2022-09-06 09:20:15',
                        ],
                        'product' => [
                            'id' => 1,
                            'name' => 'Product #1',
                            'description' => 'Lorem ipsum',
                            'price' => 6.99,
                        ],
                    ],
                    [
                        'price' => 5.69,
                        'order' => [
                            'id' => '4',
                            'customer' => 'Kelly J Lozano',
                            'address' => '13 West Street',
                            'created_at' => '2022-09-06 09:20:15',
                        ],
                        'product' => [
                            'id' => 2,
                            'name' => 'Product #2',
                            'description' => 'Dolor sit',
                            'price' => 5.69,
                        ],
                    ],
                ],
            ],
        ], $result);
    }

    public function testCanLimitHasManyResults(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                categories {
                    id
                    name
                    products__category_id (
                        pagination: {
                            limit: 2
                        }
                    ) {
                        id
                        name
                        description
                        price
                    }
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
                        'products__category_id' => [
                            [
                                'id' => 1,
                                'name' => 'Product #1',
                                'description' => 'Lorem ipsum',
                                'price' => 6.99,
                            ],
                            [
                                'id' => 2,
                                'name' => 'Product #2',
                                'description' => 'Dolor sit',
                                'price' => 5.69,
                            ],
                        ],
                    ],
                    [
                        'id' => '2',
                        'name' => 'Category #2',
                        'products__category_id' => [
                            [
                                'id' => 4,
                                'name' => 'Product #4',
                                'description' => 'Dolor sit',
                                'price' => 12.49,
                            ],
                            [
                                'id' => 8,
                                'name' => 'Product #8',
                                'description' => null,
                                'price' => 19.99,
                            ],
                        ],
                    ],
                    [
                        'id' => '3',
                        'name' => 'Category #3',
                        'products__category_id' => [
                            [
                                'id' => 5,
                                'name' => 'Product #5',
                                'description' => 'Aster mor',
                                'price' => 16.2,
                            ],
                            [
                                'id' => 10,
                                'name' => 'Product #10',
                                'description' => 'Mora de sito',
                                'price' => 22.0,
                            ],
                        ],
                    ],
                ],
            ],
        ], $result);
    }

}
