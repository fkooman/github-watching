<?php

require_once 'vendor/autoload.php';
require_once 'config.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Guzzle\Plugin\Log\LogPlugin;
use Guzzle\Log\MessageFormatter;
use Guzzle\Log\MonologLogAdapter;
use Guzzle\Http\Exception\ClientErrorResponseException;

$apiScope = array("notifications");

$clientConfig = new fkooman\OAuth\Client\GitHubClientConfig(
    array(
        "client_id" => $config['client_id'],
        "client_secret" => $config['client_secret']
    )
);

$tokenStorage = new fkooman\OAuth\Client\SessionStorage();
$httpClient = new Guzzle\Http\Client();
$api = new fkooman\OAuth\Client\Api("github", $clientConfig, $tokenStorage, $httpClient);

$context = new fkooman\OAuth\Client\Context("john.doe@example.org", $apiScope);

$accessToken = $api->getAccessToken($context);
if (false === $accessToken) {
    /* no valid access token available, go to authorization server */
    header("HTTP/1.1 302 Found");
    header("Location: " . $api->getAuthorizeUri($context));
    exit;
}

try {
    $log = new Logger('github-watching');
    $log->pushHandler(new StreamHandler(sprintf("%s/log/client.log", __DIR__), Logger::DEBUG));
    $logPlugin = new LogPlugin(new MonologLogAdapter($log), MessageFormatter::DEBUG_FORMAT);

    $client = new Guzzle\Http\Client();
    $bearerAuth = new fkooman\Guzzle\Plugin\BearerAuth\BearerAuth($accessToken->getAccessToken());
    $client->addSubscriber($bearerAuth);
    // $client->addSubscriber($logPlugin);

    $ghapi = new fkooman\GitHub\Api($client);
    $userLogin = $ghapi->getUserLogin();

    // $ghapi->unsubscribeRepository('php-lib-types');
    // $ghapi->unsubscribeRepository('php-remoteStorage');
    // $ghapi->unsubscribeRepository('github-watching');
    // $ghapi->subscribeRepository('github-watching');

    $listOfRepositories = $ghapi->getMyRepositories();
    $listOfSubscriptions = $ghapi->getMySubscriptions();

    $data = array();

    foreach ($listOfRepositories as $r) {
        $watch = false;

        foreach ($listOfSubscriptions as $s) {
            if ($r['id'] === $s['id']) {
                // yes we watch it
                $watch = true;
            }
        }

        $data[] = array(
            "name" => $r['name'],
            "watching" => $watch ? "yes" : "no",
            "html_url" => $r['html_url']
        );
    }

    $loader = new Twig_Loader_Filesystem('templates');
    $twig = new Twig_Environment($loader);
    $template = $twig->loadTemplate('index.html');
    echo $template->render(array("projects" => $data, "userLogin" => $userLogin));
} catch (ClientErrorResponseException $e) {
    // GitHub does not use the official OAuth 2.0 Bearer response with
    // the WWW-Authenticate header in case of authorization errors so we
    // cannot catch BearerErrorResponseException
    if (401 === $e->getResponse()->getStatusCode()) {
        $api->deleteAccessToken($context);
        $api->deleteRefreshToken($context);
        /* no valid access token available, go to authorization server */
        header("HTTP/1.1 302 Found");
        header("Location: " . $api->getAuthorizeUri($context));
        exit;
    }
    throw $e;
} catch (Exception $e) {
    die(sprintf('ERROR: %s', $e->getMessage()));
}
