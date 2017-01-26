<?php

abstract class Authenticator {
    public function __construct($config = null) {
        $this->init($config);
    }
    
    abstract protected function init($config);

    abstract public function login();
    
    abstract public function isAuthenticated();

    abstract public function logout();
    
    abstract public function getUsername();
}
?>
