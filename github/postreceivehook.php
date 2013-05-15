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

        $this->data = $data;

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
