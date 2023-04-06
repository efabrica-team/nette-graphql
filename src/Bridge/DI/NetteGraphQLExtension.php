<?php

namespace Efabrica\GraphQL\Nette\Bridge\DI;

use Efabrica\GraphQL\Drivers\WebonyxDriver;
use Efabrica\GraphQL\GraphQL;
use Efabrica\GraphQL\Helpers\AdditionalResponseData;
use Efabrica\GraphQL\Helpers\DatabaseColumnTypeTransformer;
use Efabrica\GraphQL\Nette\Factories\NetteDatabaseResolverFactory;
use Efabrica\GraphQL\Nette\Schema\Loaders\NetteDatabaseSchemaLoader;
use Nette\DI\CompilerExtension;

class NetteGraphQLExtension extends CompilerExtension
{
    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('graphql'))
            ->setFactory(GraphQL::class);

        $builder->addDefinition($this->prefix('driver'))
            ->setFactory(WebonyxDriver::class)
            ->addSetup('setDebug', [$builder->parameters['debugMode']]);

        $builder->addDefinition($this->prefix('schemaLoader'))
            ->setFactory(NetteDatabaseSchemaLoader::class);

        $builder->addDefinition($this->prefix('resolverFactory'))
            ->setFactory(NetteDatabaseResolverFactory::class);

        $builder->addDefinition($this->prefix('databaseColumnTypeTransformer'))
            ->setFactory(DatabaseColumnTypeTransformer::class);

        $builder->addDefinition($this->prefix('additionalResponseData'))
            ->setFactory(AdditionalResponseData::class);
    }
}
