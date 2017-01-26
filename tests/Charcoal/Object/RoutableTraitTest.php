<?php

namespace Charcoal\Tests\Object;

use DateTime;

use Psr\Log\NullLogger;

use Charcoal\Model\Service\MetadataLoader;

use Charcoal\Object\RoutableTrait as RoutableTrait;

/**
 *
 */
class RoutableTraitTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    /**
     * Create mock object from trait.
     */
    public function setUp()
    {
        $this->obj = $this->getMockForTrait(
            RoutableTrait::class,
            [],
            '',
            true,
            true,
            true,
            [ 'metadata' ]
        );
    }

    public function testSlugPattern()
    {
        // $this->assertEquals('', $this->obj->slugPattern());
        $ret = $this->obj->setSlugPattern('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', (string)$this->obj->slugPattern());

        $this->obj->setSlugPattern(null);
        // $this->assertEquals('', $this->obj->slugPattern());
    }

    public function testSlugPatternRoutable()
    {
        $this->obj->expects($this->any())
            ->method('metadata')
            ->willReturn([
                'routable' => [
                    'pattern' => 'foofoo'
                ]
            ]);
        $this->assertEquals('foofoo', $this->obj->slugPattern());
    }

    public function testSlugPatternWithoutRoutable()
    {
        $this->obj->expects($this->any())
            ->method('metadata')
            ->willReturn([
                'routable' => null,
                'slug_pattern' => 'barbar'
            ]);
        $this->assertEquals('barbar', $this->obj->slugPattern());
    }

    public function testSlugPatternWithoutMetadata()
    {
        $this->obj->expects($this->any())
            ->method('metadata')
            ->willReturn([]);

        $this->setExpectedException('\Exception');
        $this->obj->slugPattern();
    }

    public function testSlugPrefix()
    {
        $this->assertEquals('', $this->obj->slugPrefix());

        $this->obj->expects($this->any())
            ->method('metadata')
            ->willReturn([
                'routable' => [
                    'prefix' => 'barfoo'
                ]
            ]);
        $this->assertEquals('barfoo', $this->obj->slugPrefix());
    }

    public function testSlugSuffix()
    {
        $this->assertEquals('', $this->obj->slugSuffix());

        $this->obj->expects($this->any())
            ->method('metadata')
            ->willReturn([
                'routable' => [
                    'suffix' => 'barfoo'
                ]
            ]);
        $this->assertEquals('barfoo', $this->obj->slugSuffix());
    }

    public function testIsSlugEditableIsFalseByDefault()
    {
        $this->assertFalse($this->obj->isSlugEditable());
    }

    public function testIsSlugEditable()
    {
        $this->obj->expects($this->any())
            ->method('metadata')
            ->willReturn([
                'routable' => [
                    'editable' => true                ]
            ]);
        $this->assertTrue($this->obj->isSlugEditable());
    }

    public function testSlug()
    {
        $this->assertNull($this->obj->slug());

        $ret = $this->obj->setSlug('test123');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('test123', $this->obj->slug());

        $this->obj->setSlug(null);
        $this->assertNull($this->obj->slug());
    }

    public function testGenerateSlug()
    {
        $this->obj->expects($this->any())
            ->method('metadata')
            ->willReturn([
                'routable' => [
                    'pattern' => 'FooFoo',
                    'prefix'  => 'bar-',
                    'suffix'  => '-baz'
                ]
            ]);
        $metadataLoader = new MetadataLoader([
            'logger' => new NullLogger(),
            'base_path' => __DIR__.'/../../../',
            'paths' => ['metadata'],
            'config' => $GLOBALS['container']['config'],
            'cache'  => $GLOBALS['container']['cache']
        ]);
        $factory = new \Charcoal\Factory\GenericFactory([
            'arguments' => [[
                'logger' => new NullLogger(),
                'metadata_loader' => $metadataLoader,
                'source_factory' => new \Charcoal\Factory\GenericFactory([
                    'map'=>[
                        'database' => '\Charcoal\Source\DatabaseSource',

                    ],
                    'arguments' => [[
                        'logger' => new NullLogger(),
                        'pdo'    => new \PDO('sqlite::memory:')
                    ]]
                ])
            ]]
        ]);
        $this->obj->expects($this->any())
            ->method('modelFactory')
            ->willReturn($factory);
        $ret = $this->obj->generateSlug();
        $this->assertEquals('barfoofoobaz', (string)$ret);
    }


    /**
     * @dataProvider providerSlugs
     */
    public function testSlugify($str, $slug)
    {
        $this->assertEquals($slug, $this->obj->slugify($str));
    }

    public function providerSlugs()
    {
        return [
            ['A B C', 'a-b-c'],
            ['_this_is_a_test_', 'this-is-a-test'],
            ['Allö Bébé!', 'allo-bebe'],
            ['"Hello-#-{$}-£™¡¢∞§¶•ªº-World"', 'hello-world'],
            ['&quot;', 'quot'],
            ['fr/14/Services Santé et Sécurité au Travail', 'fr/14/services-sante-et-securite-au-travail'],
            ['fr/ 14/Services S   anté et Sécurité au Travail', 'fr/14/services-s-ante-et-securite-au-travail']
        ];
    }
}
