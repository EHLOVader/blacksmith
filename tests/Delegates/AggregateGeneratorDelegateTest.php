<?php namespace Delegates;

use Delegates\AggregateGeneratorDelegate;
use Console\GenerateCommand;
use Configuration\ConfigReader;
use Generators\Generator;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Mustache_Engine;
use Mockery as m;
use org\bovigo\vfs\vfsStream;

class AggregateGeneratorDelegateTest extends \BlacksmithTest
{
    private $command;

    private $config;

    private $generator;

    private $args;

    private $optionReader;

    public function setUp()
    {
        parent::setUp();
        $this->command = m::mock('Console\GenerateCommand');
        $this->config = m::mock('Configuration\ConfigReader');
        $this->generator = m::mock('Generators\Generator');
        $this->genFactory = m::mock('Factories\GeneratorFactory');
        $this->filesystem = m::mock('Illuminate\Filesystem\Filesystem');
        $this->filesystem->shouldDeferMissing();
        $this->args = [
            'command'     => 'generate',
            'entity'      => 'Order',
            'what'        => 'scaffold',
            'config-file' => null,
        ];

        $this->optionReader = m::mock('Console\OptionReader');
        $this->optionReader->shouldReceive('isGenerationForced')->andReturn(false);
        $this->optionReader->shouldReceive('getFields')->andReturn([]);
        $this->optionReader->shouldDeferMissing();

        $this->genFactory
            ->shouldReceive('make')
            ->with($this->args['what'], $this->optionReader)
            ->andReturn($this->generator);
    }



    public function testRunWithInvalidConfigAndFails()
    {
        $this->config->shouldReceive('validateConfig')->once()
            ->andReturn(false);

        $this->command->shouldReceive('comment')->once()
            ->with('Error', 'The loaded configuration file is invalid', true);

        $delegate = new AggregateGeneratorDelegate(
            $this->command,
            $this->config,
            $this->genFactory,
            $this->filesystem,
            $this->args,
            $this->optionReader
        );
        $this->assertFalse($delegate->run());
    }



    public function testRunWithInvalidGenerationRequestAndFails()
    {
        //change the args to have an invalid generation request
        $requested = 'something-invalid';
        $this->args['what'] = $requested;

        //mock valid options
        $options = $this->getValidOptions();

        $this->config->shouldReceive('validateConfig')->once()
            ->andReturn(true);

        //return possible aggregates that include the requested
        $this->config->shouldReceive('getAvailableAggregates')->once()
            ->andReturn(array_keys($options));

        $this->command->shouldReceive('comment')->once()
            ->with('Error', "{$requested} is not a valid option", true);

        $this->command->shouldReceive('comment')->once()
            ->with('Error Details', "Please choose from: ". implode(", ", array_keys($options)), true);

        $delegate = new AggregateGeneratorDelegate(
            $this->command,
            $this->config,
            $this->genFactory,
            $this->filesystem,
            $this->args,
            $this->optionReader
        );
        $this->assertFalse($delegate->run());
    }


    public function testRunWithValidArgumentsShouldSucceed()
    {
        //mock valid options
        $options = $this->getValidOptions();
        $cnt = count($options['scaffold']);

        $this->config->shouldReceive('validateConfig')->once()
            ->andReturn(true);

        //return possible aggregates that include the requested
        $this->config->shouldReceive('getAvailableAggregates')->once()
            ->andReturn(array_keys($options));

        $this->config->shouldReceive('getAggregateValues')->once()
            ->with('scaffold')
            ->andReturn($options['scaffold']);

        $baseDir = '/path/to';
        $this->config->shouldReceive('getConfigDirectory')->times($cnt)
            ->andReturn($baseDir);

        //settings to be returned by getConfigValue below
        $settings = [
            ConfigReader::CONFIG_VAL_TEMPLATE  => 'template.txt',
            ConfigReader::CONFIG_VAL_DIRECTORY => '/path/to/dir',
            ConfigReader::CONFIG_VAL_FILENAME  => 'Output.php'
        ];


        foreach ($options['scaffold'] as $to_generate) {

            $this->config->shouldReceive('getConfigValue')
                ->withAnyArgs()
                ->andReturn($settings);

            $this->genFactory->shouldReceive('make')->once()
                ->with($to_generate, $this->optionReader)
                ->andReturn($this->generator);

            //mock call to generator->make()
            $this->generator->shouldReceive('make')
                ->withAnyArgs()->andReturn(true);

            $dest = '/path/to/dir/Output.php';
            $this->generator->shouldReceive('getTemplateDestination')
                ->andReturn($dest);

            $this->command->shouldReceive('comment')->withAnyArgs();

        }//end foreach

        $delegate = new AggregateGeneratorDelegate(
            $this->command,
            $this->config,
            $this->genFactory,
            $this->filesystem,
            $this->args,
            $this->optionReader
        );

        $this->assertTrue($delegate->run());
    }


    public function testRunWithFalseArgumentsShouldSucceed()
    {
        //mock valid options
        $options = $this->getValidOptions();

        $cnt = count($options['scaffold']);

        $this->config->shouldReceive('validateConfig')->once()
            ->andReturn(true);

        //return possible aggregates that include the requested
        $this->config->shouldReceive('getAvailableAggregates')->once()
            ->andReturn(array_keys($options));

        $options['scaffold']['view_show'] = false;
        $this->config->shouldReceive('getAggregateValues')->once()
            ->with('scaffold')
            ->andReturn($options['scaffold']);

        $baseDir = '/path/to';
        $this->config->shouldReceive('getConfigDirectory')->times($cnt)
            ->andReturn($baseDir);

        //settings to be returned by getConfigValue below
        $settings = [
            ConfigReader::CONFIG_VAL_TEMPLATE  => 'template.txt',
            ConfigReader::CONFIG_VAL_DIRECTORY => '/path/to/dir',
            ConfigReader::CONFIG_VAL_FILENAME  => 'Output.php'
        ];

        foreach ($options['scaffold'] as $to_generate) {
            //test skipping generation
            if ($to_generate === 'view_show') {
                $this->config->shouldReceive('getConfigValue')->once()
                    ->with($to_generate)
                    ->andReturn(false);
                $this->command->shouldReceive('comment')
                    ->with(
                        "Blacksmith",
                        "I skipped \"".$to_generate."\"",
                        true
                    );
                continue;
            }

            $this->genFactory->shouldReceive('make')->once()
                ->with($to_generate, $this->optionReader)
                ->andReturn($this->generator);

            $this->config->shouldReceive('getConfigValue')
                ->with($to_generate)
                ->andReturn($settings);

            //mock call to generator->make()
            $this->generator->shouldReceive('make')
                ->withAnyArgs()->andReturn(true);

            $dest = '/path/to/dir/Output.php';
            $this->generator->shouldReceive('getTemplateDestination')
                ->andReturn($dest);

            $this->command->shouldReceive('comment')->withAnyArgs();

        }//end foreach

        $delegate = new AggregateGeneratorDelegate(
            $this->command,
            $this->config,
            $this->genFactory,
            $this->filesystem,
            $this->args,
            $this->optionReader
        );
        $this->assertTrue($delegate->run());
    }

    public function testRunWithValidArgumentsShouldFail()
    {
        //mock valid options
        $options = $this->getValidOptions();
        $cnt = count($options['scaffold']);

        $this->config->shouldReceive('validateConfig')->once()
            ->andReturn(true);

        //return possible aggregates that include the requested
        $this->config->shouldReceive('getAvailableAggregates')->once()
            ->andReturn(array_keys($options));

        $this->config->shouldReceive('getAggregateValues')->once()
            ->with('scaffold')
            ->andReturn($options['scaffold']);

        $baseDir = '/path/to';
        $this->config->shouldReceive('getConfigDirectory')->times($cnt)
            ->andReturn($baseDir);

        //settings to be returned by getConfigValue below
        $settings = [
            ConfigReader::CONFIG_VAL_TEMPLATE  => 'template.txt',
            ConfigReader::CONFIG_VAL_DIRECTORY => '/path/to/dir',
            ConfigReader::CONFIG_VAL_FILENAME  => 'Output.php'
        ];


        foreach ($options['scaffold'] as $to_generate) {

            $this->config->shouldReceive('getConfigValue')
                ->withAnyArgs()
                ->andReturn($settings);

            $this->genFactory->shouldReceive('make')->once()
                ->with($to_generate, $this->optionReader)
                ->andReturn($this->generator);

            //mock call to generator->make()
            $this->generator->shouldReceive('make')
                ->withAnyArgs()->andReturn(false);


            $this->command->shouldReceive('comment')
                ->with(
                    "Blacksmith",
                    "An unknown error occurred, nothing was generated for {$to_generate}",
                    true
                );

        }//end foreach

        $delegate = new AggregateGeneratorDelegate(
            $this->command,
            $this->config,
            $this->genFactory,
            $this->filesystem,
            $this->args,
            $this->optionReader
        );
        $this->assertTrue($delegate->run());
    }


    public function testUpdateRoutesFile()
    {
        $entity = 'order';
        $name = 'orders';
        $dir = 'root';
        $routes = implode(DIRECTORY_SEPARATOR, [$dir, 'app', 'Http', 'routes.php']);
        $data = '/**** Route and IOC Binding for ' . ucwords($entity) . " ****/" . "\n";
        $data .= "Route::resource('" . $name . "', '" . ucwords($name) . "Controller');" . "\n";
        // App binding if not setup properly
        $data .= "App::bind('App\\Contracts\\Repositories\\" . ucwords($entity) . "RepositoryInterface', 'App\\Repositories\\Db" . ucwords($entity) . "Repository');";

        $root = vfsStream::setup('root', NULL, ['app' => ['Http' => ['routes.php' => '<?php ']]]);


        $this->filesystem->shouldReceive('get')->once()
            ->with(vfsStream::URL($routes))
            ->andReturn('');

        $this->filesystem->shouldReceive('exists')->once()
            ->with(vfsStream::URL($routes))
            ->andReturn(true);

        $this->filesystem->shouldReceive('append')->once()
            ->with(vfsStream::URL($routes), \Mockery::any());

        $delegate = new AggregateGeneratorDelegate(
            $this->command,
            $this->config,
            $this->genFactory,
            $this->filesystem,
            $this->args,
            $this->optionReader
        );
        $delegate->updateRoutesFile($name, vfsStream::URL($dir));
    }


    public function testNotUpdateRoutesFileWithDuplicates()
    {
        $name = 'orders';
        $dir = 'root';
        $routes = implode(DIRECTORY_SEPARATOR, [$dir, 'app', 'Http', 'routes.php']);
        $route = "Route::resource('" . $name . "', '" . ucwords($name) . "Controller');";
        $data = "\n\n".$route;

        $root = vfsStream::setup('root', NULL, ['app' => ['Http' => ['routes.php' => "<?php \n\n " . $data]]]);


        $this->filesystem->shouldReceive('get')->once()
            ->with(vfsStream::URL($routes))
            ->andReturn($route);

        $this->filesystem->shouldReceive('exists')->once()
            ->with(vfsStream::URL($routes))
            ->andReturn(true);

        $this->filesystem->shouldReceive('append')->never()
            ->with(vfsStream::URL($routes), $data);

        $delegate = new AggregateGeneratorDelegate(
            $this->command,
            $this->config,
            $this->genFactory,
            $this->filesystem,
            $this->args,
            $this->optionReader
        );
        $delegate->updateRoutesFile($name, vfsStream::URL($dir));
    }

    private function getValidOptions()
    {
        return [
                'scaffold' => [
                    "model",
                    "controller",
                    "seed",
                    "migration_create",
                    "view_create",
                    "view_update",
                    "view_show",
                    "view_index",
                    "form",
                    "unit_test",
                    "service_creator",
                    "service_creator_test",
                    "service_updater",
                    "service_updater_test",
                    "service_destroyer",
                    "service_destroyer_test",
                    "validator"
                ]
            ];
    }
}
