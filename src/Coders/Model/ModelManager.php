<?php

/**
 * Created by Cristian.
 * Date: 02/10/16 08:24 PM.
 */

namespace Reliese\Coders\Model;

use ArrayIterator;
use IteratorAggregate;
use Illuminate\Support\Arr;

class ModelManager implements IteratorAggregate
{
    /**
     * @var \Reliese\Coders\Model\Factory
     */
    protected $factory;

    /**
     * @var \Reliese\Coders\Model\Model[]
     */
    protected $models = [];

    /**
     * ModelManager constructor.
     *
     * @param \Reliese\Coders\Model\Factory $factory
     */
    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param string $schema
     * @param string $table
     * @param \Reliese\Coders\Model\Mutator[] $mutators
     * @param bool $withRelations
     *
     * @return \Reliese\Coders\Model\Model
     */
    public function make($name, $schema, $table, $mutators = [], $withRelations = true, $namespace = null)
    {
        $mapper = $this->factory->makeSchema($schema);

        $blueprint = $mapper->table($table);

        if (Arr::has($this->models, $blueprint->qualifiedTable())) {
            return $this->models[$schema][$table];
        }

        $model = new Model($name, $blueprint, $this->factory, $mutators, $withRelations, $namespace);

        if ($withRelations) {
            $this->models[$schema][$table] = $model;
        }

        return $model;
    }

    /**
     * Get Models iterator.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->models);
    }
}
