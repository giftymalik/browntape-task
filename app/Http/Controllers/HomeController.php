<?php

namespace App\Http\Controllers;

use \App\Services\Twitter;
use \App\Services\DataChurner;
use \Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Homepage to get the inputs
     * 
     * @return html
     */
    public function index()
    {
        return view('home.index');
    }

    /**
     * Get the best time to tweet
     * 
     * @param Request $request
     * @return json
     */
    public function fetch(Request $request)
    {
        $user_id     = $request->input('user_id');
        $screen_name = $request->input('screen_name');

        // Validate and sanitize the inputs
        if (empty($user_id) && empty($screen_name)) {
            return response()->json([
                "status" => false,
                "error"  => "Kindly retry with either of the fields."
            ]);
        }

        if (empty($user_id)) {
            $twitter = Twitter::initFromScreenName(
                strtolower($screen_name)
            );
        } else {
            $user_id = (int) $request->get('user_id');

            if (is_null($user_id)) {
                return response()->json([
                    "status" => false,
                    "error"  => "Provided User ID is invalid. Kindly retry with a valid numeric ID."
                ]);
            }

            $twitter = Twitter::initFromUserID($user_id);
        }
        
        // Fetch and map recent tweets against followers
        $tweets = $twitter->fetchRecentTweets();
        if (! is_array($tweets)) {
            return response()->json([
                "status" => false,
                "error"  => "Invalid response from Twitter"
            ]);
        }

        // Process data to return the best hour and day of week to tweet
        $churner = new DataChurner($tweets);
        return response()->json($churner->churn());
    }
}
