<?php namespace WordpressFoundation\Providers;
/**
 * WordpressFoundation Package
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Resolve controllers and call their actions.
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class ControllerServiceProvider implements ServiceProviderInterface {

    public function register(Container $app)
    {
        $app['controller'] = $app->factory(function($app, $controller)
        {
            $args = array_splice(func_get_args(), -1);

            if ($sep = strpos($controller, '@'))
            {
                $action = substr($controller, $sep+1);
                $controller = substr($controller, 0, $sep);
            }
            else
            {
                $action = 'index';
            }

            $controllerObject = new $controller($app);

            switch (count($args))
            {
                case 0:
                    return $controllerObject->$action();

                    break;
                case 1:
                    return $controllerObject->$action($args[0]);

                    break;
                case 2:
                    return $controllerObject->$action($args[0], $args[1]);

                    break;
                case 3:
                    return $controllerObject->$action($args[0], $args[1], $args[2]);

                    break;
                case 4:
                    return $controllerObject->$action($args[0], $args[1], $args[2], $args[3]);

                    break;
                default:
                    return call_user_func_array(array($controllerObject, $action), $args);

                    break;
            }
        });
    }

}