<?php

require_once 'vendor/autoload.php';
require_once 'config.php';

$repositoriesUri = "https://api.github.com/user/repos";
$subscriptionUri = "https://api.github.com/user/subscriptions";

$apiScope = array("user", "read:org");

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
    $client = new Guzzle\Http\Client();
    $bearerAuth = new fkooman\Guzzle\Plugin\BearerAuth\BearerAuth($accessToken->getAccessToken());
    $client->addSubscriber($bearerAuth);
    $listOfRepositories = $client->get($repositoriesUri)->send()->json();
    $listOfSubscriptions = $client->get($subscriptionUri)->send()->json();

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
    echo $template->render(array("projects" => $data));
} catch (fkooman\Guzzle\Plugin\BearerAuth\Exception\BearerErrorResponseException $e) {
    if ("invalid_token" === $e->getBearerReason()) {
        // the token we used was invalid, possibly revoked, we throw it away
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
