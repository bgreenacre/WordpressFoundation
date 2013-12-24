<?php namespace WpQueue;

use ArrayAccess;
use DirectoryIterator;

/**
 * Config class handles all interactions between loading and saving options
 * to the [wordpress options api](https://codex.wordpress.org/Options_API).
 * This class can also load option values from a config file which
 * is useful when setting default values in an options form.'
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class Config implements ArrayAccess {

    /**
     * Holds the array.
     *
     * @access protected
     * @var array
     */
    protected $data = array();

    /**
     * Contains wordpress option values in the
     * global scope.
     *
     * @access protected
     * @var array
     */
    protected $options = array();

    /**
     * Set the name space for all options
     * loaded by this object. A typical usage would
     * to set the namespace to a plugin name.
     *
     * @access protected
     * @var string
     */
    protected $namespace;

    /**
     * FileLoader object.
     *
     * @access protected
     * @var FileLoader
     */
    protected $loader;

    /**
     * Tracks which top-level config/option values have been
     * loaded.
     *
     * @access protected
     * @var array
     */
    protected $loaded = array();

    /**
     * The file extension the config files are using.
     *
     * @access protected
     * @var string
     */
    protected $extension = 'php';

    /**
     * Tracks the key that was loaded by the load
     * method.
     *
     * @access protected
     * @var string
     */
    protected $lastKeyLoaded;

    /**
     * Initial the object with the path to config files and
     * optionally arguments.
     *
     * @access public
     * @param Pimple $container Plugin container object.
     * @param object $loader    File loader object.
     * @param string $namespace Option namespace.
     * @return void
     */
    public function __construct(FileLoader $loader, $namespace = null)
    {
        $this->setFileLoader($loader);

        if ($namespace !== null)
        {
            $this->setNamespace($namespace)
                ->loadNamespace();
        }
    }

    /**
     * getPathParts - Splits a string by the delimiter character.
     *
     * @access public
     * @param  string $path Array index path
     * @return array        Array of strings
     */
    public function getPathParts($path)
    {
        return preg_split(
            '/\s?\.{1}\s?/',
            $path
        );
    }

    /**
     * Load a key/path from a config file and then from
     * the option key.
     *
     * @access public
     * @param  string $key Array index path to load.
     * @return $this
     */
    public function load($key)
    {
        // Track this key was loaded last
        $this->lastKeyLoaded = $key;

        // Don't reload the value if it's been loaded before.
        if ($this->loaded($key))
        {
            return $this;
        }

        // Get the path parts.
        $parts = $this->getPathParts($key);

        // If there's a config file path set then
        // let's try and load values from it.
        if (($filePath = $this->getFileLoader()->getPaths('config')) && ! $this->loaded($parts[0]))
        {
            $pathParts = $parts;
            $path      = '';

            // Loop through the parts until a file is found
            while ($part = array_shift($pathParts))
            {
                if (is_file($filePath . $path . $part . '.' . $this->extension))
                {
                    // Replace directory characters with the
                    // delimiter character for proper setting
                    // of the data array in this object.
                    $pathKey = str_replace(array('/', '\\'), '.', $path);

                    // Append the last part
                    $pathKey .= $part;

                    // Finally, set the value to the complete
                    // array index path.
                    $this[$pathKey] = $this->loadFile(
                        $filePath . $path . $part,
                        $this->extension
                    );
                }
                elseif (is_dir($filePath . $path . $part))
                {
                    // If this is a directory then append
                    // the dirname to the path variable
                    // which will be used in the next
                    // iteration to file a config file
                    $path .= $part . DIRECTORY_SEPARATOR;
                }
            }
        }

        // Let's lookup an option value that might be saved
        // to the top-level path parts.
        $optionValue = get_option($this->getNamespace() . $parts[0]);

        if ($optionValue !== false)
        {
            // Overwrite any config file values with the value
            // loaded from the options api.
            $this[$parts[0]] = unserialize($optionValue);
            
            // Track the loaded values.
            $this->loaded[] = $this->getNamespace() . $parts[0];

            return $this;
        }
        elseif ( ! $this->getNamespace())
        {
            $optionValue = get_option($parts[0]);

            if ($optionValue !== false)
            {
                $this->options[$parts[0]] = unserialize($optionValue);

                $this->loaded[] = $parts[0];
            }

            return $this;
        }
        else
        {
            return $this;
        }
    }

    /**
     * Check to see if a array index path has been
     * loaded already by this object.
     *
     * @access public
     * @param  string $key Array index path
     * @return bool        True if the $key has been loaded else false.
     */
    public function loaded($key)
    {
        $parts = $this->getPathParts($key);

        return (
            isset($parts[0]) &&
            in_array(
                $this->getNamespace() . $parts[0],
                $this->loaded
            )
        );
    }

    /**
     * Load config file or folder of the set namespace if any exists.
     *
     * @access public
     * @param  string $path Relative path.
     * @return $this
     */
    public function loadNamespace($path = '')
    {
        $namespace = rtrim($this->getNamespace(), '.');
        $filePath  = $this->getFileLoader()->getPaths('config');

        if ( ! $path && is_file($filePath . $namespace . '.' . $this->extension))
        {
            // Set data array with the namespace config file name
            $this->data = $this->loadFile(
                $filePath . $namespace . $path,
                $this->extension
            );
        }
        elseif (is_dir($filePath . $namespace . $path))
        {
            // Iterate files in the namespace folder.
            $dir = new DirectoryIterator($filePath . $namespace . $path);

            foreach ($dir as $file)
            {
                $filename = $file->getFilename();

                if ($filename[0] === '.' || $filename[strlen($filename)-1] === '~')
                {
                    // Skip all hidden files and UNIX backup files
                    continue;
                }
                elseif ($file->isDir())
                {
                    // Move into the directory and load all files.
                    if ( ! $path)
                    {
                        $path .= DIRECTORY_SEPARATOR;
                    }

                    $this->loadNamespace($path . $filename . DIRECTORY_SEPARATOR);
                }
                elseif ($file->getExtension() === $this->extension)
                {
                    // Found a config file so load it's contents
                    $data = $this->loadFile(
                        $filePath . $namespace . $path . $filename,
                        $this->extension
                    );
                    
                    $pathKey = $this->getNamespace();

                    if ($path)
                    {
                        // If there is a relative path then set the specific
                        // index path based on the relative path.
                        $pathKey .= str_replace(
                            DIRECTORY_SEPARATOR,
                            '.',
                            DIRECTORY_SEPARATOR . trim($path, DIRECTORY_SEPARATOR)
                        );

                        rtrim($pathKey, '.');
                    }

                    $this[$pathKey] = $data;
                }
            }
        }

        return $this;
    }

    /**
     * Load a config file's content.
     *
     * @access private
     * @param  string $file      Complete path to the file name.
     * @param  string $extension File extension.
     * @return array             The file should return an associative array.
     */
    private function loadFile($file, $extension)
    {
        return $this->loader->load($file, $extension);
    }

    /**
     * Saves the entire data array of this object into
     * the Wordpress Options API.
     *
     * @access public
     * @return $this
     */
    public function save()
    {
        // Iterate through the data array and save each index
        // as a field in the options api.
        foreach ($this->data as $key => $value)
        {
            update_option(
                $this->getNamespace() . $key,
                serialize($value)
            );
        }

        return $this;
    }

    /**
     * Set the namespace for the options loaded by this
     * object.
     *
     * @access public
     * @param string $namespace Object namespace.
     * @return $this
     */
    public function setNamespace($namespace)
    {
        // Clean up the string, make sure there's no trailing
        // delimiter character since we're going to re-append it.
        $this->namespace = rtrim(
            $namespace,
            '. '
        );

        // Append delimiter
        $this->namespace .= '.';

        return $this;
    }

    /**
     * Get the namespace.
     *
     * @access public
     * @param  bool   $withDelimiter True returns with delimiter appended else no delimiter.
     * @return string Object namespace
     */
    public function getNamespace($withDelimiter = true)
    {
        return ($withDelimiter === true)
            ? $this->namespace
            : trim($this->namespace, '.');
    }

    /**
     * Set the file extension.
     *
     * @access public
     * @param string $extension The file extension.
     * @return $this
     */
    public function setFileExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Get the file extension.
     *
     * @access public
     * @return string File extension.
     */
    public function getFileExtension()
    {
        return $this->extension;
    }

    /**
     * Set file path where config files are located.
     *
     * @access public
     * @param  FileLoader
     * @return $this
     */
    public function setFileLoader(FileLoader $loader)
    {
        $this->loader = $loader;

        return $this;
    }

    /**
     * Get the file loader.
     *
     * @access public
     * @return FileLoader.
     */
    public function getFileLoader()
    {
        return $this->loader;
    }

    /**
     * Return the entire data or a key.
     *
     * @access public
     * @return array  Array contents
     */
    public function asArray($key = null)
    {
        if ($key === null)
        {
            $key = $this->lastKeyLoaded;
        }

        return ($key === true)
            ? (array) $this->data
            : (array) $this[$key];
    }

    /**
     * Implements the ArrayAccess method.
     *
     * @access public
     * @param  string $key   Index path.
     * @param  mixed  $value Index path's value.
     * @return void
     */
    public function offsetSet($key, $value)
    {
        array_set($this->data, $key, $value);
    }

    /**
     * Implements the ArrayAccess method.
     *
     * @access public
     * @param  string $key Index path.
     * @return mixed       Contents of the Index path in the array.
     */
    public function offsetGet($key)
    {
        if ( ! $this->loaded($key))
        {
            $this->load($key);
        }

        return array_get(
            $this->data,
            $key,
            array_get($this->options, $key, null)
        );
    }

    /**
     * Implements the ArrayAccess method.
     *
     * @access public
     * @param  string $key Index path.
     * @return void
     */
    public function offsetUnset($key)
    {
        array_forget($this->data, $key);
    }

    /**
     * Implements the ArrayAccess method.
     *
     * @access public
     * @param  string $key Index path.
     * @return bool        True if Index path found else false.
     */
    public function offsetExists($key)
    {
        $exists = array_get(
            $this->data,
            $key,
            array_get($this->options, $key, false)
        );

        return ($exists !== false) ? true : false;
    }

}