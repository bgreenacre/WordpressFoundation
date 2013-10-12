<?php namespace WordpressFoundation;
/**
 * WordpressFoundation Utilities
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

use Pimple;

/**
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class PostTypes extends Provider {

    /**
     * Holds all post types.
     *
     * @access protected
     * @var array
     */
    protected $types = array();

    /**
     * Set types array.
     * 
     * @param Pimple $container Plugin container object.
     * @param array  $types     Array of wordpress post types.
     */
    public function __construct(Pimple $container, array $types)
    {
        parent::__construct($container);

        $this->setTypes($types);
    }

    public function register()
    {
        foreach ($this->types as $type => $args)
        {
            register_post_type($type, $args);
        }

        return $this;
    }

    public function addType($type, array $args = array())
    {
        $this->types[$type] = $args;

        return $this;
    }

    public function setTypes(array $types)
    {
        foreach ($types as $type)
        {
            $this->addType(
                array_get($type, 'name'),
                array_get($type, 'args')
            );
        }

        return $this;
    }

    public function getTypes()
    {
        return $this->types;
    }

}