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

    public function churn()
    {
        $unique_activity_timestamps = [];

        foreach ($this->raw_data as $follower_id => $tweets) {
            if (count($tweets) < 2) continue;
            $unique_activity_timestamps[$follower_id] = $this->getUniqueTimestamps($tweets);
        }

        return $this->getBestTimeToTweet($unique_activity_timestamps);
    }

    private function getUniqueTimestamps($tweets)
    {
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

    private function getBestTimeToTweet($timestamps)
    {
        foreach ($timestamps as $follower_id => $time_map) {
            
            foreach ($timestamps[$follower_id]['active_days'] as $active_day) {
                if (! array_key_exists($active_day, $this->active_days_map)) {
                    $this->active_days_map[$active_day] = 0;
                }
                $this->active_days_map[$active_day]++;
            }
            
            foreach ($timestamps[$follower_id]['active_hours'] as $active_hour) {
                if (! array_key_exists($active_hour, $this->active_hours_map)) {
                    $this->active_hours_map[$active_hour] = 0;
                }
                $this->active_hours_map[$active_hour]++;
            }
        }

        return [
            'hour' => array_keys($this->active_hours_map, max($this->active_hours_map))[0],
            'day' => array_keys($this->active_days_map, max($this->active_days_map))[0],
        ];
    }

}