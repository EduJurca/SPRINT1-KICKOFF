<?php
/**
 * 🔀 Router - Sistema de rutes de l'aplicació
 * Gestiona el matching i dispatch de rutes cap als controladors
 */

class Router {
    private static $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];
    
    /**
     * Registrar una ruta GET
     * 
     * @param string $uri URI de la ruta
     * @param callable|array $action Acció a executar (funció o [Controller, method])
     */
    public static function get($uri, $action) {
        self::$routes['GET'][$uri] = $action;
    }
    
    /**
     * Registrar una ruta POST
     * 
     * @param string $uri URI de la ruta
     * @param callable|array $action Acció a executar
     */
    public static function post($uri, $action) {
        self::$routes['POST'][$uri] = $action;
    }
    
    /**
     * Registrar una ruta PUT
     * 
     * @param string $uri URI de la ruta
     * @param callable|array $action Acció a executar
     */
    public static function put($uri, $action) {
        self::$routes['PUT'][$uri] = $action;
    }
    
    /**
     * Registrar una ruta DELETE
     * 
     * @param string $uri URI de la ruta
     * @param callable|array $action Acció a executar
     */
    public static function delete($uri, $action) {
        self::$routes['DELETE'][$uri] = $action;
    }
    
    /**
     * Dispatch - Executar la ruta corresponent
     * 
     * @param string $uri URI sol·licitada
     * @param string $method Mètode HTTP
     */
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
        // Escapar barres
        $pattern = str_replace('/', '\/', $route);
        
        // Convertir {param} a ([^\/]+)
        $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([^\/]+)', $pattern);
        
        return '/^' . $pattern . '$/';
    }
    
    /**
     * Executar l'acció d'una ruta
     * 
     * @param callable|array $action Acció a executar
     * @param array $params Paràmetres de la ruta
     */
    private static function executeAction($action, $params = []) {
        // Si és un array [Controller, method]
        if (is_array($action)) {
            list($controller, $method) = $action;
            
            // Si el controlador és una string, instanciar-lo
            if (is_string($controller)) {
                // Carregar el fitxer del controlador si és necessari
                // Buscar en múltiples subdirectoris
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
        
        // Si existeix una vista personalitzada de 404
        $notFoundView = VIEWS_PATH . '/errors/404.php';
        if (file_exists($notFoundView)) {
            require_once $notFoundView;
        } else {
            // Vista 404 per defecte
            echo '<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Pàgina no trobada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .container {
            text-align: center;
        }
        h1 {
            font-size: 120px;
            margin: 0;
        }
        p {
            font-size: 24px;
        }
        a {
            color: white;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>404</h1>
        <p>Pàgina no trobada</p>
        <a href="/">Tornar a l\'inici</a>
    </div>
</body>
</html>';
        }
        exit;
    }
    
    /**
     * Redirigir a una URL
     * 
     * @param string $url URL de destí
     */
    public static function redirect($url) {
        header("Location: $url");
        exit;
    }
    
    /**
     * Renderitzar una vista
     * 
     * @param string $view Nom de la vista (sense extensió)
     * @param array $data Dades a passar a la vista
     */
    public static function view($view, $data = []) {
        // Extreure dades perquè siguin accessibles com a variables
        extract($data);
        
        // Construir path de la vista
        $viewPath = VIEWS_PATH . '/' . str_replace('.', '/', $view);
        
        // Intentar trobar el fitxer amb diferents extensions (prioritat a .php)
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
    
    /**
     * Retornar resposta JSON
     * 
     * @param mixed $data Dades a retornar
     * @param int $statusCode Codi d'estat HTTP
     */
    public static function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
