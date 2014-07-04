<?php
namespace phorkie;

class ToolsTest extends \PHPUnit_Framework_TestCase
{
    public function testDetectBaseUrlPhar()
    {
        $_SERVER['REQUEST_URI'] = '/phar/phorkie-0.4.0.phar/list.php';
        $_SERVER['SCRIPT_NAME'] = '/phar/phorkie-0.4.0.phar';
        $this->assertEquals(
            '/phar/phorkie-0.4.0.phar/',
            Tools::detectBaseUrl()
        );
    }

    public function testDetectBaseUrlRoot()
    {
        $_SERVER['REQUEST_URI'] = '/new';
        $_SERVER['SCRIPT_NAME'] = '/new.php';
        $this->assertEquals('/', Tools::detectBaseUrl());
    }

    public function testDetectBaseUrlRootWithPhp()
    {
        $_SERVER['REQUEST_URI'] = '/new.php';
        $_SERVER['SCRIPT_NAME'] = '/new.php';
        $this->assertEquals('/', Tools::detectBaseUrl());
    }

    public function testDetectBaseUrlSubdir()
    {
        $_SERVER['REQUEST_URI'] = '/foo/new';
        $_SERVER['SCRIPT_NAME'] = '/new.php';
        $this->assertEquals('/foo/', Tools::detectBaseUrl());
    }

    public function testFoldPathParentSingle()
    {
        $this->assertEquals(
            '/path/to/foo',
            Tools::foldPath('/path/to/bar/../foo')
        );
    }

    public function testFoldPathParentDouble()
    {
        $this->assertEquals(
            '/path/to/foo',
            Tools::foldPath('/path/to/foo/bar/../../foo')
        );
    }

    public function testFoldPathCurrentSingle()
    {
        $this->assertEquals(
            '/path/to/foo/',
            Tools::foldPath('/path/to/foo/./')
        );
    }

    public function testFoldPathCurrentThrice()
    {
        $this->assertEquals(
            '/path/to/foo/',
            Tools::foldPath('/path/././to/foo/./')
        );
    }
}
?>
