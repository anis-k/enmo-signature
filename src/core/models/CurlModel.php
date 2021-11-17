<?php

/**
 * Copyright Maarch since 2008 under license.
 * See LICENSE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Curl Model
 * @author dev@maarch.org
 */

namespace SrcCore\models;

use SrcCore\models\ValidatorModel;

class CurlModel
{
    public static function execSimple(array $args)
    {
        ValidatorModel::notEmpty($args, ['url', 'method']);
        ValidatorModel::stringType($args, ['url', 'method']);
        ValidatorModel::arrayType($args, ['headers', 'queryParams', 'basicAuth', 'bearerAuth']);
        ValidatorModel::boolType($args, ['isXml']);

        $args['isXml'] = $args['isXml'] ?? false;

        $opts = [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_SSL_VERIFYPEER => false];

        //Headers
        if (!empty($args['headers'])) {
            $opts[CURLOPT_HTTPHEADER] = $args['headers'];
        }
        //Auth
        if (!empty($args['basicAuth'])) {
            $opts[CURLOPT_HTTPHEADER][] = 'Authorization: Basic ' . base64_encode($args['basicAuth']['user']. ':' .$args['basicAuth']['password']);
        }
        if (!empty($args['bearerAuth'])) {
            $opts[CURLOPT_HTTPHEADER][] = 'Authorization: Bearer ' . $args['bearerAuth']['token'];
        }

        //QueryParams
        if (!empty($args['queryParams'])) {
            $args['url'] .= '?';
            $i = 0;
            foreach ($args['queryParams'] as $queryKey => $queryParam) {
                if ($i > 0) {
                    $args['url'] .= '&';
                }
                $args['url'] .= "{$queryKey}={$queryParam}";
                ++$i;
            }
        }

        //Body
        if (!empty($args['body'])) {
            $opts[CURLOPT_POSTFIELDS] = $args['body'];
        }
        //MultipartBody
        if (!empty($args['multipartBody'])) {
            $boundary = uniqid();
            $postData = CurlModel::createMultipartFormData(['boundary' => $boundary, 'body' => $args['multipartBody']]);
            $opts[CURLOPT_HTTPHEADER][] = "Content-Type: multipart/form-data; boundary=-------------{$boundary}";
            $opts[CURLOPT_POSTFIELDS] = $postData;
        }
        //Method
        if ($args['method'] == 'POST') {
            $opts[CURLOPT_POST] = true;
        } elseif ($args['method'] == 'PUT' || $args['method'] == 'PATCH' || $args['method'] == 'DELETE') {
            $opts[CURLOPT_CUSTOMREQUEST] = $args['method'];
        }

        //Url
        $opts[CURLOPT_URL] = $args['url'];

        $curl        = curl_init();
        curl_setopt_array($curl, $opts);
        $rawResponse = curl_exec($curl);
        $code        = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $headerSize  = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $errors      = curl_error($curl);
        curl_close($curl);

        $headers  = substr($rawResponse, 0, $headerSize);
        $headers  = explode("\r\n", $headers);
        $response = substr($rawResponse, $headerSize);

        if ($args['isXml']) {
            $response = simplexml_load_string($response);
        } elseif (!$args['headers'][CURLOPT_BINARYTRANSFER]) {
            $response = json_decode($response, true);
        }

        return ['code' => $code, 'headers' => $headers, 'response' => $response, 'errors' => $errors];
    }

    private static function createMultipartFormData(array $args)
    {
        ValidatorModel::notEmpty($args, ['boundary', 'body']);
        ValidatorModel::stringType($args, ['boundary']);
        ValidatorModel::arrayType($args, ['body']);

        $delimiter = "-------------{$args['boundary']}";

        $postData = '';
        foreach ($args['body'] as $key => $value) {
            $postData .= "--{$delimiter}\r\n";
            if (is_array($value) && $value['isFile']) {
                $postData .= "Content-Disposition: form-data; name=\"{$key}\"; filename=\"{$value['filename']}\"\r\n";
                $postData .= "\r\n{$value['content']}\r\n";
            } else {
                $postData .= "Content-Disposition: form-data; name=\"{$key}\"\r\n";
                $postData .= "\r\n{$value}\r\n";
            }
        }
        $postData .= "--{$delimiter}--\r\n";

        return $postData;
    }
}
