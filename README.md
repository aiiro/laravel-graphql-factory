<h1 align="center">Generate GraphQL Schema Template</h1>

### Installing
``` shell
composer require --dev aiiro/laravel-graphql-factory
```

### Configuration
Optionally, you can publish the config file by running this command.
``` shell
php artisan vendor:publish --provider="Aiiro\GraphQL\GraphQLFactoryServiceProvider"
```

And then, you can find `config/graphql-factory.php`.
``` php
<?php

return [

    'path' => 'app',

    /**
     * List of the columns that will not appear in the type schema.
     */
    'ignored_columns' => [
        'deleted_at',
    ],
];
```

### Usage
After installing and Configuration, you can generate the schema file by running the following command.

Please pass the table name to `generate:type` command as the argument.

``` shell
php artisan generate:type books
```

app/Book.graphql
```graphql
type Book {
    id: Int
    title: String
    created_at: String
    updated_at: String
}
```

#### To generate type files of all tables in database.

Use `--all` option without table name, to generate type schema of all tables in database.

If a type schema of the table exists, it will be skipped and continue to generate schema of other tables.

```bash
php artisan generate:type --all
```

## License
This project is released under MIT License. See [MIT License](LICENSE)
 for the detail.

