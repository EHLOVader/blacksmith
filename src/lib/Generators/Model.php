<?php namespace Generators;

use Illuminate\Support\Str;

class Model extends Generator implements GeneratorInterface
{

  /**
   * Function to get the minimum template variables
   *
   * @return array
   */
  public function getTemplateVars() {
    $entity = $this->getEntityName();
    $fieldData = $this->getFieldData();
    $relations = $this->getRelations($fieldData);

    return [
      'Entity'     => Str::studly($entity),
      'Entities'   => Str::plural(Str::studly($entity)),
      'collection' => Str::plural(Str::snake($entity)),
      'instance'   => Str::singular(Str::snake($entity)),
      'fields'     => $fieldData,
      'relations'  => $relations
    ];
  }

  protected function getRelations($fields)
  {
    $relations = [];
    foreach($fields as $key=>$meta){
      if($this->fieldHasForeignConstraint($meta))
      {
        $relation['class'] = studly_case(str_replace('_id', '', $key));
        $relation['method'] = str_replace('_id', '', $key);
        $relations[] = $relation;
      }
    }

    return $relations;

  }

    /**
     * Determine if the user wants a foreign constraint for the field.
     *
     * @param  array $segments
     * @return bool
     */
    private function fieldHasForeignConstraint($segments) {
        return isset($segments['decorators']) && !!in_array('foreign', $segments['decorators']);
    }
}
