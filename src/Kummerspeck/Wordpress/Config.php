<?php namespace Kummerspeck\Wordpress;
/**
 * Kummerspeck Wordpress Utilities
 *
 * @package Kummerspeck/WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

use Kummerspeck\Arr\set_path;
use Kummerspeck\Arr\unset_path;
use Kummerspeck\Arr\get_path;

/**
 * Config class handles all interactions between loading and saving options
 * to the [wordpress options api](https://codex.wordpress.org/Options_API).
 * This class can also load option values from a config file which
 * is useful when setting default values in an options form.'
 *
 * @package Kummerspeck/WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class Config implements \ArrayAccess {

    /**
     * Holds the array.
     *
     * @access protected
     * @var array
     */
    protected $_data = array();

    /**
     * The delimiter character to use when getting or
     * setting embeded values in the array.
     *
     * @access protected
     * @var string
     */
    protected $_delimiter = '.';

    /**
     * Set the name space for all options
     * loaded by this object. A typical usage would
     * to set the namespace to a plugin name.
     *
     * @access protected
     * @var string
     */
    protected $_namespace;

    /**
     * FileLoader object.
     *
     * @access protected
     * @var FileLoader
     */
    protected $_loader;

    /**
     * Tracks which top-level config/option values have been
     * loaded.
     *
     * @access protected
     * @var array
     */
    protected $_loaded = array();

    /**
     * The file extension the config files are using.
     *
     * @access protected
     * @var string
     */
    protected $_extension = 'yml';

    /**
     * Initial the object with the path to config files and
     * optionally arguments.
     *
     * @access public
     * @param object $path      File loader object.
     * @param string $namespace Option namespace.
     * @param string $delimiter Delimiter character for array access.
     * @return void
     */
    public function __construct(FileLoader $loader, $namespace = null, $delimiter = null)
    {
        $this->setFileLoader($path);

        if ($delimiter !== null)
        {
            $this->setDelimiter();
        }

        if ($namespace !== null)
        {
            $this->setNamespace($namespace)
                ->loadNamespace();
        }
    }

    /**
     * Force a save to the options API when the
     * object is destroyed or dereferenced.
     *
     * @access public
     * @return void
     */
    public function __destruct()
    {
        $this->save();
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
            '/\s?' . preg_quote($this->getDelimiter()) . '\s?/',
            $path
        );
    }

    /**
     * Load a key/path from a config file and then from
     * the option key.
     *
     * @access public
     * @param  string $key Array index path to load.
     * @return mixed       Config/Option value.
     */
    public function load($key)
    {
        // Don't reload the value if it's been loaded before.
        if ($this->loaded($key))
        {
            return $this;
        }

        // Get the path parts.
        $parts = $this->getPathParts($key);

        // If there's a config file path set then
        // let's try and load values from it.
        if ($filePath = $this->getFilePath())
        {
            $pathParts = $parts;
            $path = '';

            // Loop through the parts until a file is found
            while ($part = array_shift($pathParts))
            {
                if (is_file($filePath . $path . $part))
                {
                    // Replace directory characters with the
                    // delimiter character for proper setting
                    // of the data array in this object.
                    $pathKey = str_replace(
                        array('/', '\\'),
                        $this->getDelimiter(),
                        $path
                    );

                    // Append the last part
                    $pathKey .= $part;

                    // Finally, set the value to the complete
                    // array index path.
                    $this[$pathKey] = $this->_loadFile(
                        $filePath . $path . $part,
                        'php'
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

        if ($optionValue)
        {
            // Overwrite any config file values with the value
            // loaded from the options api.
            $this[$parts[0]] = unserialize($optionValue);
        }

        // Track the loaded values.
        $this->_loaded[] = $this->getNamespace() . $parts[0];

        return $this;
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

        return (in_array($this->getNamespace() . $parts[0], $this->_loaded));
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
        $namespace = rtrim($this->getNamespace(), $this->getDelimiter());
        $filePath  = $this->getFilePath();

        if ( ! $path && is_file($filePath . $namespace . '.' . $this->_extension))
        {
            // Set data array with the namespace config file name
            $this->_data = $this->_loadFile(
                $filePath . $namespace . $path,
                $this->_extension
            );
        }
        elseif (is_dir($filePath . $namespace . $path))
        {
            // Iterate files in the namespace folder.
            $dir = new DirectoryIterator($filePath . $namespace . $path);

            foreach ($dir as $file)
            {
                $filename = $file->getFilename();

                if ($filename[0] === '.' OR $filename[strlen($filename)-1] === '~')
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
                elseif ($file->getExtension() === $this->_extension)
                {
                    // Found a config file so load it's contents
                    $data    = $this->_loadFile(
                        $filePath . $namespace . $path . $filename,
                        $this->_extension
                    );
                    
                    $pathKey = $this->getNamespace();

                    if ($path)
                    {
                        // If there is a relative path then set the specific
                        // index path based on the relative path.
                        $pathKey .= str_replace(
                            DIRECTORY_SEPARATOR,
                            $this->getDelimiter(),
                            $path
                        );

                        rtrim($pathKey, $this->getDelimiter());
                    }

                    $this[$pathKey] = $data;
                }
            }
        }
    }

    /**
     * Load a config file's content.
     *
     * @access private
     * @param  string $file      Complete path to the file name.
     * @param  string $extension File extension.
     * @return array             The file should return an associative array.
     */
    private function _loadFile($file, $extension)
    {
        return $this->_loader->load($file, $extension);
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
        foreach ($this->_data as $key => $value)
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
        $this->_namespace = rtrim(
            $namespace,
            $this->getDelimiter() . ' '
        );

        // Append delimiter
        $this->_namespace .= $this->getDelimiter();

        return $this;
    }

    /**
     * Get the namespace.
     *
     * @access public
     * @return string Object namespace
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * Set the delimiter character.
     *
     * @access public
     * @param string $delimiter The delimiter character.
     * @return $this
     */
    public function setDelimiter($delimiter)
    {
        $this->_delimiter = $delimiter;

        return $this;
    }

    /**
     * Get the delimiter character.
     *
     * @access public
     * @return string Delimiter character.
     */
    public function getDelimiter()
    {
        return $this->_delimiter;
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
        $this->_extension = $extension;

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
        return $this->_extension;
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
        $this->_loader = $loader;

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
        return $this->_loader;
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
        set_path($this->_data, $key, $value, $this->getDelimiter());
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
        return get_path($key, $this->_path, null, $this->getDelimiter());
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
        unset_path($this->_data, $key, $this->getDelimiter());
    }

    /**
     * Implements the ArrayAccess method.
     *
     * @access public
     * @param  string $key Index path.
     * @return bool        True is Index path found else false.
     */
    public function offsetExists($key)
    {
        return (
            get_path(
                $key,
                $this->_path,
                null,
                $this->getDelimiter()
            ) !== null
        );
    }

}