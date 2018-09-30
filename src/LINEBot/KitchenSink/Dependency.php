<?php

/**
 * Copyright 2016 LINE Corporation
 *
 * LINE Corporation licenses this file to you under the Apache License,
 * version 2.0 (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at:
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

namespace LINE\LINEBot\KitchenSink;

use PDO;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

class Dependency
{
    public function register(\Slim\App $app)
    {
        $container = $app->getContainer();

        $container['logger'] = function ($c) {
            $settings = $c->get('settings')['logger'];
            $logger = new \Monolog\Logger($settings['name']);
            $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
            $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], \Monolog\Logger::DEBUG));
            return $logger;
        };

        $container['bot'] = function ($c) {
            $settings = $c->get('settings');
            $channelSecret = $settings['bot']['channelSecret'];
            $channelToken = $settings['bot']['channelToken'];
            $apiEndpointBase = $settings['apiEndpointBase'];
            $pdo=$c->get('pdo');
            $bot = new LINEBot(new CurlHTTPClient($channelToken), [
                'channelSecret' => $channelSecret,
                'endpointBase' => $apiEndpointBase, // <= Normally, you can omit this
                'pdo' => $pdo
            ]);
            return $bot;
        };

        $container['pdo']=function($c) {
            $url=parse_url(getenv('DATABASE_URL'));
            $host=$url['host'];
            $dbname=substr($url['path'],1);
            $user=$url['user'];
            $password=$url['pass'];
            $pdo=new PDO("pgsql:host=$host;dbname=$dbname",$user,$password);
if ($pdo==null) {
error_log("Failed to connect to data base");
} else {
error_log("Succeeded to connect to data base");
}
            return $pdo;
        };
    }
}
