<?php

namespace fkooman\GitHub;

use Guzzle\Http\Client;

class Api
{
    private $client;

    const API_USER = 'https://api.github.com/user';
    const API_REPOS = "https://api.github.com/user/repos";
    const API_SUBSCRIPTIONS = 'https://api.github.com/user/subscriptions';

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

    public function subscribeRepository($id)
    {

    }

    public function unsubscribeRepository($id)
    {

    }
}
