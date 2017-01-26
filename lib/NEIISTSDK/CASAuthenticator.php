<?php
require_once("Authenticator.php");
require_once("CAS.php");

class CASAuthenticator extends Authenticator {
    protected function init($config) {
        phpCAS::client(CAS_VERSION_3_0,'id.tecnico.ulisboa.pt',443,'/cas');
        phpCAS::setCasServerCACert('/etc/ssl/certs/AddTrust_External_Root.pem');
        phpCAS::handleLogoutRequests(true, array('id.tecnico.ulisboa.pt'));
    }

    public function login() {
        phpCAS::forceAuthentication();
    }
    
    public function isAuthenticated() {
        return phpCAS::isAuthenticated();
    }

    public function logout() {
        phpCAS::logout();
    }
    
    public function getUsername() {
        return phpCas::getUser();
    }
}
?>
