<?php

namespace Aiiro\GraphQL\Commands;

use Aiiro\GraphQL\Connections\DatabaseContract;
use Aiiro\GraphQL\Connections\MySql;
use Aiiro\Graphql\Exceptions\UnknownConnectionException;
use Illuminate\Config\Repository;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Database\Connection;
use Illuminate\Database\MySqlConnection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;


class GenerateType extends GeneratorCommand
{

    /**
     * @var string
     */
    protected $signature = 'generate:type {name?} {--all}';

    /**
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * @var DatabaseContract
     */
    protected $database;

    /**
     * GenerateFactory constructor.
     *
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param \Illuminate\Config\Repository $config
     */
    public function __construct(Filesystem $files, Repository $config)
    {
        parent::__construct($files);
        $this->config = $config;
    }

    /**
     * @return bool|void|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws UnknownConnectionException
     */
    public function handle()
    {
        /** @var Connection $conn */
        $conn = \DB::connection();
        /** @var \PDO $pdo */
        $pdo = \DB::connection()->getPdo();

        if ($conn instanceof MySqlConnection) {
            $this->database = new MySql($pdo);
        } else {
            throw new UnknownConnectionException('Unknown connection is set.');
        }

        if ($this->hasOption('all') && $this->option('all')) {
            $tables = $this->database->fetchTables();
            foreach ($tables as $tableRecord) {
                $this->runFactoryBuilder($tableRecord[0]);
            }
        } else {
            $table = $this->getNameInput();
            $this->runFactoryBuilder($table);
        }
    }

    /**
     * @param $table
     * @return bool
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function runFactoryBuilder($table)
    {
        $model = Str::singular(Str::ucfirst(Str::camel($table)));

        $path = $this->getPath($model);

        if ($this->alreadyExists($path)) {
            $this->error("Type: $model already exists!");
            return false;
        }

        $columns = $this->database->fetchColumns($table);

        $factoryContent = $this->buildFactory($table, $model, $columns);

        $this->createFile($path, $factoryContent);

        $this->info($model . '.graphql created successfully.');
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getPath($name)
    {

        $path = $this->config->get('graphql-factory.path');

        return base_path() . '/' . $path . '/' . $name . '.graphql';
    }

    /**
     * @param string $path
     * @return bool
     */
    protected function alreadyExists($path)
    {
        return !!$this->files->exists($path);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/type.stub';
    }

    protected function buildFactory($table, $model, $columns)
    {
        $stub = $this->files->get($this->getStub());

        $stub = $this->replaceType($stub, $model);

        $stub = $this->replaceColumns($stub, $columns, $table);

        return $stub;
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param string $stub
     * @param string $name
     * @return string
     */
    protected function replaceType($stub, $name)
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);

        return str_replace('DummyType', $class, $stub);
    }


    /**
     * @param $path
     * @param $content
     */
    protected function createFile($path, $content)
    {
        $this->files->put($path, $content);
    }

    protected function replaceColumns($stub, $columns, $table)
    {
        $content = '';

        $ignoredColumns = $this->config->get('graphql-factory.ignored_columns');

        $columnTypes = $this->database->readSchemaColumns($table);

        foreach ($columns as $column) {
            if (in_array($column, $ignoredColumns)) {
                continue;
            }

            // indent spaces
            $content .= "    ";
            $content .= "{$column}";
            if ($columnTypes[$column] === 'int' || $columnTypes[$column] === 'tinyint') {
                $content .= ': Int';
            } elseif ($columnTypes[$column] === 'varchar' || $columnTypes[$column] === 'text') {
                $content .= ': String';
            } else {
                $content .= ': String';
            }
            $content .= "\n";
        }

        if (Str::length($content) > 0) {
            // Remove newline character of the last line.
            $content = rtrim($content, "\n");
        }

        return str_replace('DummyColumns', $content, $stub);
    }

}
