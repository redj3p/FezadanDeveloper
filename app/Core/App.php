<?php
class App {
    protected $controller = 'HomeController';
    protected $method = 'index';
    protected $params = [];
    public static $lang = 'TR';

    public static function getLang(): string {
        return self::$lang;
    }

    public function __construct() {
        // Global check to reject method override headers
        if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) || 
            isset($_SERVER['HTTP_X_HTTP_METHOD']) || 
            isset($_SERVER['HTTP_X_METHOD_OVERRIDE'])) {
            http_response_code(400);
            echo 'Bad Request: Method override headers are not allowed.';
            exit;
        }

        $host = $_SERVER['HTTP_HOST'] ?? '';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $parts = explode('?', $requestUri, 2);
        $rawPath = $parts[0];
        $queryString = isset($parts[1]) ? '?' . $parts[1] : '';

        // Reject path traversals and encoded slashes
        $rawUriLower = strtolower($requestUri);
        if (strpos($rawUriLower, '%2e%2e') !== false || 
            strpos($rawUriLower, '%2e.') !== false || 
            strpos($rawUriLower, '.%2e') !== false || 
            strpos($rawUriLower, '..') !== false) {
            http_response_code(400);
            echo 'Bad Request: Path traversal detected.';
            exit;
        }

        if (strpos($rawUriLower, '%2f') !== false) {
            http_response_code(400);
            echo 'Bad Request: Encoded slashes are not allowed.';
            exit;
        }

        // Canonicalize path: decode, replace double slashes, strip trailing slash
        $canonicalPath = rawurldecode($rawPath);
        
        // Check decoded path segments for . and ..
        $segments = explode('/', $canonicalPath);
        foreach ($segments as $segment) {
            if ($segment === '.' || $segment === '..') {
                http_response_code(400);
                echo 'Bad Request: Path traversal detected.';
                exit;
            }
        }

        $canonicalPath = preg_replace('#/{2,}#', '/', $canonicalPath);
        if ($canonicalPath !== '/' && substr($canonicalPath, -1) === '/') {
            $canonicalPath = rtrim($canonicalPath, '/');
        }

        // Perform 301 Redirect if raw path is not canonical
        if ($rawPath !== $canonicalPath) {
            http_response_code(301);
            header('Location: ' . $canonicalPath . $queryString);
            exit;
        }

        // Block fallback admin paths
        $pathLower = strtolower($canonicalPath);
        if (in_array($pathLower, ['/admin', '/panel', '/dashboard']) ||
            strpos($pathLower, '/admin/') === 0 ||
            strpos($pathLower, '/panel/') === 0 ||
            strpos($pathLower, '/dashboard/') === 0) {
            $this->render404();
        }

        $url = $this->parseUrl();

        // Language context detection
        $hasLangPrefix = false;
        if (isset($url[0]) && strtolower($url[0]) === 'en') {
            self::$lang = 'EN';
            array_shift($url);
            $hasLangPrefix = true;
            if (empty($url)) {
                $url = ['home'];
            }
        } elseif (isset($url[0]) && strtolower($url[0]) === 'tr') {
            self::$lang = 'TR';
            array_shift($url);
            $hasLangPrefix = true;
            if (empty($url)) {
                $url = ['home'];
            }
        }

        // Redirect main site pages to prefixed versions if missing
        if (!$hasLangPrefix && strpos($host, 'notlar.') !== 0) {
            $pathLower = strtolower($canonicalPath);
            $excludePrefixes = ['/yonetim', '/uploads', '/cdn', '/assets', '/scripts', '/admin', '/panel', '/dashboard', '/furkan'];
            $isExcluded = false;
            foreach ($excludePrefixes as $prefix) {
                if ($pathLower === $prefix || strpos($pathLower, $prefix . '/') === 0) {
                    $isExcluded = true;
                    break;
                }
            }
            if (!$isExcluded) {
                $redirectUrl = ($canonicalPath === '/') ? '/tr' . $queryString : '/tr' . $canonicalPath . $queryString;
                http_response_code(301);
                header('Location: ' . $redirectUrl);
                exit;
            }
        }

        // Furkan portfolio subdomain routing (furkan.fezadan.org)
        if (strpos($host, 'furkan.') === 0) {
            require_once ROOT . '/app/Controllers/FurkanController.php';
            $controller = new FurkanController();

            // /yonetim or /admin -> admin panel
            if (isset($url[0]) && ($url[0] === 'yonetim' || $url[0] === 'admin')) {
                $method = 'yonetim';
                call_user_func([$controller, $method]);
            } elseif (isset($url[0]) && $url[0] === 'furkan' && isset($url[1]) && ($url[1] === 'yonetim' || $url[1] === 'admin')) {
                call_user_func([$controller, 'yonetim']);
            } elseif (isset($url[0]) && $url[0] === 'furkan' && isset($url[1]) && $url[1] === 'store') {
                call_user_func([$controller, 'store']);
            } elseif (isset($url[0]) && $url[0] === 'furkan' && isset($url[1]) && $url[1] === 'delete') {
                call_user_func([$controller, 'delete']);
            } elseif (isset($url[0]) && $url[0] === 'furkan' && isset($url[1]) && $url[1] === 'reorder') {
                call_user_func([$controller, 'reorder']);
            } elseif (isset($url[0]) && $url[0] === 'store') {
                call_user_func([$controller, 'store']);
            } elseif (isset($url[0]) && $url[0] === 'delete') {
                call_user_func([$controller, 'delete']);
            } elseif (isset($url[0]) && $url[0] === 'reorder') {
                call_user_func([$controller, 'reorder']);
            } else {
                call_user_func([$controller, 'index']);
            }
            return;
        }

        // Notlar subdomain routing.
        if (strpos($host, 'notlar.') === 0) {
            $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            if ($requestMethod !== 'GET' && $requestMethod !== 'HEAD') {
                http_response_code(405);
                header('Allow: GET, HEAD');
                echo 'Method Not Allowed';
                exit;
            }

            require_once ROOT . '/app/Controllers/NotlarController.php';
            $this->controller = new NotlarController();

            if (isset($url[0]) && $url[0] === 'not') {
                if (isset($url[1]) && $url[1] === 'download') {
                    $this->method = 'download';
                    $this->params = isset($url[2]) ? [$url[2]] : [];
                } elseif (isset($url[1]) && $url[1] === 'view') {
                    $this->method = 'viewPdf';
                    $this->params = isset($url[2]) ? [$url[2]] : [];
                } else {
                    $this->method = 'read';
                    $this->params = isset($url[1]) ? [$url[1]] : [];
                }
            } else {
                $this->method = 'index';
                $this->params = [];
            }

            call_user_func_array([$this->controller, $this->method], $this->params);
            return;
        }

        // Main domain Furkan routes redirect to furkan subdomain
        if (isset($url[0]) && strtolower($url[0]) === 'furkan') {
            $redirectPath = '/';
            if (isset($url[1]) && $url[1] !== '') {
                $redirectPath = '/' . $url[1];
                if (isset($url[2])) {
                    $remaining = array_slice($url, 2);
                    if (!empty($remaining)) {
                        $redirectPath .= '/' . implode('/', $remaining);
                    }
                }
            }

            http_response_code(301);
            header('Location: https://furkan.fezadan.org' . $redirectPath . $queryString);
            exit;
        }

        $firstSegment = isset($url[0]) ? $url[0] : '';
        $controllerFile = ROOT . '/app/Controllers/' . ucfirst($firstSegment) . 'Controller.php';

        if (file_exists($controllerFile)) {
            $this->controller = ucfirst($firstSegment) . 'Controller';
            unset($url[0]);
        } elseif ($firstSegment !== '') {
            // Check if it is an author slug
            require_once ROOT . '/app/Core/Db.php';
            try {
                $pdo = Db::pdo();
                $stmt = $pdo->prepare("SELECT id FROM authors WHERE slug = ? LIMIT 1");
                $stmt->execute([$firstSegment]);
                $authorExists = $stmt->fetchColumn();
            } catch (\Exception $e) {
                $authorExists = false;
            }

            if ($authorExists) {
                // If there are no further segments, perform a 301 redirect to the author page
                if (!isset($url[1]) || $url[1] === '') {
                    $redirectPath = langUrl('/yazar/' . $firstSegment);
                    http_response_code(301);
                    header('Location: ' . $redirectPath);
                    exit;
                } else {
                    // Route to MakaleController::index($articleSlug, $authorSlug)
                    $this->controller = 'MakaleController';
                    $this->method = 'index';
                    $this->params = [$url[1], $firstSegment];
                    
                    require_once ROOT . '/app/Controllers/MakaleController.php';
                    $this->controller = new MakaleController();
                    call_user_func_array([$this->controller, $this->method], $this->params);
                    return;
                }
            } else {
                $this->render404();
            }
        }

        require_once ROOT . '/app/Controllers/' . $this->controller . '.php';

        $this->controller = new $this->controller;

        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            } else {
                $camelCaseMethod = lcfirst(str_replace('-', '', ucwords($url[1], '-')));

                if (method_exists($this->controller, $camelCaseMethod)) {
                    $this->method = $camelCaseMethod;
                    unset($url[1]);
                }
            }
        }

        $this->params = $url ? array_values($url) : [];
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    public function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return ['home'];
    }

    private function render404(): void {
        http_response_code(404);
        header('Content-Type: text/html; charset=utf-8');
        $view = ROOT . '/app/Views/errors/404.php';
        if (is_file($view)) {
            require $view;
        } else {
            echo 'Sayfa bulunamadı.';
        }
        exit;
    }
}
