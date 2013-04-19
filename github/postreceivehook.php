<?php
/*
* Copyright (c) 2013 onwards Christopher Tombleson <chris@powerstack-php.org>
*
* Permission is hereby granted, free of charge, to any person obtaining a copy of this
* software and associated documentation files (the "Software"), to deal in the Software
* without restriction, including without limitation the rights to use, copy, modify, merge,
* publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons
* to whom the Software is furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING
* BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
* IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
* IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE
* OR OTHER DEALINGS IN THE SOFTWARE.
*/
/**
* Github Post Receive Hook
* Github Post Receive Hook class for Powerstack
*
* @author Christopher Tombleson <chris@powerstack-php.org>
* @package Powerstack
* @subpackage Plugins
*/
namespace Powerstack\Plugins\Github;

class PostReceiveHook {
    /**
    * @access private
    * @var stdClass
    */
    private $data;

    /**
    * Process Payload
    * Process post data from sent from Github
    *
    * Hook: Implements github_post_hook which is passed the processed data as a parameter.
    *
    * @access public
    * @return void
    */
    function processPayload() {
        $app = registry('app');
        $hooks = registry('hooks');

        $payload = $app->params->payload;
        $data = json_decode($payload);

        $obj = new \stdclass();
        $obj->after = $data['after'];
        $obj->before = $data['before'];
        $obj->ref = $data['ref'];

        $obj->repository = (object) array(
            'name' => $data['repository']['name'],
            'url' => $data['repository']['url'],
            'pledgie' => $data['repository']['pledgie'],
            'description' => $data['repository']['description'],
            'homepage' => $data['repository']['homepage'],
            'watchers' => $data['repository']['watchers'],
            'forks' => $data['repository']['forks'],
            'private' => $data['repository']['private'],
            'owner' => (object) array(
                'email' => $data['repository']['owner']['email'],
                'name' => $data['repository']['owner']['name'],
            ),
        );

        $obj->commits = array();

        foreach ($data['commits'] as $commit) {
            if (!isset($obj->commits[$commit['id']])) {
                $obj->commits[$commit['id']] = new \stdclass();
            }

            $obj->commits[$commit['id']]->author = (object) array(
                'email' => $commit['author']['email'],
                'name' => $commit['author']['name'],
                'username' => $commit['author']['username'],
            );

            $obj->commits[$commit['id']]->distinct = $commit['distinct'];
            $obj->commits[$commit['id']]->message = $commit['message'];
            $obj->commits[$commit['id']]->timestamp = $commit['timestamp'];
            $obj->commits[$commit['id']]->url = $commit['url'];

            if (!empty($commit['added'])) {
                $obj->commits[$commit['id']]->added = array();

                foreach ($commit['added'] as $added) {
                    $obj->commits[$commit['id']]->added[] = $added;
                }
            }

            if (!empty($commit['modified'])) {
                $obj->commits[$commit['id']]->modified = array();

                foreach ($commit['modified'] as $modified) {
                    $obj->commits[$commit['id']]->modified[] = $modified;
                }
            }

            if (!empty($commit['removed'])) {
                $obj->commits[$commit['id']]->removed = array();

                foreach ($commit['removed'] as $removed) {
                    $obj->commits[$commit['id']]->added[] = $removed;
                }
            }
        }

        $this->data = $obj;

        if ($hooks->exists('github_post_hook')) {
            $githooks = $hooks->get('github_post_hook');

            foreach ($githooks as $githook) {
                if (is_array($githooks)) {
                    call_user_func($githook, $this->data);
                } else {
                    $githook($this->data);
                }
            }
        }
    }

    /**
    * Get Data
    * Get processed data
    *
    * @access public
    * @return stdClass data
    */
    function getData() {
        return $this->data;
    }
}
?>
