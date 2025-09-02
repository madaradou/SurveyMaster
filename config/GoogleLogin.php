<?php
require_once 'google_config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Google_Client;

class GoogleLogin {
    private $client;
    private $client_id;
    private $client_secret;
    private $client_redirect_uri;

    public function __construct($redirect_url = '') {
        // Use credentials from config file
        $this->client_id = GOOGLE_CLIENT_ID;
        $this->client_secret = GOOGLE_CLIENT_SECRET;
        $this->client_redirect_uri = empty($redirect_url) ? GOOGLE_REDIRECT_URI : $redirect_url;

        // Initialize Google Client
        $this->client = new Google_Client();
        $this->client->setClientId($this->client_id);
        $this->client->setClientSecret($this->client_secret);
        $this->client->setRedirectUri($this->client_redirect_uri);

        // Add all scopes from config
        foreach (GOOGLE_SCOPES as $scope) {
            $this->client->addScope($scope);
        }
    }

    public function getClient() {
        return $this->client;
    }

    public function getAuthUrl() {
        return $this->client->createAuthUrl();
    }

    public function getAccessToken() {
        return $this->client->getAccessToken();
    }

    public function setAccessToken($token) {
        $this->client->setAccessToken($token);
    }

    public function isAccessTokenExpired() {
        return $this->client->isAccessTokenExpired();
    }

    public function getClientId() {
        return $this->client_id;
    }

    public function getClientSecret() {
        return $this->client_secret;
    }

    public function getRedirectUri() {
        return $this->client_redirect_uri;
    }
}