<?php

namespace TobiasMarty;

class FrontController {
    public $pdo;
	private $route, $model, $controller, $view;
	
	public function __construct(Router $router, $routeName, $action = null) {
		$this->pdo = new \PDO('mysql:host=localhost;dbname=tobiasmarty', 'root', '');
		
		//Fetch a route based on a name, e.g. "search" or "list" or "edit"
		$this->route = $router->getRoute($routeName);

		//Fetch the names of each component from the router
		$modelName = "\TobiasMarty\models\\".$this->route->model;
		$controllerName = "\TobiasMarty\controllers\\".$this->route->controller;
		$viewName = "\TobiasMarty\\views\\".$this->route->view;

		//Instantiate each component
		$this->model = new $modelName($this->pdo);
		$this->controller = new $controllerName($this->model);
		$this->view = new $viewName($this->model);
		
		//Run the controller action
		if(!empty($action) && method_exists($this->controller, $action)) $this->controller->{$action}();
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
		//Finally a method for outputting the data from the view 
		//This allows for some consistent layout generation code such as a page header/footer
		$header = '<h1>Hello world example</h1>';
		return $header . '<div>' . $this->view->output() . '</div>';
	}
}

class Router {
    private $table = array();
	
    public function __construct() {
		$this->table['pages'] = new Route('pagesModel', 'pagesView', 'pagesController');  
		//$this->table['someotherroute'] = new Route('OtherModel', 'OtherView', 'OtherController');
    }
	
    public function getRoute($route) {
        if(empty($route)) $route = "pages"; // Set Default Route here!
		
		$route_lc = strtolower($route);
        return $this->table[$route_lc];
    }
}

class Route {
    public $model;
    public $view;
    public $controller;
    
    public function __construct($model, $view, $controller) {
        $this->model = $model;
        $this->view = $view;
        $this->controller = $controller;
    }
}

?>