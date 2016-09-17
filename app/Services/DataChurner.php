<?php

namespace App\Services;

class DataChurner
{
    private $raw_data;
    private $active_days_map = [];
    private $active_hours_map = [];

    public function __construct(array $data)
    {
        $this->raw_data = $data;
    }

    /**
     * Analyze data to return best day and hour to tweet
     * 
     * @return array
     */
    public function churn()
    {
        $unique_activity_timestamps = [];

        // Get unique timestamps against each follower
        foreach ($this->raw_data as $follower_id => $tweets) {
            // Ignore followers having less than two tweets
            if (count($tweets) < 2) continue;

            $unique_activity_timestamps[$follower_id] = $this->getUniqueTimestamps($tweets);
        }

        return $this->getBestTimeToTweet($unique_activity_timestamps);
    }

    /**
     * Return unique presence of user in terms of days of week and hours
     * 
     * @param array $tweets
     * @return array
     */
    private function getUniqueTimestamps($tweets)
    {
        // Process the twitter timestamp to get the required data
        foreach ($tweets as $tweet) {
            $fragments      = explode(' ', $tweet->created_at);
            $active_days[]  = $fragments[0];
            $active_hours[] = explode(':', $fragments[3])[0];
        }

        return [
            'active_days'  => array_unique($active_days),
            'active_hours' => array_unique($active_hours) 
        ];
    }

    /**
     * Process time counters to get the most active intervals
     * 
     * @param array $timestamps
     * @return array
     */
    private function getBestTimeToTweet($timestamps)
    {
        foreach ($timestamps as $follower_id => $time_map) {
            
            // Increment day of the week counters
            foreach ($timestamps[$follower_id]['active_days'] as $active_day) {
                if (! array_key_exists($active_day, $this->active_days_map)) {
                    $this->active_days_map[$active_day] = 0;
                }
                $this->active_days_map[$active_day]++;
            }
            
            // Increment hour of the day counters            
            foreach ($timestamps[$follower_id]['active_hours'] as $active_hour) {
                if (! array_key_exists($active_hour, $this->active_hours_map)) {
                    $this->active_hours_map[$active_hour] = 0;
                }
                $this->active_hours_map[$active_hour]++;
            }
        }

        // Return max of the both the counters
        return [
            'hour_of_the_day' => array_keys($this->active_hours_map, max($this->active_hours_map))[0],
            'day_of_the_week' => array_keys($this->active_days_map, max($this->active_days_map))[0],
        ];
    }

}