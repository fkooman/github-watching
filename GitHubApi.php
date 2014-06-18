<?php

use Guzzle\Http\Client;

class GitHubApi
{
    private $client;

    const API_REPOS = "https://api.github.com/user/repos";
    const API_SUBSCRIPTIONS = 'https://api.github.com/user/subscriptions';

    /**
     * @param $http The Guzzle object with the OAuth header already added
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
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
