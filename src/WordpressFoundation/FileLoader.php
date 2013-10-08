<?php namespace WordpressFoundation;
/**
 * WordpressFoundation Utilities
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

use Exception;
use Symfony\Component\Yaml\Yaml;

/**
 * Loads file contents.
 *
 * @package WordpressFoundation
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
    protected $paths = array();

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
            catch (Exception $e)
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
        // Load file contents and parse contents based on
        // file extension or make a call if it is a Closure.
        if ($extension instanceof \Closure)
        {
            return $extension($file, $c);
        }
        else
        {
            $extension = '.' . ltrim($extension, '.');

            if ( ! is_file($file . $extension))
            {
                // File doesn't exist so throw exception.
                throw new Exception(
                    sprintf(
                        'File "%s" does not exist.',
                        $file
                    )
                );
            }

            switch (ltrim($extension, '.'))
            {
                case 'php':
                    return include $file . $extension;

                    break;
                case 'json':
                    return json_decode(file_get_contents($file . $extension), true);

                    break;
                case 'yml':
                    return Yaml::parse($file . $extension);

                    break;
                case 'xml':
                    return simplexml_load_file($file . $extension);

                    break;
                default:
                    return file_get_contents($file . $extension);

                    break;
            }
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
            $this->loadByPaths($paths, $file, 'php');
        }

        return $this->load($paths . $file, 'php');
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
        $this->paths = $paths;

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
            return array_get($this->paths, $which);
        }

        return $this->paths;
    }

}