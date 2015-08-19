<?php namespace Generators;

use DateTime;
use Console\OptionReader;
use Illuminate\Filesystem\Filesystem;
use Parsers\FieldParser;
use Mustache_Engine;
use Mockery as m;

class FormTest extends \BlacksmithTest
{

    public function testParentClass()
    {
        $instance = new Form(
            new Filesystem,
            new Mustache_Engine,
            new FieldParser,
            new OptionReader([])
        );
        $this->assertInstanceOf("Generators\Generator", $instance);
    }



    public function testGetElementTypes()
    {
        $g = m::mock('Generators\Form');
        $g->shouldDeferMissing();

        $this->assertEquals('text', $g->getElementType('string'));
        $this->assertEquals('text', $g->getElementType('float'));
        $this->assertEquals('text', $g->getElementType('date'));
        $this->assertEquals('textarea', $g->getElementType('text'));
        $this->assertEquals('checkbox', $g->getElementType('boolean'));
    }



    public function testGetTemplateVars()
    {
        $generator = m::mock('Generators\Form');
        $generator->shouldDeferMissing();

        $generator->shouldReceive('getEntityName')->once()
            ->andReturn('Order');

        $fieldData = [
            'name' => ['type' => 'string']
        ];

        $generator->shouldReceive('getFieldData')->once()
            ->andReturn($fieldData);

        $form_rows = [
            [
                'label' => "{!! Form::label('name', 'Name:') !!}",
                'element' => "{!! Form::text('name', null, ['class'=>'form-control']) !!}"
            ]
        ];

        $expected = [
            'Entity'     => 'Order',
            'Entities'   => 'Orders',
            'collection' => 'orders',
            'instance'   => 'order',
            'fields'     => $fieldData,
            'form_rows'  => $form_rows
        ];

        $this->assertEquals($expected, $generator->getTemplateVars());
    }

    public function testGetRelationVars() {
        $generator = m::mock('Generators\Form');
        $generator->shouldDeferMissing();

        $generator->shouldReceive('getEntityName')->once()
          ->andReturn('Order');

        $fieldData = [
          'user_id' => ['type' => 'integer', 'decorators'=>['foreign']]
        ];

        $generator->shouldReceive('getFieldData')->once()
          ->andReturn($fieldData);

        $form_rows = [
          [
            'label'   => "{!! Form::label('user_id', 'User:') !!}",
            'element' => "{!! Form::select('user_id', App\\Models\\User::lists('name', 'id'), null, ['class'=>'form-control']) !!}"
          ]
        ];

        $expected = [
          'Entity'     => 'Order',
          'Entities'   => 'Orders',
          'collection' => 'orders',
          'instance'   => 'order',
          'fields'     => $fieldData,
          'form_rows'  => $form_rows
        ];

        $this->assertEquals($expected, $generator->getTemplateVars());
    }

}
