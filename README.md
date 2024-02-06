# Nette GraphQL

This package is implementation of [efabrica/graphql](https://github.com/efabrica-team/graphql) and generates GraphQL
schema from [nette database explorer](https://doc.nette.org/en/database/explorer).

## Installation

Via composer

```sh
composer require efabrica/nette-graphql
```

## Usage

### Configuration

```neon
# config.neon

extensions:
    graphql: Efabrica\GraphQL\Nette\Bridge\DI\NetteGraphQLExtension
    
services:
    - Symfony\Component\String\Inflector\EnglishInflector

    graphql.schemaLoader:
        setup:
            - #...

    graphql.resolverFactory:
        setup:
            - #...
```

#### Loader options

| Option                     | Description                                                                                                                                                                                                                                                                                                                                                                                     |
|----------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| setExceptTables            | Blacklist tables from schema generation                                                                                                                                                                                                                                                                                                                                                         |
| setOnlyTables              | Whitelist tables in schema generation                                                                                                                                                                                                                                                                                                                                                           |
| setExceptTableColumns      | Blacklist table columns from schema generation                                                                                                                                                                                                                                                                                                                                                  |
| setOnlyTableColumns        | Whitelist table columns in schema geneartion                                                                                                                                                                                                                                                                                                                                                    |
| setForcedHasManyLongName   | When disabled, has many relation field names will be generated in format 'users' (table_name) instead of 'users__user_id' (table_name__referencing_column). This looks prettier, but can change your API in future (if there was only one reference to table but later on is added another, field name will change from short to long syntax as there can't be multiple fields with same name). |
| setBelongsToReplacePattern | This pattern will be used when creating relation field names from referencing columns. Matching pattern will be removed from column name. By default text after last '_' will be removed e.g 'user_id' -> 'user'                                                                                                                                                                                |
| addMorphRelationDefinition | You can define morph relaiton that will be added to every resource. You need to define table that has morph relations, name of id and table columns that reference to morph resource and name of the relation that will be used by graphql server                                                                                                                                               |

#### Resolver options

| Option        | Description                                                                                                                                                                                                                                                                     |
|---------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| setFirstParty | This option will enable more complex queries, as column names won't be escaped. This for example enables you to add conditions on relations using 'related.colum' as column name. **THIS WILL ENABLE SQL INJECTION!** Use only when necessary and used only by trusted parties. |

### Query execution

```php
$input = json_decode(file_get_contents('php://input'), true);
$response = $graphQL->executeQuery($input['query']);
```
