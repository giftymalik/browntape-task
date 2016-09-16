<?php

$app->get('/', 'HomeController@index');
$app->post('/fetch-data', 'HomeController@fetch');