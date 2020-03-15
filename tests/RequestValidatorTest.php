<?php
use PHPUnit\Framework\TestCase;
use NgramSearch\RequestValidator;
 
class RequestValidatorTest extends TestCase {

    public function testPassingGetRequestValidationWithAnonymousFunc() : void
    {
        $errors = (new RequestValidator)->addGetCheck(
            1,
            function($value) {
                return is_numeric($value);
            },
            'Value must be numeric',
            'number'
        )->execute();

        $this->assertSame([], $errors);
    }

    public function testPassingPostRequestValidationWithAnonymousFunc() : void
    {
        $errors = (new RequestValidator)->addPostCheck(
            1,
            function($value) {
                return is_numeric($value);
            },
            'Value must be numeric',
            '/foo/bar/number'
        )->execute();

        $this->assertSame([], $errors);
    }

    public function testPassingRequestValidationWithDefinedFunction() : void
    {
        $errors = (new RequestValidator)->addGetCheck(
            1,
            'is_numeric',
            'Value must be numeric',
            'number'
        )->execute();

        $this->assertSame([], $errors);
    }

    public function testExceptionOnNonBooleanReturnValue() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new RequestValidator)->addGetCheck(
            1,
            function($value) {
                return 'I am not a boolean';
            },
            'Value must be numeric',
            'number'
        )->execute(); 
    }

    public function testFailingGetRequestValidation() : void
    {
        $errors = (new RequestValidator)->addGetCheck(
            'I am not numeric',
            function($value) {
                return is_numeric($value);
            },
            'Value must be numeric',
            'number'
        )->execute();

        $this->assertSame(
            [
                [
                    'status' => 400,
                    'title' => 'Invalid Query Parameter',
                    'detail' => 'Value must be numeric',
                    'source' => [
                        'parameter' => 'number'
                    ]
                ]
            ], 
            $errors
        );
    }

    public function testFailingPostRequestValidation() : void
    {
        $errors = (new RequestValidator)->addPostCheck(
            'I am not numeric',
            function($value) {
                return is_numeric($value);
            },
            'Value must be numeric',
            '/foo/bar/number'
        )->execute();

        $this->assertSame(
            [
                [
                    'status' => 400,
                    'title' => 'Invalid Request Body',
                    'detail' => 'Value must be numeric',
                    'source' => [
                        'pointer' => '/foo/bar/number'
                    ]
                ]
            ], 
            $errors
        );
    } 

    public function testStopOnFirstError() : void
    {
        $errors = (new RequestValidator)->addGetCheck(
            'I am not numeric',
            function($value) {
                return is_numeric($value);
            },
            'First failing check, store error.',
            'number'
        )
        ->addGetCheck(
            'I am not numeric',
            function($value) {
                return is_numeric($value);
            },
            'Second failing check, ignore error!',
            'number'
        )
        ->addGetCheck(
            'I am not numeric',
            function($value) {
                return is_numeric($value);
            },
            'Third failing check, but different source, store error.',
            'age'
        )->execute();

        $this->assertSame(2, count($errors));
    }
}
