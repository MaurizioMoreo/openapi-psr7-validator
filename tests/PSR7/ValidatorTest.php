<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7;

use cebe\openapi\Reader;
use OpenAPIValidation\PSR7\Exception\ResponseBodyMismatch;
use OpenAPIValidation\PSR7\Exception\ResponseHeadersMismatch;
use OpenAPIValidation\PSR7\ResponseAddress;
use OpenAPIValidation\PSR7\Validator;
use function GuzzleHttp\Psr7\stream_for;

class ValidatorTest extends BaseValidatorTest
{

    public function test_it_validates_message_green()
    {
        $response = $this->makeGoodResponse('/path1');

        $validator = new Validator(Reader::readFromYamlFile($this->apiSpecFile));
        $validator->validateResponse(new ResponseAddress('/path1', 'get', 200), $response);
        $this->addToAssertionCount(1);
    }

    public function test_it_validates_message_wrong_body_value_red()
    {
        $addr     = new ResponseAddress('/path1', 'get', 200);
        $body     = [];
        $response = $this->makeGoodResponse('/path1')->withBody(stream_for(json_encode($body)));

        try {
            $validator = new Validator(Reader::readFromYamlFile($this->apiSpecFile));
            $validator->validateResponse($addr, $response);
            $this->fail("Exception expected");
        } catch (ResponseBodyMismatch $e) {
            $this->assertEquals($addr->path(), $e->path());
            $this->assertEquals($addr->method(), $e->method());
            $this->assertEquals($addr->responseCode(), $e->responseCode());
        }

    }

    public function test_it_validates_message_wrong_header_value_red()
    {
        $addr = new ResponseAddress('/path1', 'get', 200);

        $response = $this->makeGoodResponse('/path1')->withHeader('Header-B', 'wrong value');

        try {
            $validator = new Validator(Reader::readFromYamlFile($this->apiSpecFile));
            $validator->validateResponse($addr, $response);
            $this->fail("Exception expected");
        } catch (ResponseHeadersMismatch $e) {
            $this->assertEquals($addr->path(), $e->path());
            $this->assertEquals($addr->method(), $e->method());
            $this->assertEquals($addr->responseCode(), $e->responseCode());
        }

    }
}
