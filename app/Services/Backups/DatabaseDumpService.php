<?php

namespace App\Services\Backups;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use Throwable;

class DatabaseDumpService
{
    public function createGzippedDump(): string
    {
        $directory = config('backup.temp_directory');
        File::ensureDirectoryExists($directory);

        $sqlPath = $directory.DIRECTORY_SEPARATOR.uniqid('dump_', true).'.sql';
        $gzipPath = $sqlPath.'.gz';

        try {
            $this->dumpToFile($sqlPath);
            $this->gzipFile($sqlPath, $gzipPath);
        } catch (Throwable $exception) {
            $this->cleanup([$sqlPath, $gzipPath]);

            throw $exception;
        } finally {
            if (is_file($sqlPath)) {
                @unlink($sqlPath);
            }
        }

        return $gzipPath;
    }

    protected function dumpToFile(string $sqlPath): void
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        match ($driver) {
            'mysql', 'mariadb' => $this->dumpMysql($sqlPath, $connection),
            'sqlite' => $this->dumpSqlite($sqlPath, $connection),
            default => throw new RuntimeException("Database driver [{$driver}] is not supported for backups."),
        };
    }

    protected function dumpMysql(string $sqlPath, string $connection): void
    {
        $config = config("database.connections.{$connection}");
        $database = $config['database'] ?? null;

        if (blank($database)) {
            throw new RuntimeException('MySQL database name is not configured.');
        }

        $command = [
            'mysqldump',
            '--host='.($config['host'] ?? '127.0.0.1'),
            '--port='.($config['port'] ?? 3306),
            '--user='.($config['username'] ?? 'root'),
            '--single-transaction',
            '--quick',
            '--routines',
            '--triggers',
            '--result-file='.$sqlPath,
            $database,
        ];

        $result = Process::env([
            'MYSQL_PWD' => (string) ($config['password'] ?? ''),
        ])->timeout(600)->run($command);

        if (! $result->successful()) {
            throw new RuntimeException(
                'Failed to create MySQL dump: '.trim($result->errorOutput() ?: $result->output())
            );
        }

        if (! is_file($sqlPath) || filesize($sqlPath) === 0) {
            throw new RuntimeException('MySQL dump file was not created or is empty.');
        }
    }

    protected function dumpSqlite(string $sqlPath, string $connection): void
    {
        $database = config("database.connections.{$connection}.database");

        if ($database !== ':memory:' && ! is_file((string) $database)) {
            throw new RuntimeException("SQLite database file [{$database}] was not found.");
        }

        $pdo = DB::connection($connection)->getPdo();
        $statement = $pdo->query('SELECT sql FROM sqlite_master WHERE sql IS NOT NULL ORDER BY type DESC, name');
        $lines = ["PRAGMA foreign_keys=OFF;\nBEGIN TRANSACTION;\n"];

        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $lines[] = $row['sql'].";\n";
        }

        $tables = DB::connection($connection)->select(
            "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name"
        );

        foreach ($tables as $table) {
            $tableName = $table->name;
            $rows = DB::connection($connection)->table($tableName)->get();

            foreach ($rows as $row) {
                $values = collect((array) $row)
                    ->map(function (mixed $value) use ($pdo): string {
                        if ($value === null) {
                            return 'NULL';
                        }

                        return $pdo->quote((string) $value);
                    })
                    ->implode(', ');

                $columns = collect((array) $row)
                    ->keys()
                    ->map(fn (string $column): string => '"'.$column.'"')
                    ->implode(', ');

                $lines[] = "INSERT INTO \"{$tableName}\" ({$columns}) VALUES ({$values});\n";
            }
        }

        $lines[] = "COMMIT;\n";

        if (file_put_contents($sqlPath, implode('', $lines)) === false) {
            throw new RuntimeException('Failed to write SQLite dump file.');
        }
    }

    protected function gzipFile(string $sourcePath, string $destinationPath): void
    {
        $source = fopen($sourcePath, 'rb');
        $destination = gzopen($destinationPath, 'wb9');

        if ($source === false || $destination === false) {
            throw new RuntimeException('Failed to open streams for gzip compression.');
        }

        while (! feof($source)) {
            $chunk = fread($source, 1024 * 512);

            if ($chunk === false) {
                fclose($source);
                gzclose($destination);

                throw new RuntimeException('Failed while reading dump file for compression.');
            }

            gzwrite($destination, $chunk);
        }

        fclose($source);
        gzclose($destination);

        if (! is_file($destinationPath) || filesize($destinationPath) === 0) {
            throw new RuntimeException('Compressed backup file was not created or is empty.');
        }
    }

    /**
     * @param  list<string>  $paths
     */
    protected function cleanup(array $paths): void
    {
        foreach ($paths as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }
}
