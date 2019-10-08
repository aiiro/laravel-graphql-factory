<?php

namespace Aiiro\GraphQL\Connections;

interface DatabaseContract
{
    public function fetchColumns($table);

    public function readSchemaColumns($table);
}
