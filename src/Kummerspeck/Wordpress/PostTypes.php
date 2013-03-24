<?php namespace Kummerspeck\Wordpress;
/**
 * Kummerspeck Wordpress Utilities
 *
 * @package Kummerspeck/WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

/**
 *
 * @package Kummerspeck/WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class PostTypes {

    protected $_types;

    public function __construct(array $types)
    {
        $this->setTypes($types);
    }

    public function register()
    {
        foreach ($this->_types as $type => $args)
        {
            register_post_type($type, $args);
        }

        return $this;
    }

    public function addType($type, array $args = array())
    {
        $this->_types[$type] = $args;

        return $this;
    }

    public function setTypes(array $types)
    {
        foreach ($types as $type => $args)
        {
            $this->addType($type, $args);
        }

        return $this;
    }

    public function getTypes()
    {
        return $this->_types;
    }

}