<?php

namespace Reliese\Coders\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Reliese\Coders\Model\Factory;
use Illuminate\Contracts\Config\Repository;

class FetchTablesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code:tables
                            {--t|table= : The table to create a model for}
                            {--s|schema= : The name of the MySQL database}
                            {--c|connection= : The name of the connection}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get available schema tables';

    /**
     * @var \Reliese\Coders\Model\Factory
     */
    protected $models;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * Create a new command instance.
     *
     * @param \Reliese\Coders\Model\Factory $models
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(Factory $models, Repository $config)
    {
        parent::__construct();

        $this->models = $models;
        $this->config = $config;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $connection = $this->getConnection();
        $schema = $this->getSchema($connection);

        $tables = $this->models->on($connection, $schema)->fetchTables($schema);

        $chosenTable = $this->checkParameterOrChoice('table', 'Select a table to create a model for', $tables);

        $tableExploded = explode('_', $chosenTable);

        if(count($tableExploded) > 1) {
            $sectionName = Str::camel($tableExploded[0]);
            $_sectionName = Str::lower($tableExploded[0]);
        } else {
            $sectionName = 'AppSection';
            $_sectionName = 'appsection';
        }

        $containerName = Str::camel($chosenTable);
        $_containerName = Str::lower($chosenTable);

        $this->call('apihawk:generate:container:api', [
            '--section' => $sectionName,
            '--container' => $containerName,
            '--migration' => true,
            '--table' => $chosenTable,
            '--file' => 'composer',
        ]);
    }

    /**
     * @return string
     */
    protected function getConnection()
    {
        return $this->option('connection') ?: $this->config->get('database.default');
    }

    /**
     * @param $connection
     *
     * @return string
     */
    protected function getSchema($connection)
    {
        return $this->option('schema') ?: $this->config->get("database.connections.$connection.database");
    }

    protected function checkParameterOrChoice($param, $question, $choices, mixed $default = null): bool|array|string|null
    {
        // Check if we already have a param set
        $value = $this->option($param);
        if ($value === null) {
            // There was no value provided via CLI, so ask the user…
            $value = $this->choice($question, $choices, $default);
        }

        return $value;
    }
}
