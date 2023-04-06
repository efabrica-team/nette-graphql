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

class WhereConditionsTest extends TestCase
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

    public function testWhereInCondition(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                products (
                    conditions: {
                        where: {
                            column: "id"
                            comparator: IN
                            value: ["1", "3", "5", "7", "9"]
                        }
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
                        'id' => 1,
                        'category_id' => 1,
                        'name' => 'Product #1',
                        'description' => 'Lorem ipsum',
                        'price' => 6.99,
                    ],
                    [
                        'id' => 3,
                        'category_id' => null,
                        'name' => 'Product #3',
                        'description' => null,
                        'price' => 18.99,
                    ],
                    [
                        'id' => 5,
                        'category_id' => 3,
                        'name' => 'Product #5',
                        'description' => 'Aster mor',
                        'price' => 16.2,
                    ],
                    [
                        'id' => 7,
                        'category_id' => null,
                        'name' => 'Product #7',
                        'description' => 'Loras dore',
                        'price' => 15.0,
                    ],
                    [
                        'id' => 9,
                        'category_id' => 1,
                        'name' => 'Product #9',
                        'description' => null,
                        'price' => 21.0,
                    ],
                ],
            ],
        ], $result);
    }

    public function testWhereNotInCondition(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                products (
                    conditions: {
                        where: {
                            column: "id"
                            comparator: NOT_IN
                            value: ["1", "3", "5", "7", "9"]
                        }
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
                        'id' => 2,
                        'category_id' => 1,
                        'name' => 'Product #2',
                        'description' => 'Dolor sit',
                        'price' => 5.69,
                    ],
                    [
                        'id' => 4,
                        'category_id' => 2,
                        'name' => 'Product #4',
                        'description' => 'Dolor sit',
                        'price' => 12.49,
                    ],
                    [
                        'id' => 6,
                        'category_id' => 1,
                        'name' => 'Product #6',
                        'description' => null,
                        'price' => 32.0,
                    ],
                    [
                        'id' => 8,
                        'category_id' => 2,
                        'name' => 'Product #8',
                        'description' => null,
                        'price' => 19.99,
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

    public function testWhereEqualCondition(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                products (
                    conditions: {
                        where: {
                            column: "id"
                            comparator: EQUAL
                            value: "2"
                        }
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
                        'id' => 2,
                        'category_id' => 1,
                        'name' => 'Product #2',
                        'description' => 'Dolor sit',
                        'price' => 5.69,
                    ],
                ],
            ],
        ], $result);
    }

    public function testWhereNotEqualCondition(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                products (
                    conditions: {
                        where: {
                            column: "id"
                            comparator: NOT_EQUAL
                            value: "2"
                        }
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
                        'id' => 1,
                        'category_id' => 1,
                        'name' => 'Product #1',
                        'description' => 'Lorem ipsum',
                        'price' => 6.99,
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

    public function testWhereLessThanCondition(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                products (
                    conditions: {
                        where: {
                            column: "price"
                            comparator: LESS_THAN
                            value: "15"
                        }
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
                        'id' => 4,
                        'category_id' => 2,
                        'name' => 'Product #4',
                        'description' => 'Dolor sit',
                        'price' => 12.49,
                    ],
                ],
            ],
        ], $result);
    }

    public function testWhereLessThanEqualCondition(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                products (
                    conditions: {
                        where: {
                            column: "price"
                            comparator: LESS_THAN_EQUAL
                            value: "15"
                        }
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
                        'id' => 4,
                        'category_id' => 2,
                        'name' => 'Product #4',
                        'description' => 'Dolor sit',
                        'price' => 12.49,
                    ],
                    [
                        'id' => 7,
                        'category_id' => null,
                        'name' => 'Product #7',
                        'description' => 'Loras dore',
                        'price' => 15.0,
                    ],
                ],
            ],
        ], $result);
    }

    public function testWhereMoreThanCondition(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                products (
                    conditions: {
                        where: {
                            column: "price"
                            comparator: MORE_THAN
                            value: "15"
                        }
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

    public function testWhereMoreThanEqualCondition(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                products (
                    conditions: {
                        where: {
                            column: "price"
                            comparator: MORE_THAN_EQUAL
                            value: "15"
                        }
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

    public function testWhereLikeCondition(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                products (
                    conditions: {
                        where: {
                            column: "description"
                            comparator: LIKE
                            value: "%lo%"
                        }
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
                        'id' => 4,
                        'category_id' => 2,
                        'name' => 'Product #4',
                        'description' => 'Dolor sit',
                        'price' => 12.49,
                    ],
                    [
                        'id' => 7,
                        'category_id' => null,
                        'name' => 'Product #7',
                        'description' => 'Loras dore',
                        'price' => 15.0,
                    ],
                ],
            ],
        ], $result);
    }

    public function testWhereNotLikeCondition(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                products (
                    conditions: {
                        where: {
                            column: "description"
                            comparator: NOT_LIKE
                            value: "%lo%"
                        }
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
                        'id' => 5,
                        'category_id' => 3,
                        'name' => 'Product #5',
                        'description' => 'Aster mor',
                        'price' => 16.2,
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

    public function testWhereOnHasManyCondition(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                categories {
                    id
                    name
                    products__category_id (
                        conditions: {
                            where: {
                                column: "description"
                                value: null
                                comparator: NOT_EQUAL
                            }
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

    public function testMultipleAndWhereConditions(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                products (
                    conditions: {
                        where: [
                            {
                                column: "id"
                                comparator: IN
                                value: ["1", "3"]
                            }
                            {
                                column: "id"
                                comparator: IN
                                value: ["3", "5"]
                            }
                        ]
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
                ],
            ],
        ], $result);
    }

    public function testMultipleOrWhereConditions(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                products (
                    conditions: {
                        where_or: [
                            {
                                column: "id"
                                comparator: IN
                                value: ["1", "3"]
                            }
                            {
                                column: "id"
                                comparator: IN
                                value: ["3", "5"]
                            }
                        ]
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
                        'id' => 1,
                        'category_id' => 1,
                        'name' => 'Product #1',
                        'description' => 'Lorem ipsum',
                        'price' => 6.99,
                    ],
                    [
                        'id' => 3,
                        'category_id' => null,
                        'name' => 'Product #3',
                        'description' => null,
                        'price' => 18.99,
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

    public function testNestedWhereConditions(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                products (
                    conditions: {
                        where_or: [
                            {
                                where: [
                                    {
                                        column: "id"
                                        comparator: IN
                                        value: ["1", "3"]
                                    }
                                    {
                                        column: "id"
                                        comparator: IN
                                        value: ["3", "5"]
                                    }
                                ]
                            }
                            {
                                column: "id"
                                comparator: IN
                                value: ["7"]
                            }
                        ]
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
                        'id' => 7,
                        'category_id' => null,
                        'name' => 'Product #7',
                        'description' => 'Loras dore',
                        'price' => 15.0,
                    ],
                ],
            ],
        ], $result);
    }

    public function testNestedWhereOrConditions(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                products (
                    conditions: {
                        where: [
                            {
                                where_or: [
                                    {
                                        column: "id"
                                        comparator: IN
                                        value: ["1", "3"]
                                    }
                                    {
                                        column: "id"
                                        comparator: IN
                                        value: ["3", "5"]
                                    }
                                ]
                            }
                            {
                                column: "id"
                                comparator: IN
                                value: ["5", "7"]
                            }
                        ]
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

    public function testSkipsEmptyNotInCondition(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                products (
                    conditions: {
                        where: [
                            {
                                column: "id"
                                comparator: NOT_IN
                                value: []
                            }
                        ]
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

    public function testResolvesEmptyInCondition(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                products (
                    conditions: {
                        where: [
                            {
                                column: "id"
                                comparator: IN
                                value: []
                            }
                        ]
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
                'products' => [],
            ],
        ], $result);
    }
}
