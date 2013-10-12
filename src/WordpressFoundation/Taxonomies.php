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
class Taxonomies extends Provider {

    protected $taxonomies = array();

    public function __construct(Pimple $container, array $taxonomies)
    {
        parent::__construct($container);

        $this->setTaxonomies($taxonomies);
    }

    public function register()
    {
        foreach ($this->taxonomies as $name => $taxonomy)
        {
            register_taxonomy($name, array_get($taxonomy, 'type'), array_get($taxonomy, 'args'));
        }

        return $this;
    }

    public function addTaxonomy($name, $type, array $args = array())
    {
        $this->taxonomies[$name] = array(
            'type' => $type,
            'args' => $args,
        );

        return $this;
    }

    public function setTaxonomies(array $taxonomies)
    {
        foreach ($taxonomies as $taxonomy)
        {
            $this->addTaxonomy(
                array_get($taxonomy, 'name'),
                array_get($taxonomy, 'type'),
                array_get($taxonomy, 'args', array())
            );
        }

        return $this;
    }

    public function getTaxonomies()
    {
        return $this->taxonomies;
    }

}