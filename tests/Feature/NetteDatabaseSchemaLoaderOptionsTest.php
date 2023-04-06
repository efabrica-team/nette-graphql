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

class NetteDatabaseSchemaLoaderOptionsTest extends TestCase
{
    private GraphQL $graphQL;

    private NetteDatabaseSchemaLoader $schemaLoader;

    protected function setUp(): void
    {
        $explorer = $this->createExplorer(__DIR__ . '/../Mock/data.sql');
        $additionalResponseData = new AdditionalResponseData();
        $this->schemaLoader = new NetteDatabaseSchemaLoader(
            $explorer,
            new NetteDatabaseResolverFactory($explorer, $additionalResponseData),
            new DatabaseColumnTypeTransformer(),
            new EnglishInflector()
        );
        $driver = new WebonyxDriver($this->schemaLoader, $additionalResponseData);
        $this->graphQL = new GraphQL($driver);
    }

    public function testSchemaHasAllTables(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
              __schema {
                queryType {
                  fields {
                    name
                  }
                }
              }
            }
            GQL
        );

        $this->assertSame([
            'data' => [
                '__schema' => [
                    'queryType' => [
                        'fields' => [
                            [
                                'name' => 'categories',
                            ],
                            [
                                'name' => 'categories_count',
                            ],
                            [
                                'name' => 'order_product',
                            ],
                            [
                                'name' => 'order_product_count',
                            ],
                            [
                                'name' => 'orders',
                            ],
                            [
                                'name' => 'orders_count',
                            ],
                            [
                                'name' => 'products',
                            ],
                            [
                                'name' => 'products_count',
                            ],
                        ],
                    ],
                ],
            ],
        ], $result);
    }

    public function testCanAddBlacklistTables(): void
    {
        $this->schemaLoader->addExceptTable('order_product');
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
              __schema {
                queryType {
                  fields {
                    name
                  }
                }
              }
            }
            GQL
        );

        $this->assertSame([
            'data' => [
                '__schema' => [
                    'queryType' => [
                        'fields' => [
                            [
                                'name' => 'categories',
                            ],
                            [
                                'name' => 'categories_count',
                            ],
                            [
                                'name' => 'orders',
                            ],
                            [
                                'name' => 'orders_count',
                            ],
                            [
                                'name' => 'products',
                            ],
                            [
                                'name' => 'products_count',
                            ],
                        ],
                    ],
                ],
            ],
        ], $result);
    }

    public function testCanSetBlacklistTables(): void
    {
        $this->schemaLoader->setExceptTables(['order_product', 'categories']);
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
              __schema {
                queryType {
                  fields {
                    name
                  }
                }
              }
            }
            GQL
        );

        $this->assertSame([
            'data' => [
                '__schema' => [
                    'queryType' => [
                        'fields' => [
                            [
                                'name' => 'orders',
                            ],
                            [
                                'name' => 'orders_count',
                            ],
                            [
                                'name' => 'products',
                            ],
                            [
                                'name' => 'products_count',
                            ],
                        ],
                    ],
                ],
            ],
        ], $result);
    }

    public function testCanAddWhitelistTables(): void
    {
        $this->schemaLoader->addOnlyTable('products');
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
              __schema {
                queryType {
                  fields {
                    name
                  }
                }
              }
            }
            GQL
        );

        $this->assertSame([
            'data' => [
                '__schema' => [
                    'queryType' => [
                        'fields' => [
                            [
                                'name' => 'products',
                            ],
                            [
                                'name' => 'products_count',
                            ],
                        ],
                    ],
                ],
            ],
        ], $result);
    }

    public function testCanSetWhitelistTables(): void
    {
        $this->schemaLoader->setOnlyTables(['categories', 'products']);
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
              __schema {
                queryType {
                  fields {
                    name
                  }
                }
              }
            }
            GQL
        );

        $this->assertSame([
            'data' => [
                '__schema' => [
                    'queryType' => [
                        'fields' => [
                            [
                                'name' => 'categories',
                            ],
                            [
                                'name' => 'categories_count',
                            ],
                            [
                                'name' => 'products',
                            ],
                            [
                                'name' => 'products_count',
                            ],
                        ],
                    ],
                ],
            ],
        ], $result);
    }

    public function testIgnoresNonexistentTablesOnWhitelist(): void
    {
        $this->schemaLoader->setOnlyTables(['categories', 'products', 'clients']);
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
              __schema {
                queryType {
                  fields {
                    name
                  }
                }
              }
            }
            GQL
        );

        $this->assertSame([
            'data' => [
                '__schema' => [
                    'queryType' => [
                        'fields' => [
                            [
                                'name' => 'categories',
                            ],
                            [
                                'name' => 'categories_count',
                            ],
                            [
                                'name' => 'products',
                            ],
                            [
                                'name' => 'products_count',
                            ],
                        ],
                    ],
                ],
            ],
        ], $result);
    }

    public function testTablesHaveAllFields(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
              __schema {
                types {
                  name
                  fields {
                    name
                  }
                }
              }
            }
            GQL
        );

        $this->assertContains([
            'name' => 'product',
            'fields' => [
                [
                    'name' => 'id',
                ],
                [
                    'name' => 'category_id',
                ],
                [
                    'name' => 'name',
                ],
                [
                    'name' => 'description',
                ],
                [
                    'name' => 'price',
                ],
                [
                    'name' => 'category',
                ],
                [
                    'name' => 'order_product__product_id',
                ],
                [
                    'name' => 'order_product__product_id_count',
                ],
            ],
        ], $result['data']['__schema']['types']);
    }

    public function testCanAddBlacklistTableColumns(): void
    {
        $this->schemaLoader->addExceptTableColumn('products', 'description');
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
              __schema {
                types {
                  name
                  fields {
                    name
                  }
                }
              }
            }
            GQL
        );

        $this->assertContains([
            'name' => 'product',
            'fields' => [
                [
                    'name' => 'id',
                ],
                [
                    'name' => 'category_id',
                ],
                [
                    'name' => 'name',
                ],
                [
                    'name' => 'price',
                ],
                [
                    'name' => 'category',
                ],
                [
                    'name' => 'order_product__product_id',
                ],
                [
                    'name' => 'order_product__product_id_count',
                ],
            ],
        ], $result['data']['__schema']['types']);
    }

    public function testCanSetBlacklistTableColumns(): void
    {
        $this->schemaLoader->setExceptTableColumns('products', ['description', 'price']);
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
              __schema {
                types {
                  name
                  fields {
                    name
                  }
                }
              }
            }
            GQL
        );

        $this->assertContains([
            'name' => 'product',
            'fields' => [
                [
                    'name' => 'id',
                ],
                [
                    'name' => 'category_id',
                ],
                [
                    'name' => 'name',
                ],
                [
                    'name' => 'category',
                ],
                [
                    'name' => 'order_product__product_id',
                ],
                [
                    'name' => 'order_product__product_id_count',
                ],
            ],
        ], $result['data']['__schema']['types']);
    }

    public function testCanAddWhitelistTableColumns(): void
    {
        $this->schemaLoader->addOnlyTableColumn('products', 'id');
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
              __schema {
                types {
                  name
                  fields {
                    name
                  }
                }
              }
            }
            GQL
        );

        $this->assertContains([
            'name' => 'product',
            'fields' => [
                [
                    'name' => 'id',
                ],
                [
                    'name' => 'order_product__product_id',
                ],
                [
                    'name' => 'order_product__product_id_count',
                ],
            ],
        ], $result['data']['__schema']['types']);
    }

    public function testCanSetWhitelistTableColumns(): void
    {
        $this->schemaLoader->setOnlyTableColumns('products', ['id', 'name']);
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
              __schema {
                types {
                  name
                  fields {
                    name
                  }
                }
              }
            }
            GQL
        );

        $this->assertContains([
            'name' => 'product',
            'fields' => [
                [
                    'name' => 'id',
                ],
                [
                    'name' => 'name',
                ],
                [
                    'name' => 'order_product__product_id',
                ],
                [
                    'name' => 'order_product__product_id_count',
                ],
            ],
        ], $result['data']['__schema']['types']);
    }

    public function testCanEnableShortHasManyFieldNames(): void
    {
        $this->schemaLoader->setForcedHasManyLongName(false);
        $this->assertFalse($this->schemaLoader->isForcedHasManyLongName());

        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
              __schema {
                types {
                  name
                  fields {
                    name
                  }
                }
              }
            }
            GQL
        );

        $this->assertContains([
            'name' => 'product',
            'fields' => [
                [
                    'name' => 'id',
                ],
                [
                    'name' => 'category_id',
                ],
                [
                    'name' => 'name',
                ],
                [
                    'name' => 'description',
                ],
                [
                    'name' => 'price',
                ],
                [
                    'name' => 'category',
                ],
                [
                    'name' => 'order_product',
                ],
                [
                    'name' => 'order_product_count',
                ],
            ],
        ], $result['data']['__schema']['types']);
    }
}
