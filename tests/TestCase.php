<?php

namespace Tests;

use Nette\Caching\Storages\DevNullStorage;
use Nette\Database\Connection;
use Nette\Database\Explorer;
use Nette\Database\Helpers;
use Nette\Database\Structure;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @param string|string[]|null $importFiles
     */
    protected function createExplorer($importFiles = null): Explorer
    {
        $connection = new Connection('sqlite::memory:');
        $structure = new Structure($connection, new DevNullStorage());
        $explorer = new Explorer($connection, $structure);

        foreach ((array)$importFiles as $importFile) {
            Helpers::loadFromFile($connection, $importFile);
        }

        return $explorer;
    }
}
