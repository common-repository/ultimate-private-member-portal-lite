<?php

if (!function_exists('upmp_add_query_string')) {

    function upmp_add_query_string($link, $query_str) {

        $build_url = $link;

        $query_comp = explode('&', $query_str);

        foreach ($query_comp as $param) {
            $params = explode('=', $param);
            $key = isset($params[0]) ? $params[0] : '';
            $value = isset($params[1]) ? $params[1] : '';
            $build_url = esc_url_raw(add_query_arg($key, $value, $build_url));
        }

        return $build_url;
    }

}

if (!function_exists('upmp_add_query_string')) {

    function upmp_add_query_string($link, $query_str) {

        $build_url = $link;

        $query_comp = explode('&', $query_str);

        foreach ($query_comp as $param) {
            $params = explode('=', $param);
            $key = isset($params[0]) ? $params[0] : '';
            $value = isset($params[1]) ? $params[1] : '';
            $build_url = esc_url_raw(add_query_arg($key, $value, $build_url));
        }

        return $build_url;
    }

}

if (!function_exists('upmp_email_set_content_type_html')) {
    function upmp_email_set_content_type_html( $content_type ) {
        return 'text/html';
    }
}

if (!function_exists('upmp_current_page_url')) {
    function upmp_current_page_url() {
        $current_page_url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
        $current_page_url .= $_SERVER["REQUEST_URI"];

        $parsed_url = parse_url($current_page_url);
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query     = isset($parsed_url['query']) ? $parsed_url['query'] : '';

        $current_page_url = $scheme.$user.$pass.$host.$port.$path;
        if($query != '')
            $current_page_url = $scheme.$user.$pass.$host.$port.$path."?".$query;

        return $current_page_url;
    }
}







