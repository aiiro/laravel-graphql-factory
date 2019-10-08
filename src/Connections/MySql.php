<?php

namespace Aiiro\GraphQL\Connections;

class MySql implements DatabaseContract
{

    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * MySql constructor.
     *
     * @param $pdo
     */
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function fetchTables()
    {
        $stmt = $this->pdo->prepare("SHOW TABLES");
        $stmt->execute();

        return $stmt->fetchAll();
    }


    public function fetchColumns($table)
    {
        $columns = [];

        $stmt = $this->pdo->prepare("DESCRIBE $table");
        $stmt->execute();

        foreach ($stmt->fetchAll() as $column) {
            $columns[] = $column['Field'];
        }

        return $columns;
    }

    public function readSchemaColumns($table)
    {
        $columns = [];

        $stmt = $this->pdo->prepare(
            <<<EOT
SELECT
    COLUMN_NAME, DATA_TYPE
FROM
    INFORMATION_SCHEMA.COLUMNS
WHERE
    TABLE_NAME = '{$table}'
;
EOT

        );

        $stmt->execute();

        foreach ($stmt->fetchAll() as $column) {
            $columns[$column['COLUMN_NAME']] = $column['DATA_TYPE'];
        }

        return $columns;

    }
}
