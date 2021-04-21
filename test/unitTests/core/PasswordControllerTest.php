<?php

/**
* Copyright Maarch since 2008 under license.
* See LICENSE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;

class PasswordControllerTest extends TestCase
{
    public function testGet()
    {
        $passwordController = new \SrcCore\controllers\PasswordController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $passwordController->get($request, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertNotEmpty($responseBody->rules);

        foreach ($responseBody->rules as $value) {
            $this->assertNotEmpty($value->id);
            $this->assertIsInt($value->id);
            $this->assertNotEmpty($value->label);
            $this->assertIsInt($value->value);
            $this->assertIsBool($value->enabled);
        }
    }
}
