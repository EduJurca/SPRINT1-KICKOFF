<?php

class Router {
    private static $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];
    
    public static function get($uri, $action) {
        self::$routes['GET'][$uri] = $action;
    }
    
    public static function post($uri, $action) {
        self::$routes['POST'][$uri] = $action;
    }
    
    public static function put($uri, $action) {
        self::$routes['PUT'][$uri] = $action;
    }
    
    public static function delete($uri, $action) {
        self::$routes['DELETE'][$uri] = $action;
    }
    
    public static function dispatch($uri, $method = 'GET') {
        // Eliminar prefijo de idioma de la URI (/en/... o /ca/...)
        foreach (['en', 'ca'] as $lang) {
            if (strpos($uri, '/' . $lang . '/') === 0) {
                $uri = substr($uri, strlen('/' . $lang));
                break;
            } elseif ($uri === '/' . $lang) {
                $uri = '/';
                break;
            }
        }
        
        // Normalitzar URI (eliminar barra final excepte per '/')
        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }
        
        // Buscar ruta exacta
        if (isset(self::$routes[$method][$uri])) {
            $action = self::$routes[$method][$uri];
            return self::executeAction($action);
        }
        
        // Buscar ruta amb paràmetres dinàmics
        foreach (self::$routes[$method] as $route => $action) {
            $pattern = self::convertRouteToRegex($route);
            
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Eliminar primer match (URI completa)
                return self::executeAction($action, $matches);
            }
        }
        
        // Si no es troba cap ruta, retornar 404
        self::notFound();
    }
    
    /**
     * Convertir ruta amb paràmetres a expressió regular
     * Exemple: /users/{id} -> /^\/users\/([^\/]+)$/
     * 
     * @param string $route Ruta amb paràmetres
     * @return string Expressió regular
     */
    private static function convertRouteToRegex($route) {
        $pattern = str_replace('/', '\/', $route);
        $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([^\/]+)', $pattern);
        return '/^' . $pattern . '$/';
    }
    
    private static function executeAction($action, $params = []) {
        if (is_array($action)) {
            list($controller, $method) = $action;
            
            if (is_string($controller)) {
                $controllerPaths = [
                    CONTROLLERS_PATH . '/auth/' . $controller . '.php',
                    CONTROLLERS_PATH . '/public/' . $controller . '.php',
                    CONTROLLERS_PATH . '/' . $controller . '.php'
                ];
                
                foreach ($controllerPaths as $controllerFile) {
                    if (file_exists($controllerFile)) {
                        require_once $controllerFile;
                        break;
                    }
                }
                
                // Instanciar controlador
                if (class_exists($controller)) {
                    $controller = new $controller();
                } else {
                    die("Controller class '$controller' not found");
                }
            }
            
            // Executar mètode del controlador
            if (method_exists($controller, $method)) {
                return call_user_func_array([$controller, $method], $params);
            } else {
                die("Method '$method' not found in controller");
            }
        }
        
        // Si és una funció anònima
        if (is_callable($action)) {
            return call_user_func_array($action, $params);
        }
        
        die("Invalid route action");
    }
    
    /**
     * Pàgina 404 - No trobat
     */
    private static function notFound() {
        http_response_code(404);
        
        $notFoundView = VIEWS_PATH . '/errors/404.php';
        if (file_exists($notFoundView)) {
            require_once $notFoundView;
        } else {
            // Fallback simple si no existeix la vista
            echo '<h1>404 - Pàgina no trobada</h1><a href="/">Tornar a l\'inici</a>';
        }
        exit;
    }
    
    public static function redirect($url) {
        header("Location: $url");
        exit;
    }
    
    public static function view($view, $data = []) {
        // `Authorization` is expected to be loaded centrally at bootstrap (index.php)
        if (!isset($data['auth'])) {
            $data['auth'] = Authorization::getAuthInfo();
        }
        
        extract($data);
        
        $viewPath = VIEWS_PATH . '/' . str_replace('.', '/', $view);
        $extensions = ['.php', '.phtml', '.html'];
        $foundPath = null;
        
        foreach ($extensions as $ext) {
            $testPath = $viewPath . $ext;
            if (file_exists($testPath)) {
                $foundPath = $testPath;
                break;
            }
        }
        
        if ($foundPath) {
            require $foundPath;
        } else {
            die("View '$view' not found. Tried: $viewPath.php, $viewPath.phtml, $viewPath.html");
        }
    }
    
    public static function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
