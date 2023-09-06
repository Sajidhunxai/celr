<?php


namespace FilterEverything\Filter\Pro\Api;


class ApiRequests
{
    private $api_url = '';

    private $send_data = [];

    public function __construct(){
        $this->api_url = 'https://connect.filtereverything.pro/api/v1';
    }

    public function collectPluginData( $license = '' ){
        $this->send_data = array(
            'plugin_name' => FLRT_PLUGIN_BASENAME,
            'plugin_slug' => FLRT_PLUGIN_SLUG,
            'plugin_url'  => FLRT_PLUGIN_DIR_URL,
            'tested_to'   => FLRT_PLUGIN_TESTED_TO,
        );

        if( $license ){
            $license_parts = explode("|", base64_decode( $license ) );
            $license_type  = isset( $license_parts[0] ) ? $license_parts[0] : false;

            $this->send_data = array_merge( $this->send_data, array(
                'license'        => trim( $license ),
                'license_type'   => $license_type
            ) );
        }

        $this->send_data = array_merge( $this->send_data, array(
                'plugin_version' => FLRT_PLUGIN_VER,
                'wp_name'        => get_bloginfo( 'name' ),
                'wp_url'         => home_url(),
                'wp_version'     => get_bloginfo( 'version' ),
                'wp_language'    => get_bloginfo( 'language' ),
                'wp_timezone'    => get_option( 'timezone_string' ),
            )
        );

        return $this->send_data;
    }

    public function sendRequest( $method, $endpoint, $data )
    {
        $curl   = curl_init();
        $url    = $this->api_url .'/'.$endpoint .'/';

        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                // This should be GET method
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "VIEW":
                // This should be GET method
                curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, "VIEW" );
                if ( $data ){
//                    curl_setopt( $curl, CURLOPT_POSTFIELDS, $data );
                    $url = sprintf( "%s?%s", $url, http_build_query( $data ) );
                }
                break;
            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                $url = sprintf("%s?%s", $url, http_build_query($data));
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        // OPTIONS:
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: multipart/form-data'
        ));

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        // EXECUTE:
        $result = curl_exec($curl);
        curl_close($curl);
        if( $result ){
            return json_decode( $result, true );
        }
        return $result;
    }

}