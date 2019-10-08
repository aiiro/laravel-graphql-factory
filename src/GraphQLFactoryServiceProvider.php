<?php

namespace Aiiro\GraphQL;

use Illuminate\Support\ServiceProvider;

class GraphQLFactoryServiceProvider extends ServiceProvider
{
    public function boot()
    {

        $configPath = __DIR__ . '/../config/graphql-factory.php';
        if (function_exists('config_path')) {
            $publishPath = config_path('graphql-factory.php');
        } else {
            $publishPath = base_path('config/graphql-factory.php');
        }
        $this->publishes([$configPath => $publishPath], 'config');
    }

    public function register()
    {
        $configPath = __DIR__ . '/../config/graphql-factory.php';
        $this->mergeConfigFrom($configPath, 'graphql-factory');

        $this->commands([
        ]);
    }
}
