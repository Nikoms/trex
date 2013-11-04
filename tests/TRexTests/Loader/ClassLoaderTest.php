<?php
namespace TRexTests\Loader;

use TRex\Loader\ClassLoader;

/**
 * Class ClassLoaderTest
 * @package TRexTests\Loader
 */
class ClassLoaderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * ClassLoader is a singleton and can not be instantiate directly
     */
    public function test__construct()
    {
        $reflectedClass = new \ReflectionClass('TRex\Loader\ClassLoader');
        $this->assertFalse($reflectedClass->isInstantiable());
    }

    /**
     * ClassLoader is a singleton and can not be clone directly
     */
    public function test__clone()
    {
        $reflectedClass = new \ReflectionClass('TRex\Loader\ClassLoader');
        $this->assertFalse($reflectedClass->isCloneable());
    }

    /**
     * test the type of return
     */
    public function testGetInstanceType()
    {
        $this->assertInstanceOf('\TRex\Loader\ClassLoader', ClassLoader::getInstance());
    }

    /**
     * test the unique instance of the return
     */
    public function testGetInstanceUnique()
    {
        $this->assertSame(ClassLoader::getInstance(), ClassLoader::getInstance());
    }

    /**
     * has an undefined vendor
     *
     * @return array
     */
    public function testHasVendorNotAdded()
    {
        $vendorData = array(
            'name' => 'Vendor',
            'sourcePath' => sprintf('vendor%ssrc%s', DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR),
            'realPath' => $this->getBaseDir(sprintf('vendor%ssrc', DIRECTORY_SEPARATOR)),
            'rootDir' => $this->getBaseDir('vendor'),
        );
        $this->assertFalse(ClassLoader::getInstance()->hasVendor($vendorData['name']));
        return $vendorData;
    }

    /**
     * get an undefined vendor source path
     *
     * @depends testHasVendorNotAdded
     */
    public function testGetSourcePathNotAdded(array $vendorData)
    {
        $this->assertSame('', ClassLoader::getInstance()->getSourcePath($vendorData['name']));
        return $vendorData;
    }

    /**
     * get an undefined vendor root dir
     *
     * @depends testGetSourcePathNotAdded
     */
    public function testGetRootDirNotAdded(array $vendorData)
    {
        $this->assertSame('', ClassLoader::getInstance()->getRootDir($vendorData['name']));
        return $vendorData;
    }

    /**
     * get an undefined vendor real path
     *
     * @depends testGetRootDirNotAdded
     */
    public function testGetRealPathNotAdded(array $vendorData)
    {
        $this->assertSame('', ClassLoader::getInstance()->getRealPath($vendorData['name']));
        return $vendorData;
    }

    /**
     * remove an undefined vendor
     *
     * @depends testGetRealPathNotAdded
     */
    public function testRemoveVendorNotExisting(array $vendorData)
    {
        $this->assertFalse(ClassLoader::getInstance()->removeVendor($vendorData['name']));
        return $vendorData;
    }

    /**
     * add a vendor for the first time
     *
     * @depends testGetSourcePathNotAdded
     */
    public function testAddVendorFirst(array $vendorData)
    {
        $this->assertTrue(ClassLoader::getInstance()->addVendor($vendorData['name'], $vendorData['sourcePath']));
        return $vendorData;
    }

    /**
     * add a same vendor for the second time
     *
     * @depends testAddVendorFirst
     */
    public function testAddVendorSecond(array $vendorData)
    {
        $this->assertFalse(ClassLoader::getInstance()->addVendor($vendorData['name'], $vendorData['sourcePath']));
        return $vendorData;
    }

    /**
     * added source path can be relative
     */
    public function testAddVendorWithRelativePath()
    {
        $this->assertTrue(
            ClassLoader::getInstance()->addVendor(
                __FUNCTION__,
                sprintf('..%s%s%sPath%s', DIRECTORY_SEPARATOR, __FUNCTION__, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR)
            )
        );
    }

    /**
     * get a vendor source path witch has been added without an ending DIRECTORY_SEPARATOR
     *
     * @expectedException \PHPUnit_Framework_Error_Notice
     * @expectedExceptionMessage Vendor source path must end with
     */
    public function testGetSourcePathWithoutSlash()
    {
        $vendorData = array(
            'name' => __FUNCTION__,
            'sourcePath' => sprintf('%s%sPath', __FUNCTION__, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR)
        );
        $this->assertTrue(ClassLoader::getInstance()->addVendor($vendorData['name'], $vendorData['sourcePath']));
        $this->assertSame(
            $vendorData['sourcePath'] . DIRECTORY_SEPARATOR,
            ClassLoader::getInstance()->getSourcePath($vendorData['name'])
        );
    }

    /**
     * has an existing vendor
     *
     * @depends testAddVendorFirst
     */
    public function testHasVendorAfterAdd(array $vendorData)
    {
        $this->assertTrue(ClassLoader::getInstance()->hasVendor($vendorData['name']));
        return $vendorData;
    }

    /**
     * get a vendor source path
     *
     * @depends testHasVendorAfterAdd
     */
    public function testGetSourcePathAfterAdd(array $vendorData)
    {
        $this->assertSame(
            $vendorData['sourcePath'],
            ClassLoader::getInstance()->getSourcePath($vendorData['name'])
        );
        return $vendorData;
    }

    /**
     * get a vendor root dir
     *
     * @depends testGetSourcePathAfterAdd
     */
    public function testGetRootDirAfterAdd(array $vendorData)
    {
        $this->assertSame(
            $vendorData['rootDir'],
            ClassLoader::getInstance()->getRootDir($vendorData['name'])
        );
        return $vendorData;
    }

    /**
     * get a vendor real path
     *
     * @depends testGetRootDirAfterAdd
     */
    public function testGetRealPathAfterAdd(array $vendorData)
    {
        $this->assertSame(
            $vendorData['realPath'],
            ClassLoader::getInstance()->getRealPath($vendorData['name'])
        );
        return $vendorData;
    }

    /**
     * remove a vendor
     *
     * @depends testGetRealPathAfterAdd
     */
    public function testRemoveVendorExisting(array $vendorData)
    {
        $this->assertTrue(ClassLoader::getInstance()->removeVendor($vendorData['name']));
        return $vendorData;
    }

    /**
     * has a removed vendor
     *
     * @depends testRemoveVendorExisting
     */
    public function testHasVendorAfterRemove(array $vendorData)
    {
        $this->assertFalse(ClassLoader::getInstance()->hasVendor($vendorData['name']));
        return $vendorData;
    }

    /**
     * add a list of vendors
     */
    public function testAddVendors()
    {
        $this->assertTrue(
            ClassLoader::getInstance()->addVendors(
                array(
                    sprintf('%s1', __FUNCTION__) => sprintf(
                        '%s1%sPath%s',
                        __FUNCTION__,
                        DIRECTORY_SEPARATOR,
                        DIRECTORY_SEPARATOR
                    ),
                    sprintf('%s2', __FUNCTION__) => sprintf(
                        '%s2%sPath%s',
                        __FUNCTION__,
                        DIRECTORY_SEPARATOR,
                        DIRECTORY_SEPARATOR
                    ),
                )
            )
        );

        $this->assertTrue(ClassLoader::getInstance()->hasVendor(sprintf('%s1', __FUNCTION__)));
        $this->assertTrue(ClassLoader::getInstance()->hasVendor(sprintf('%s2', __FUNCTION__)));

    }

    /**
     * add the same vendor twice
     */
    public function testAddVendorsWithDouble()
    {
        $this->assertTrue(
            ClassLoader::getInstance()->addVendors(
                array(
                    sprintf('%s', __FUNCTION__) => sprintf(
                        '%s%sPath%s',
                        __FUNCTION__,
                        DIRECTORY_SEPARATOR,
                        DIRECTORY_SEPARATOR
                    ),
                    sprintf('%s', __FUNCTION__) => sprintf(
                        '%s%sPath%s',
                        __FUNCTION__,
                        DIRECTORY_SEPARATOR,
                        DIRECTORY_SEPARATOR
                    ),
                )
            )
        );
    }

    /**
     * test the default config with TRex path
     */
    public function testGetSourcePathDefault()
    {
        $this->assertSame('trex/src/', ClassLoader::getInstance()->getSourcePath('TRex'));
        $this->assertSame('trex/tests/', ClassLoader::getInstance()->getSourcePath('TRexTests'));
    }

    /**
     * test the default config with TRex root dir
     */
    public function testGetRootDirDefault()
    {
        $this->assertSame($this->getBaseDir('trex'), ClassLoader::getInstance()->getRootDir('TRex'));
        $this->assertSame($this->getBaseDir('trex'), ClassLoader::getInstance()->getRootDir('TRexTests'));
    }

    /**
     * test the conversion of a class path
     *
     * @param string $className
     * @param string $path
     *
     * @dataProvider provideClassPaths
     */
    public function testGetClassPath($className, $path)
    {
        ClassLoader::getInstance()->addVendor(
            'Vendor',
            sprintf('vendor%ssrc%s', DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR)
        );
        $this->assertSame($path, ClassLoader::getInstance()->getClassPath($className));
    }

    /**
     * test the conversion of a class path with a vendor not added before.
     *
     * @expectedException \PHPUnit_Framework_Error
     * @expectedExceptionMessage Detected vendor Vendor2 was not recorded
     */
    public function testGetClassPathWithNotAddedVendor()
    {
        $this->assertSame(
            $this->getFilePath(
                sprintf(
                    'Vendor2%sClassName.php',
                    DIRECTORY_SEPARATOR
                )
            ),
            ClassLoader::getInstance()->getClassPath('Vendor2\ClassName')
        );
    }

    /**
     * test the load of a class file
     */
    public function testLoadOk()
    {
        $this->assertSame('Foo_test', ClassLoader::getInstance()->load('TRexTests\Loader\resources\Foo'));
    }

    /**
     * test the load of a class file when there is no such file
     *
     * @expectedException \Exception
     * @expectedExceptionMessage No file found for class TRexTests\Loader\resources\Bar with the path
     */
    public function testLoadKo()
    {
        $this->assertSame(null, ClassLoader::getInstance()->load('TRexTests\Loader\resources\Bar'));
    }

    /**
     * return the root dir for $dir
     *
     * @param $dir
     * @return string
     */
    private function getBaseDir($dir)
    {
        return $this->getFilePath($dir . DIRECTORY_SEPARATOR);
    }

    /**
     * return the path for $file
     *
     * @param $file
     * @return string
     */
    private function getFilePath($file)
    {
        return realpath(__DIR__ . '/../../../..') . DIRECTORY_SEPARATOR . $file;
    }

    /**
     * provide class paths for test
     *
     * @return array
     */
    public function provideClassPaths()
    {
        return array(
            array(
                'ClassName',
                $this->getFilePath('ClassName.php')
            ),
            array(
                '\ClassName',
                $this->getFilePath('ClassName.php')
            ),
            array(
                'Vendor\ClassName',
                $this->getFilePath(
                    sprintf(
                        'vendor%ssrc%sVendor%sClassName.php',
                        DIRECTORY_SEPARATOR,
                        DIRECTORY_SEPARATOR,
                        DIRECTORY_SEPARATOR
                    )
                )
            ),
            array(
                '\Vendor\ClassName',
                $this->getFilePath(
                    sprintf(
                        'vendor%ssrc%sVendor%sClassName.php',
                        DIRECTORY_SEPARATOR,
                        DIRECTORY_SEPARATOR,
                        DIRECTORY_SEPARATOR
                    )
                )
            ),
            array(
                'Vendor\VendorClassName',
                $this->getFilePath(
                    sprintf(
                        'vendor%ssrc%sVendor%sVendorClassName.php',
                        DIRECTORY_SEPARATOR,
                        DIRECTORY_SEPARATOR,
                        DIRECTORY_SEPARATOR
                    )
                )
            ),
            array(
                'Vendor_ClassName',
                $this->getFilePath(
                    sprintf(
                        'vendor%ssrc%sVendor%sClassName.php',
                        DIRECTORY_SEPARATOR,
                        DIRECTORY_SEPARATOR,
                        DIRECTORY_SEPARATOR
                    )
                )
            ),
            array(
                '\Vendor_ClassName',
                $this->getFilePath(
                    sprintf(
                        'vendor%ssrc%sVendor%sClassName.php',
                        DIRECTORY_SEPARATOR,
                        DIRECTORY_SEPARATOR,
                        DIRECTORY_SEPARATOR
                    )
                )
            ),
            array(
                'Vendor_VendorClassName',
                $this->getFilePath(
                    sprintf(
                        'vendor%ssrc%sVendor%sVendorClassName.php',
                        DIRECTORY_SEPARATOR,
                        DIRECTORY_SEPARATOR,
                        DIRECTORY_SEPARATOR
                    )
                )
            ),
        );
    }

}