<?php

namespace app\core;

class FrontController {
    public $pdo;
	private $route, $routeName, $model, $controller, $view, $twig, $path_to_tmp;
	
	public function __construct(Router $router, $routeName, $action = null) {
		$this->pdo = new \PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);
		
		//Fetch a route based on a name, e.g. "search" or "list" or "edit"
		$this->route = $router->getRoute($routeName);
		$this->routeName = $routeName;
		
		//Fetch the names of each component from the router
		$modelName = "\app\model\\".$this->route->model;
		$controllerName = "\app\controller\\".$this->route->controller;
		$viewName = "\app\\view\\".$this->route->view;

		//Instantiate each component
		$this->model = new $modelName($this->pdo);
		$this->controller = new $controllerName($this->model);
		$this->view = new $viewName($this->model);
		
		//Run the controller action
		if(!empty($action) && method_exists($this->controller, $action)) $this->controller->{$action}();
                
                $this->path_to_tmp = "/site/themes/".TPL_DEFAULT."/templates/";
                
                // load TWIG
                $loader = new \Twig_Loader_Filesystem(realpath(__DIR__ .DS.'..'.DS.'..') . $this->path_to_tmp); // *!* replace TPL_DEFAULT with $theme
                $this->twig = new \Twig_Environment($loader, array(
                    'cache' => realpath(__DIR__ .DS.'..'.DS.'..') . "/cache/compilation/",
                ));
                
                $twf_url = new \Twig_Function('url', function($url) {
                    if(!is_string($url) && $url === true) return "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                    else {
                       $url = str_replace('theme://', dirname($_SERVER['REQUEST_URI']) . $this->path_to_tmp, $url);
                    }
                    
                    return $url;
                });
                $this->twig->addFunction($twf_url);
                
                
	}
	
	public function getRouteName() {
		return $this->routeName;
	}
	
	public function getModel() {
		return $this->model;
	}
	public function getController() {
		return $this->controller;
	}
	public function getView() {
		return $this->view;
	}
	
        public function output() {
                $nav = $this->view->output($this->routeName, "nav");
		//var_dump($nav); // *!* test
		
		$page = $this->view->output($this->routeName);
                $title = $page["title"];
		$content = $page["content"];
                
                $content_array = array(
                    'config' => array(
                        'site' => array(
                            'title' => $title
                        )
                    ),
                    'page' => array(
                        'content' => $content,
                        'footer' => 'This is the footer'
                    )
                );
                
                $return = $this->twig->render('content.html.twig', $content_array);
                
		return $return;
	}
}