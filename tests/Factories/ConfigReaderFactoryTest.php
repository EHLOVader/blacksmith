<?php namespace Factories;

use Configuration\ConfigReaderInterface;
use Factories\ConfigReaderFactory;
use Mockery as m;

class ConfigReaderFactoryTest extends \BlacksmithTest
{

    public function testMakesValidConfigReaderWithGivenPath()
    {
        $path = '/path/to/config.json';
        $fs = m::mock('Illuminate\Filesystem\Filesystem');
        $fs->shouldReceive('exists')->once()->with($path)->andReturn(true);
        $fs->shouldReceive('get')->once()->with($path);

        $reader = (new ConfigReaderFactory)->make($path, $fs);
        $this->assertInstanceOf("Configuration\\ConfigReaderInterface", $reader);
    }

    public function testMakesValidConfigReaderWithHomePath() {
        $path = rtrim(getenv('HOME'), '/') . '/.blacksmith/config.json';
        $fs = m::mock('Illuminate\Filesystem\Filesystem');
        $fs->shouldReceive('exists')->once()->with($path)->andReturn(TRUE);
        $fs->shouldReceive('get')->once()->withAnyArgs();

        $reader = (new ConfigReaderFactory)->make(NULL, $fs);
        $this->assertInstanceOf("Configuration\\ConfigReaderInterface", $reader);

        $dirExp = explode(DIRECTORY_SEPARATOR, $reader->getConfigDirectory());
        $this->assertEquals(
          '.blacksmith',
          $dirExp[count($dirExp) - 1]
        );
    }

    public function testMakesValidConfigReaderWithProjectPath() {
        $path = getcwd() . '/.blacksmith/config.json';
        $fs = m::mock('Illuminate\Filesystem\Filesystem');
        $fs->shouldReceive('exists')->once()->withAnyArgs()->andReturn(FALSE);
        $fs->shouldReceive('exists')->once()->with($path)->andReturn(TRUE);
        $fs->shouldReceive('get')->once()->withAnyArgs();

        $reader = (new ConfigReaderFactory)->make(NULL, $fs);
        $this->assertInstanceOf("Configuration\\ConfigReaderInterface", $reader);

        $dirExp = explode(DIRECTORY_SEPARATOR, $reader->getConfigDirectory());
        $this->assertEquals(
          '.blacksmith',
          $dirExp[count($dirExp) - 1]
        );
    }

    public function testMakesValidConfigReaderWithoutGivenPath()
    {
        $path = __DIR__.'/../../src/lib/Generators/templates/hexagonal/config.json';
        $fs = m::mock('Illuminate\Filesystem\Filesystem');
        $fs->shouldReceive('exists')->times()->withAnyArgs()->andReturn(false);
        $fs->shouldReceive('get')->once()->withAnyArgs();

        $reader = (new ConfigReaderFactory)->make(null, $fs);
        $this->assertInstanceOf("Configuration\\ConfigReaderInterface", $reader);

        $dirExp = explode(DIRECTORY_SEPARATOR, $reader->getConfigDirectory());
        $this->assertEquals(
            'hexagonal',
            $dirExp[count($dirExp)-1]
        );
    }
}
