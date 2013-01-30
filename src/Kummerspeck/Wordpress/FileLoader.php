<?php namespace Kummerspeck\Wordpress;
/**
 * Kummerspeck Wordpress Utilities
 *
 * @package Kummerspeck/WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

use Symfony\Component\Yaml\Yaml;

class FileLoader {

    protected $_paths = array();

    public function __construct(array $paths)
    {
        $this->setPaths($paths);
    }

    public function loadByPaths(array $paths, $file, $extension)
    {
        $pathsCount = count($paths);

        for ($i = 0; $i < $pathsCount; ++$i)
        {
            try
            {
                return $this->load($paths[$i] . $file, $extension);
            }
            catch (Exception $e)
            {
                if ($i == ($pathsCount - 1) )
                {
                    throw $e;
                }
            }
        }
    }

    public function load($file, $extension)
    {
        $extension = '.' . $extension;

        if ( ! is_file($file . $extension))
        {
            $c = $this->getContainer();

            throw new \Exception(
                sprintf(
                    'File "%s" does not exist in plugin "%s".',
                    str_replace($c['paths.plugin'], '', $file) . $extension,
                    $c['plugin.name']
                )
            );
        }

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

    public function config($file)
    {
        $paths = $this->getPaths('config');

        if (is_array($paths))
        {
            $this->loadByPaths($paths, $file, 'yml');
        }

        return $this->load($paths . $file, 'yml');
    }

    public function resource($file, $extension)
    {
        $paths = $this->getPaths('resources');

        if (is_array($paths))
        {
            $this->loadByPaths($paths, $file, $extension);
        }

        return $this->load($paths . $file, $extension);
    }

    public function setPaths(array $paths)
    {
        $this->_paths = $paths;

        return $this;
    }

    public function getPaths($which = null)
    {
        if ($which !== null)
        {
            return \Seeder\Arr\get_key($which, $this->_paths);
        }

        return $this->_paths;
    }

}