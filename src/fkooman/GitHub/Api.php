<?php

namespace fkooman\GitHub;

use Guzzle\Http\Client;

class Api
{
    private $client;

    const API_USER = 'https://api.github.com/user';
    const API_REPOS = "https://api.github.com/user/repos";
    const API_SUBSCRIPTIONS = 'https://api.github.com/user/subscriptions';

    const API_SUBSCRIBE = 'https://api.github.com/repos/:owner/:repo/subscription';

    /**
     * @param $http The Guzzle object with the OAuth header already added
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getUserLogin()
    {
        $userData = $this->client->get(self::API_USER)->send()->json();

        return $userData['login'];
    }

    public function getMyRepositories()
    {
        return $this->client->get(self::API_REPOS)->send()->json();
    }

    public function getMySubscriptions()
    {
        return $this->client->get(self::API_SUBSCRIPTIONS)->send()->json();
    }

    public function subscribeRepository($repo)
    {
        $subscribeUri = str_replace(':owner', $this->getUserLogin(), str_replace(':repo', $repo, self::API_SUBSCRIBE));

        $putData = json_encode(
            array(
                "subscribed" => true,
                "ignored" => false
            )
        );

        $this->client->put(
            $subscribeUri,
            array(
                'headers' => array('Content-Type' => 'application/json'),
            ),
            $putData
        )->send();
    }

    public function unsubscribeRepository($repo)
    {
        $subscribeUri = str_replace(':owner', $this->getUserLogin(), str_replace(':repo', $repo, self::API_SUBSCRIBE));
        $this->client->delete($subscribeUri)->send();
    }
}
