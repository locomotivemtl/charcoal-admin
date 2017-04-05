<?php

namespace Charcoal\Object\Tests;

use DateTime;

// From Pimple
use Pimple\Container;

// From 'charcoal-translator'
use Charcoal\Translator\Translator;
use Charcoal\Translator\LocalesManager;

// From 'charcoal-object'
use Charcoal\Object\ObjectRoute;
use Charcoal\Object\RoutableTrait;
use Charcoal\Object\Tests\ContainerProvider;
use Charcoal\Object\Tests\Mocks\RoutableClass as RoutableObject;

/**
 *
 */
class RoutableTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tested Class.
     *
     * @var RoutableTrait
     */
    private $obj;

    /**
     * Store the service container.
     *
     * @var Container
     */
    private $container;

    /**
     * Store the translator service.
     *
     * @var Translator
     */
    private $translator;

    /**
     * Set up the test.
     */
    public function setUp()
    {
        $container = $this->container();

        $route = $container['model/factory']->get(ObjectRoute::class);
        if ($route->source()->tableExists() === false) {
            $route->source()->createTable();
        }

        $this->obj = new RoutableObject([
            'factory'    => $container['model/factory'],
            'translator' => $this->translator()
        ]);
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
        $this->obj->setMetadata([
            'routable' => [
                'pattern' => 'foofoo'
            ]
        ]);
        $this->assertEquals('foofoo', $this->obj->slugPattern());
    }

    public function testSlugPatternWithoutRoutable()
    {
        $this->obj->setMetadata([
            'routable'     => null,
            'slug_pattern' => 'barbar'
        ]);
        $this->assertEquals('barbar', $this->obj->slugPattern());
    }

    public function testSlugPatternWithoutMetadata()
    {
        $this->obj->setMetadata([]);

        $this->setExpectedException('\Exception');
        $this->obj->slugPattern();
    }

    public function testSlugPrefix()
    {
        $this->assertEquals('', $this->obj->slugPrefix());

        $this->obj->setMetadata([
            'routable' => [
                'prefix' => 'barfoo'
            ]
        ]);
        $this->assertEquals('barfoo', $this->obj->slugPrefix());
    }

    public function testSlugSuffix()
    {
        $this->assertEquals('', $this->obj->slugSuffix());

        $this->obj->setMetadata([
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
        $this->obj->setMetadata([
            'routable' => [
                'editable' => true
            ]
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
        $container = $this->container();

        $this->obj->setMetadata([
            'routable' => [
                'pattern' => 'FooFoo',
                'prefix'  => 'bar-',
                'suffix'  => '-baz'
            ]
        ]);

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
            [ 'A B C', 'a-b-c' ],
            [ '_this_is_a_test_', 'this-is-a-test' ],
            [ 'Allö Bébé!', 'allo-bebe' ],
            [ '"Hello-#-{$}-£™¡¢∞§¶•ªº-World"', 'hello-world' ],
            [ '&quot;', 'quot' ],
            [ 'fr/14/Services Santé et Sécurité au Travail', 'fr/14/services-sante-et-securite-au-travail' ],
            [ 'fr/ 14/Services S   anté et Sécurité au Travail', 'fr/14/services-s-ante-et-securite-au-travail' ],
            [ 'ÓóÔô Œœ Ææ', 'oooo-oeoe-aeae']
        ];
    }

    private function translator()
    {
        if ($this->translator === null) {
            $this->translator = new Translator([
                'manager' => new LocalesManager([
                    'locales' => [
                        'en'  => [ 'locale' => 'en-US' ],
                        'fr'  => [ 'locale' => 'fr-CA' ]
                    ],
                    'default_language'   => 'en',
                    'fallback_languages' => [ 'en' ]
                ])
            ]);
        }

        return $this->translator;
    }

    /**
     * Set up the service container.
     *
     * @return Container
     */
    private function container()
    {
        if ($this->container === null) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerBaseServices($container);
            $containerProvider->registerModelFactory($container);
            $containerProvider->registerModelCollectionLoader($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
