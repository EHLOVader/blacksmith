<?php namespace Factories;

/**
 * This class was partially coppied from the MIT licensed
 * way\generators package (c) Jeffrey Way.
 *
 * @see https://github.com/JeffreyWay/Laravel-4-Generators/blob/master/src/Way/Generators/Syntax/AddToTable.php
 */
class MigrationColumnFactory
{

    protected $schema = [];

    /**
     * Factory method to return the migration column data
     * 
     * @param  array                    $fields array of data about the columns to create
     * @param  MigrationColumnFactory   $worker factory instance to use
     * @return string                           migration add column code
     */
    public static function make($fields, MigrationColumnFactory $worker = null)
    {
        $factory = $worker ?: new static;
        return $factory->addColumns($fields);
    }

    /**
     * Return string for adding all columns
     *
     * @param $fields
     * @return array
     */
    protected function addColumns($fields)
    {
        $this->schema = [];

        foreach ($fields as $property => $details) {

            if($this->fieldNeedsForeignConstraint($details))
            {
                if (($key = array_search('foreign', $details['decorators'])) !== FALSE) {
                    unset($details['decorators'][$key]);
                }
                $this->schema[] = $this->addColumn($property, $details);
                $this->addForeignConstraint($property, $details);
            }else{
                $this->schema[] = $this->addColumn($property, $details);
            }

        }

        //add increments and timestamps
        array_unshift($this->schema, "\$table->increments('id');");
        array_push($this->schema, "\$table->timestamps();");

        return $this->schema;
    }

    /**
     * Return string for adding a column
     *
     * @param $property
     * @param $details
     * @return string
     */
    private function addColumn($property, $details)
    {
        $type = $details['type'];
        $output = "\$table->$type('$property')";

        if (isset($details['args'])) {

            $output = "\$table->$type('$property', " . implode(', ', $details['args']) . ")";
        }

        if (isset($details['decorators'])) {

            $output .= $this->addDecorators($details['decorators']);
        }

        return $output . ';';
    }

    /**
     * @param $decorators
     * @return string
     */
    protected function addDecorators($decorators)
    {
        $output = '';

        foreach ($decorators as $decorator) {
            $output .= "->$decorator";

            // Do we need to tack on the parens?
            if (strpos($decorator, '(') === false) {
                $output .= '()';
            }
        }

        return $output;
    }

        /**
     * Add a foreign constraint field to the schema.
     *
     * @param array $segments
     */
    private function addForeignConstraint($property, $segments)
    {
        $segments['type'] = 'foreign';
        $segments['decorators'] = [ "references('id')",
          sprintf("on('%s')", $this->getTableNameFromForeignKey($property))];

        $this->schema[] = $this->addColumn($property, $segments);
    }
    /**
     * Try to figure out the name of a table from a foreign key.
     * Ex: user_id => users
     *
     * @param  string $key
     * @return string
     */
    private function getTableNameFromForeignKey($key)
    {
        return str_plural(str_replace('_id', '', $key));
    }

    /**
     * Determine if the user wants a foreign constraint for the field.
     *
     * @param  array $segments
     * @return bool
     */
    private function fieldNeedsForeignConstraint($segments) {
        return isset($segments['decorators']) && !!in_array('foreign', $segments['decorators']);
    }
}
