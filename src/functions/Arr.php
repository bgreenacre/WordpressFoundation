<?php namespace Kummerspeck\Arr;
/**
 * Kummerspeck Arr function file.
 *
 * @package Kummerspeck/WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

/**
 * Get a specific index from array. Optional return a
 * default value when key not found.
 *
 * @param  string $key     Array index to get.
 * @param  array  $array   Array to search in.
 * @param  mixed  $default Default value to return when key is not found.
 * @return mixed           Contents of array index.
 */
function get_key($key, array $array, $default = null)
{
    return (isset($array[$key])) ? $array[$key] : $default;
}

/**
 * Get a index path from array.
 *
 * @param  string $path      Path to search for.
 * @param  array  $array     Array to search in.
 * @param  mixed  $default   Value to return if index path not found.
 * @param  string $delimiter Delimiter character.
 * @return mixed             Array index path contents.
 */
function get_path($path, array $array = null, $default = null, $delimiter = '.')
{
    if (array_key_exists($path, $array))
    {
        // No need to do extra processing
        return $array[$path];
    }

    // Eliminate any spaces between delimiters.
    $path = preg_replace('/\s?' . preg_quote($delimiter) . '\s?/', $delimiter, $path);

    // Split the keys by delimiter
    $keys = explode($delimiter, $path);

    do
    {
        $key = array_shift($keys);

        if (ctype_digit($key))
        {
            // Make the key an integer
            $key = (int) $key;
        }

        if (isset($array[$key]))
        {
            if ($keys)
            {
                if (is_array($array[$key]))
                {
                    // Dig down into the next part of the path
                    $array = $array[$key];
                }
                else
                {
                    // Unable to dig deeper
                    break;
                }
            }
            else
            {
                // Found the path requested
                return $array[$key];
            }
        }
        else
        {
            // Unable to dig deeper
            break;
        }
    }
    while ($keys);

    // Unable to find the value requested
    return $default;
}

/**
 * Set an index path in an array.
 * 
 * @param array  $array     Array to set index path value to.
 * @param string $path      Index path to set.
 * @param mixed  $value     Value to set.
 * @param string $delimiter Delimiter character
 * @return void
 */
function set_path( & $array, $path, $value, $delimiter = '.')
{
    // Split the keys by delimiter
    $keys = explode($delimiter, $path);

    // Set current $array to inner-most array path
    while (count($keys) > 1)
    {
        $key = array_shift($keys);

        if (ctype_digit($key))
        {
            // Make the key an integer
            $key = (int) $key;
        }

        if ( ! isset($array[$key]))
        {
            $array[$key] = array();
        }

        $array =& $array[$key];
    }

    // Set key on inner-most array
    $array[array_shift($keys)] = $value;
}

/**
 * Unset a index path from array.
 *
 * @param  array  $array     Array to unset index path from.
 * @param  string $path      Index path to unset.
 * @param  string $delimiter Delimiter character.
 * @return void
 */
function unset_path( & $array, $path, $delimiter = '.')
{
    // Split the keys by delimiter
    $keys = explode($delimiter, $path);

    // Set current $array to inner-most array path
    while (count($keys) > 1)
    {
        $key = array_shift($keys);

        if (ctype_digit($key))
        {
            // Make the key an integer
            $key = (int) $key;
        }

        if (isset($array[$key]))
        {
            $array =& $array[$key];
        }
    }

    // Set key on inner-most array
    unset($array[array_shift($keys)]);
}