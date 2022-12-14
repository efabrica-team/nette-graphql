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

includes:
    - ../../../vendor/efabrica/nette-graphql/config.neon
    
services:
    - Symfony\Component\String\Inflector\EnglishInflector

    netteDatabaseResolverFactory:
        setup:
            - #...

    netteDatabaseSchemaLoader:
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

#### Resolver options
| Option        | Description                                                                                                                                                                                                                                                                     |
|---------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| setFirstParty | This option will enable more complex queries, as column names won't be escaped. This for example enables you to add conditions on relations using 'related.colum' as column name. **THIS WILL ENABLE SQL INJECTION!** Use only when necessary and used only by trusted parties. |

### Query execution
```php
$input = json_decode(file_get_contents('php://input'), true);
$response = $graphQL->executeQuery($input['query']);
```
