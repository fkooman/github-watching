<?php

require_once 'vendor/autoload.php';
require_once 'config.php';

$clientConfig = new fkooman\OAuth\Client\GitHubClientConfig(
    array(
        "client_id" => $config['client_id'],
        "client_secret" => $config['client_secret']
    )
);

try {
    $tokenStorage = new fkooman\OAuth\Client\SessionStorage();
    $httpClient = new Guzzle\Http\Client();
    $cb = new fkooman\OAuth\Client\Callback("github", $clientConfig, $tokenStorage, $httpClient);
    $cb->handleCallback($_GET);

    header("HTTP/1.1 302 Found");
    header(sprintf("Location: %s", $config['index_uri']));
    exit;
} catch (fkooman\OAuth\Client\Exception\AuthorizeException $e) {
    // this exception is thrown by Callback when the OAuth server returns a
    // specific error message for the client, e.g.: the user did not authorize
    // the request
    die(sprintf("ERROR: %s, DESCRIPTION: %s", $e->getMessage(), $e->getDescription()));
} catch (Exception $e) {
    // other error, these should never occur in the normal flow
    die(sprintf("ERROR: %s", $e->getMessage()));
}
