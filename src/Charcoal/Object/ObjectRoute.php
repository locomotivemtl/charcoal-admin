<?php
namespace Charcoal\Object;

use DateTime;
use InvalidArgumentException;
use RuntimeException;

use Pimple\Container;

// Dependencies from 'charcoal-core'
use Charcoal\Model\AbstractModel;
use Charcoal\Loader\CollectionLoader;

// Dependency from 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// Local Dependency
use Charcoal\Object\ObjectRouteInterface;

/**
 * Represents a route to an object (i.e., a permalink).
 *
 * Intended to be used to collect all routes related to models
 * under a single source (e.g., database table).
 *
 * {@see Charcoal\Object\ObjectRevision} for a similar model that aggregates data
 * under a common source.
 *
 * Requirements:
 *
 * - 'model/factory'
 * - 'model/collection/loader'
 */
class ObjectRoute extends AbstractModel implements
    ObjectRouteInterface
{
    /**
     * A route is active by default.
     *
     * @var boolean
     */
    protected $active = true;

    /**
     * The route's URI.
     *
     * @var string
     */
    protected $slug;

    /**
     * The route's locale.
     *
     * @var string
     */
    protected $lang;

    /**
     * The creation timestamp.
     *
     * @var DateTime
     */
    protected $creationDate;

    /**
     * The last modification timestamp.
     *
     * @var DateTime
     */
    protected $lastModificationDate;

    /**
     * The foreign object type related to this route.
     *
     * @var string
     */
    protected $routeObjType;

    /**
     * The foreign object ID related to this route.
     *
     * @var mixed
     */
    protected $routeObjId;

    /**
     * The foreign object's template identifier.
     *
     * @var string
     */
    protected $routeTemplate;

    /**
     * Store a copy of the original—_preferred_—slug before alterations are made.
     *
     * @var string
     */
    private $originalSlug;

    /**
     * Store the increment used to create a unique slug.
     *
     * @var integer
     */
    private $slugInc = 0;

    /**
     * Store the factory instance for the current class.
     *
     * @var FactoryInterface
     */
    private $modelFactory;

    /**
     * Store the collection loader for the current class.
     *
     * @var CollectionLoader
     */
    private $collectionLoader;

    /**
     * Inject dependencies from a DI Container.
     *
     * @param Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        $this->setModelFactory($container['model/factory']);
        $this->setCollectionLoader($container['model/collection/loader']);
    }

    /**
     * Event called before _creating_ the object.
     *
     * @see    Charcoal\Source\StorableTrait::preSave() For the "create" Event.
     * @return boolean
     */
    public function preSave()
    {
        $this->generateUniqueSlug();
        $this->setCreationDate('now');
        $this->setLastModificationDate('now');

        return parent::preSave();
    }

    /**
     * Event called before _updating_ the object.
     *
     * @see    Charcoal\Source\StorableTrait::preUpdate() For the "update" Event.
     * @param  array $properties Optional. The list of properties to update.
     * @return boolean
     */
    public function preUpdate(array $properties = null)
    {
        $this->setCreationDate('now');
        $this->setLastModificationDate('now');

        return parent::preUpdate($properties);
    }

    /**
     * Determine if the current slug is unique.
     *
     * @return boolean
     */
    public function isSlugUnique()
    {
        $proto  = $this->modelFactory()->get(self::class);
        $loader = $this->collectionLoader();
        $loader
            ->reset()
            ->setModel($proto)
            ->addFilter('active', true)
            ->addFilter('slug', $this->slug())
            ->addOrder('creation_date', 'desc')
            ->setPage(1)
            ->setNumPerPage(1);
        $routes = $loader->load()->objects();
        if (!$routes) {
            return true;
        }
        $obj = reset($routes);
        if (!$obj->id()) {
            return true;
        }
        if ($obj->id() === $this->id()) {
            return true;
        }
        if ($obj->routeObjId() === $this->routeObjId() &&
            $obj->routeObjType() === $this->routeObjType() &&
            $obj->lang() === $this->lang()) {
            $this->setId($obj->id());
            return true;
        }
        return false;
    }

    /**
     * Generate a unique URL slug for routable object.
     *
     * @return self
     */
    public function generateUniqueSlug()
    {
        if (!$this->isSlugUnique()) {
            if (!$this->originalSlug) {
                $this->originalSlug = $this->slug();
            }
            $this->slugInc++;
            $this->setSlug($this->originalSlug.'-'.$this->slugInc);
            return $this->generateUniqueSlug();
        }
        return $this;
    }

    /**
     * Set an object model factory.
     *
     * @param FactoryInterface $factory The model factory, to create objects.
     * @return self
     */
    protected function setModelFactory(FactoryInterface $factory)
    {
        $this->modelFactory = $factory;
        return $this;
    }

    /**
     * Set a model collection loader.
     *
     * @param CollectionLoader $loader The collection loader.
     * @return self
     */
    protected function setCollectionLoader(CollectionLoader $loader)
    {
        $this->collectionLoader = $loader;
        return $this;
    }

    /**
     * Set the object route URI.
     *
     * @param string|null $slug The route.
     * @throws InvalidArgumentException If the slug argument is not a string.
     * @return self
     */
    public function setSlug($slug)
    {
        if ($slug === null) {
            $this->slug = null;
            return $this;
        }
        if (!is_string($slug)) {
            throw new InvalidArgumentException(
                'Slug is not a string'
            );
        }
        $this->slug = $slug;

        return $this;
    }

    /**
     * Set the locale of the object route.
     *
     * @param string $lang The route's locale.
     * @return self
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * @param \DateTime|string|null $creationDate The Creation Date date/time.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return self
     */
    public function setCreationDate($creationDate)
    {
        if ($creationDate === null) {
            $this->creationDate = null;
            return $this;
        }

        if (is_string($creationDate)) {
            $creationDate = new DateTime($creationDate);
        }
        if (!($creationDate instanceof DateTime)) {
            throw new InvalidArgumentException(
                'Invalid "Creation Date" value. Must be a date/time string or a DateTime object.'
            );
        }
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * @param \DateTime|string|null $lastModificationDate The Last modification date date/time.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return self
     */
    public function setLastModificationDate($lastModificationDate)
    {
        if ($lastModificationDate === null) {
            $this->lastModificationDate = null;
            return $this;
        }
        if (is_string($lastModificationDate)) {
            $lastModificationDate = new DateTime($lastModificationDate);
        }
        if (!($lastModificationDate instanceof DateTime)) {
            throw new InvalidArgumentException(
                'Invalid "Creation Date" value. Must be a date/time string or a DateTime object.'
            );
        }
        $this->lastModificationDate = $lastModificationDate;
        return $this;
    }

    /**
     * Set the foreign object type related to this route.
     *
     * @param string $type The object type.
     * @return self
     */
    public function setRouteObjType($type)
    {
        $this->routeObjType = $type;

        return $this;
    }

    /**
     * Set the foreign object ID related to this route.
     *
     * @param string $id The object ID.
     * @return self
     */
    public function setRouteObjId($id)
    {
        $this->routeObjId = $id;

        return $this;
    }

    /**
     * Set the foreign object's template identifier.
     *
     * @param string $template The template identifier.
     * @return self
     */
    public function setRouteTemplate($template)
    {
        $this->routeTemplate = $template;

        return $this;
    }

    /**
     * Retrieve the object model factory.
     *
     * @throws RuntimeException If the model factory was not previously set.
     * @return FactoryInterface
     */
    public function modelFactory()
    {
        if (!isset($this->modelFactory)) {
            throw new RuntimeException(
                sprintf('Model Factory is not defined for "%s"', get_class($this))
            );
        }
        return $this->modelFactory;
    }

    /**
     * Retrieve the model collection loader.
     *
     * @throws RuntimeException If the collection loader was not previously set.
     * @return CollectionLoader
     */
    public function collectionLoader()
    {
        if (!isset($this->collectionLoader)) {
            throw new RuntimeException(
                sprintf('Collection Loader is not defined for "%s"', get_class($this))
            );
        }
        return $this->collectionLoader;
    }

    /**
     * Retrieve the object route.
     *
     * @return string
     */
    public function slug()
    {
        return $this->slug;
    }

    /**
     * Retrieve the locale of the object route.
     *
     * @return string
     */
    public function lang()
    {
        return $this->lang;
    }

    /**
     * Creation date.
     * @return DateTime Creation date.
     */
    public function creationDate()
    {
        return $this->creationDate;
    }

    /**
     * Last modification date.
     * @return DateTime Last modification date.
     */
    public function lastModificationDate()
    {
        return $this->lastModificationDate;
    }

    /**
     * Retrieve the foreign object type related to this route.
     *
     * @return string
     */
    public function routeObjType()
    {
        return $this->routeObjType;
    }

    /**
     * Retrieve the foreign object ID related to this route.
     *
     * @return string
     */
    public function routeObjId()
    {
        return $this->routeObjId;
    }

    /**
     * Retrieve the foreign object's template identifier.
     *
     * @return string
     */
    public function routeTemplate()
    {
        return $this->routeTemplate;
    }

    /**
     * Alias of {@see self::slug()}.
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->slug();
    }
}
