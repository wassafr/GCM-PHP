<?php

namespace Wassa\GcmPhp;
/**
*
* @package Gcm
* @version $Id$
* @copyright (c) 2011 lytsing.org & 2012 thebub.net
* Description: C2DM implementation PHP code
* refer to: http://stackoverflow.com/questions/4121508/Gcm-implementation-php-code
*
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
* http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*
*/

class Gcm
{
	private static $instance = null;

	public static function getInstance()
	{
		if (!Gcm::$instance) {
			Gcm::$instance = new Gcm();
		}
	
		return Gcm::$instance;
	}

    /**
     * Send GCM message to specified device
     * @see https://developers.google.com/android/Gcm/#server
     */
    public function sendMessage($yourKey, $dryRun, $deviceRegistrationIds, $message, $msgType)
    {
        $headers = array("Authorization:key=$yourKey\nContent-type: application/json");

        $data = array(
            'dry_run' => $dryRun,
            'registration_ids' => $deviceRegistrationIds,
            'collapse_key' => $msgType,
            'data' => ['message' => $message]);

        $curl = \curl_init();
        \curl_setopt($curl, CURLOPT_URL, "https://android.googleapis.com/gcm/send");
        \curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        \curl_setopt($curl, CURLOPT_HEADER, 1);
        \curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        \curl_setopt($curl, CURLOPT_POST, true);
        \curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

        $response_data = \curl_exec($curl);
        $response_info = \curl_getinfo($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $body = substr($response_data, $header_size);

        \curl_close($curl);

        if ($response_info['http_code'] == 200) {
            return json_decode($body);
        } else if ($response_info['http_code'] == 401) {
            throw new \Exception("Not authenticated");
        } else if ($response_info['http_code'] == 503) {
            throw new \Exception("Service Unavailable");
        }

        return false;
    }
}
