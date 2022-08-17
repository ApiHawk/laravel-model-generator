<?php

namespace Reliese\Coders\Console;

use Illuminate\Console\Command;
use Reliese\Coders\Model\Factory;
use Illuminate\Contracts\Config\Repository;

class CodeModelsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code:models
                            {--name= : The name of the created model}
                            {--s|schema= : The name of the MySQL database}
                            {--c|connection= : The name of the connection}
                            {--t|table= : The name of the table}
                            {--p|path= : The path of the container}
                            {--namespace= : The namespace of the container}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse connection schema into models';

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
        $name = $this->getModelName();
        $connection = $this->getConnection();
        $schema = $this->getSchema($connection);
        $table = $this->getTable();
        $namespace = $this->getNamespace();
        $path = $this->getPath();

        // Check whether we just need to generate one table
        if ($table) {
            $this->models->on($connection, $schema, $table, $namespace, $path)->create($name, $schema, $table, $namespace, $path);
            $this->info("Check out your models for $table");
        }

        // Otherwise map the whole database
        else {
            $this->models->on($connection)->map($schema);
            $this->info("Check out your models for $schema");
        }
    }

    protected function getModelName()
    {
        return $this->option('name') ?: $this->config->get('defaultName');
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

    protected function getNamespace() {
        return $this->option('namespace') ?: $this->config->get('namespace');
    }

    protected function getPath() {
        return $this->option('path') ?: $this->config->get('path');
    }

    /**
     * @return string
     */
    protected function getTable()
    {
        return $this->option('table');
    }
}
