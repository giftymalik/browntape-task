<?php

namespace App\Services;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use GuzzleHttp\Client as HttpRequestClient;

class Twitter
{
    private $client;
    private $user_id;
    private $screen_name;
    private $tweet_deck = [];

    private function __construct()
    {
        $handler_stack = HandlerStack::create();
        
        $oauth_params = new Oauth1([
            'consumer_key'    => env('TWITTER_CONSUMER_KEY'),
            'consumer_secret' => env('TWITTER_CONSUMER_SECRET'),
            'token'           => env('TWITTER_TOKEN'),
            'token_secret'    => env('TWITTER_TOKEN_SECRET')
        ]);
        
        $handler_stack->push($oauth_params);
        
        $this->client = new HttpRequestClient([
            'base_uri' => 'https://api.twitter.com/1.1/',
            'handler'  => $handler_stack
        ]);
    }

    public static function initFromUserID(int $user_id)
    {
        $twitter = new Twitter();
        $twitter->user_id = $user_id;
        
        return $twitter;
    }

    public static function initFromScreenName(string $screen_name)
    {
        $twitter = new Twitter();
        $twitter->screen_name = $screen_name;
        
        return $twitter;
    }

    public function fetchActiveFollowers()
    {
        try {
            $res = $this->client->get('followers/list.json', [
                'query' => [
                    'user_id'     => $this->user_id,
                    'screen_name' => $this->screen_name,
                    'count'       => env('TWITTER_FOLLOWERS_TO_PROCESS')
                ],
                'auth' => 'oauth'
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        if (! $res) return 'Invalid response from Twitter.';

        return array_column(json_decode($res->getBody())->users, 'id');
    }
    
    public function fetchRecentTweets()
    {
        $followers = $this->fetchActiveFollowers();
        array_map([$this, 'fetchTweetsByUserID'], $followers);

        return $this->tweet_deck;
    }

    private function fetchTweetsByUserID($user_id)
    {
        try {
            $res = $this->client->get('statuses/user_timeline.json', [
                'query' => [
                    'user_id' => $user_id,
                    'count'   => env('TWEETS_PER_FOLLOWER_TO_PROCESS')
                ],
                'auth' => 'oauth'
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        
        if (! $res) return 'Invalid response from Twitter.';

        $this->tweet_deck[$user_id] = json_decode($res->getBody());
    }
}