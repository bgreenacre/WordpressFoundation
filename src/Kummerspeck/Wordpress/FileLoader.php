<?php namespace Kummerspeck\Wordpress;
/**
 * Kummerspeck Wordpress Utilities
 *
 * @package Kummerspeck/WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

use Symfony\Component\Yaml\Yaml;
use Kummerspeck\Arr\get_key;

/**
 * Loads file contents.
 *
 * @package Kummerspeck/WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class FileLoader {

    /**
     * Paths to look for files in.
     *
     * @access protected
     * @var array
     */
    protected $_paths = array();

    /**
     * Construct object.
     *
     * @access public
     * @param array $paths Array of paths to load files from.
     * @return void
     */
    public function __construct(array $paths)
    {
        $this->setPaths($paths);
    }

    /**
     * Search for a file and load it's contents in a set
     * of paths to look in.
     *
     * @access public
     * @param  array  $paths     Array of paths to search for file in.
     * @param  string $file      Filename to look for.
     * @param  string $extension File extension.
     * @return mixed             Return files contents
     * @throws Exception         If file was not found in any of the paths.
     */
    public function loadByPaths(array $paths, $file, $extension)
    {
        // Cache a array index count.
        $pathsCount = count($paths);

        for ($i = 0; $i < $pathsCount; ++$i)
        {
            try
            {
                // Try to load the file with path.
                return $this->load($paths[$i] . $file, $extension);
            }
            catch (\Exception $e)
            {
                if ($i == ($pathsCount - 1) )
                {
                    // Re-throw the error when we've looked
                    // through all the paths.
                    throw $e;
                }
            }
        }
    }

    /**
     * Load a files contents and return it.
     *
     * @access public
     * @param  string $file      File to search for.
     * @param  string $extension File extension.
     * @return mixed             File contents.
     * @throws Exception         If file does not exist.
     */
    public function load($file, $extension)
    {
        $extension = '.' . $extension;

        if ( ! is_file($file . $extension))
        {
            // File doesn't exist so throw exception.
            $c = $this->getContainer();

            throw new \Exception(
                sprintf(
                    'File "%s" does not exist in plugin "%s".',
                    str_replace($c['paths.plugin'], '', $file) . $extension,
                    $c['plugin.name']
                )
            );
        }

        // Load file contents and parse contents based on
        // file extension.
        switch (ltrim($extension, '.'))
        {
            case 'yml':
                return Yaml::parser($file . $extension);

                break;
            case 'xml':
                return simplexml_load_file($file . $extension);

                break;
            case 'json':
                return json_decode(file_get_contents($file . $extension), true);

                break;
            case 'php':
                return include $file . $extension;

                break;
            default:
                return file_get_contents($file . $extension);

                break;
        }
    }

    /**
     * Load a config file.
     *
     * @access public
     * @param  string $file Filename.
     * @return array        File contents.
     */
    public function config($file)
    {
        $paths = $this->getPaths('config');

        if (is_array($paths))
        {
            $this->loadByPaths($paths, $file, 'yml');
        }

        return $this->load($paths . $file, 'yml');
    }

    /**
     * Load a resource file.
     *
     * @access public
     * @param  string $file      Filename
     * @param  [type] $extension File extension.
     * @return mixed             File contents
     */
    public function resource($file, $extension)
    {
        $paths = $this->getPaths('resources');

        if (is_array($paths))
        {
            $this->loadByPaths($paths, $file, $extension);
        }

        return $this->load($paths . $file, $extension);
    }

    /**
     * Set the paths array.
     *
     * @access public
     * @param array $paths Associative array of paths.
     * @return $this
     */
    public function setPaths(array $paths)
    {
        $this->_paths = $paths;

        return $this;
    }

    /**
     * Get all or a specific set of paths.
     *
     * @access public
     * @param  string $which Name of path index to get.
     * @return array         Array of paths.
     */
    public function getPaths($which = null)
    {
        if ($which !== null)
        {
            return get_key($which, $this->_paths);
        }

        return $this->_paths;
    }

}