<?php

namespace App\Http\Controllers;

use \App\Services\Twitter;
use \Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('home.index');
    }

    public function fetch(Request $request)
    {
        $user_id = $request->input('user_id');
        $screen_name = $request->input('screen_name');

        if (empty($user_id) && empty($screen_name)) {
            return redirect()->back()->with("error", "Login failed!");
        }

        
        if (empty($user_id)) {
            $twitter = Twitter::initFromScreenName(
                strtolower($screen_name)
            );
            
            // return $twitter->fetchActiveFollowers();
            return $twitter->fetchRecentTweets();
        }

        $user_id = (int) $request->get('user_id');

        if (is_null($user_id)) return 'Invalid User ID';

        $twitter = Twitter::initFromUserID($user_id);

        // return $twitter->fetchActiveFollowers();
        return $twitter->fetchRecentTweets();
    }
}