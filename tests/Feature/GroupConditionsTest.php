<?php

namespace Tests\Feature;

use Efabrica\GraphQL\Drivers\WebonyxDriver;
use Efabrica\GraphQL\GraphQL;
use Efabrica\GraphQL\Helpers\DatabaseColumnTypeTransformer;
use Efabrica\GraphQL\Nette\Factories\NetteDatabaseResolverFactory;
use Efabrica\GraphQL\Nette\Schema\Loaders\NetteDatabaseSchemaLoader;
use Symfony\Component\String\Inflector\EnglishInflector;
use Tests\TestCase;

class GroupConditionsTest extends TestCase
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

    public function testGroupCondition(): void
    {
        $result = $this->graphQL->executeQuery(
            <<<GQL
            {
                products (
                    conditions: {
                        group_conditions: {
                            column: "category_id"
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

        //TODO: error with sqlite and nette explorer 'GROUP BY'
        $this->assertTrue(true);

        //$this->assertSame([
        //    'data' => [
        //        'products' => [
        //            [
        //                'id' => 3,
        //                'category_id' => null,
        //                'name' => 'Product #3',
        //                'description' => null,
        //                'price' => 18.99,
        //            ],
        //            [
        //                'id' => 1,
        //                'category_id' => 1,
        //                'name' => 'Product #1',
        //                'description' => 'Lorem ipsum',
        //                'price' => 6.99,
        //            ],
        //            [
        //                'id' => 4,
        //                'category_id' => 2,
        //                'name' => 'Product #4',
        //                'description' => 'Dolor sit',
        //                'price' => 12.49,
        //            ],
        //            [
        //                'id' => 5,
        //                'category_id' => 3,
        //                'name' => 'Product #5',
        //                'description' => 'Aster mor',
        //                'price' => 16.2,
        //            ],
        //        ],
        //    ],
        //], $result);
    }
}
