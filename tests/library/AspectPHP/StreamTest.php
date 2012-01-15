<?php

/**
 * AspectPHP_StreamTest
 *
 * @author Matthias Molitor <matthias@matthimatiker.de>
 * @package AspectPHP_Stream
 * @subpackage Tests
 * @copyright Matthias Molitor 2011
 * @version $Rev$
 * @since 13.12.2011
 */

/**
 * Initializes the test environment.
 */
require_once(dirname(__FILE__) . '/bootstrap.php');

/**
 * Tests the AspectPHP stream.
 *
 * @author Matthias Molitor <matthias@matthimatiker.de>
 * @package AspectPHP_Stream
 * @subpackage Tests
 * @copyright Matthias Molitor 2011
 * @version $Rev$
 * @since 13.12.2011
 */
class AspectPHP_StreamTest extends PHPUnit_Framework_TestCase {
    
    /**
     * The original include path.
     *
     * @var string
     */
    private $previousIncludePath = null;
    
    /**
     * See {@link PHPUnit_Framework_TestCase::setUp()} for details.
     */
    protected function setUp() {
        parent::setUp();
        $this->storeIncludePath();
        $this->resetManager();
        AspectPHP_Stream::register();
    }
    
    /**
     * See {@link PHPUnit_Framework_TestCase::tearDown()} for details.
     */
    protected function tearDown() {
        AspectPHP_Stream::unregister();
        $this->resetManager();
        $this->restoreIncludePath();
        parent::tearDown();
    }
    
    /**
     * Ensures that isRegistered() returns true if the stream is already registered.
     */
    public function testIsRegisteredReturnsTrueIfStreamIsAlreadyRegistered() {
        $this->assertTrue(AspectPHP_Stream::isRegistered());
    }
    
    /**
     * Ensures that isRegistered() returns false if the stream is not registered.
     */
    public function testIsRegisteredReturnsFalseIfStreamIsNotRegistered() {
        AspectPHP_Stream::unregister();
        $this->assertFalse(AspectPHP_Stream::isRegistered());
    }
    
    /**
     * Checks if register() registers the stream.
     */
    public function testRegisterRegistersStream() {
        $streams = stream_get_wrappers();
        $this->assertContains(AspectPHP_Stream::NAME, $streams);
    }
    
    /**
     * Ensures that register() does nothing if the stream is already registered.
     */
    public function testRegisterDoesNothingIfStreamIsAlreadyRegistered() {
        $this->setExpectedException(null);
        AspectPHP_Stream::register();
    }
    
    /**
     * Checks if unregister() removes the registered stream.
     */
    public function testUnregisterRemovesRegisteredStream() {
        AspectPHP_Stream::unregister();
        $streams = stream_get_wrappers();
        $this->assertNotContains(AspectPHP_Stream::NAME, $streams);
    }
    
    /**
     * Ensures that unregister() does nothing if the stream is not registered.
     */
    public function testUnregisterDoesNothingIfStreamIsNotRegistered() {
        $this->setExpectedException(null);
        AspectPHP_Stream::unregister();
        AspectPHP_Stream::unregister();
    }
    
    /**
     * Ensures that is_file() returns true if the given path points
     * to a existing file.
     */
    public function testIsFileReturnsTrueIfFileExists() {
        $path = $this->toStream($this->getPath('StreamCheck/Io.php'));
        $this->assertTrue(is_file($path));
    }
    
    /**
     * Ensures that is_file() returns false if the given path points
     * to a not existing file.
     */
    public function testIsFileReturnsFalseIfFileDoesNotExist() {
        $path = $this->toStream($this->getPath('Missing.php'));
        $this->assertFalse(is_file($path));
    }
    
    /**
     * Checks if stat() returns valid data about the given file.
     */
    public function testStatProvidesRequiredMetaData() {
        $path = $this->toStream($this->getPath('StreamCheck/Io.php'));
        $data = stat($path);
        $this->assertInternalType('array', $data);
        // Check the required numerical and associative keys.
        $this->assertArrayHasKey(0, $data);
        $this->assertArrayHasKey('dev', $data);
        $this->assertArrayHasKey(1, $data);
        $this->assertArrayHasKey('ino', $data);
        $this->assertArrayHasKey(2, $data);
        $this->assertArrayHasKey('mode', $data);
        $this->assertArrayHasKey(3, $data);
        $this->assertArrayHasKey('nlink', $data);
        $this->assertArrayHasKey(4, $data);
        $this->assertArrayHasKey('uid', $data);
        $this->assertArrayHasKey(5, $data);
        $this->assertArrayHasKey('gid', $data);
        $this->assertArrayHasKey(6, $data);
        $this->assertArrayHasKey('rdev', $data);
        $this->assertArrayHasKey(7, $data);
        $this->assertArrayHasKey('size', $data);
        $this->assertArrayHasKey(8, $data);
        $this->assertArrayHasKey('atime', $data);
        $this->assertArrayHasKey(9, $data);
        $this->assertArrayHasKey('mtime', $data);
        $this->assertArrayHasKey(10, $data);
        $this->assertArrayHasKey('ctime', $data);
        $this->assertArrayHasKey(11, $data);
        $this->assertArrayHasKey('blksize', $data);
        $this->assertArrayHasKey(12, $data);
        $this->assertArrayHasKey('blocks', $data);
    }
    
    /**
     * Ensures that is_writable() returns always false.
     */
    public function testIsWritableReturnsFalse() {
        $path = $this->toStream($this->getPath('StreamCheck/Io.php'));
        $this->assertFalse(is_writable($path));
    }
    
    /**
     * Ensures that the stream cannot be used to modify files.
     */
    public function testStreamDoesNotAllowModifyingFiles() {
        $path = $this->getPath('WriteTest.txt');
        // Ensures that the test file is empty.
        file_put_contents($path, '');
        // Suppress notices, otherwise PHPUnit would convert them to exceptions
        // and stop test execution.
        @file_put_contents($this->toStream($path), 'This should not be added.');
        $this->assertEquals(0, filesize($path), 'File was modified.');
    }
    
    /**
     * Checks if filesize() returns a valid value.
     */
    public function testStreamProvidesValidFileSize() {
        $original = $this->getPath('StreamCheck/Size.php');
        $stream   = $this->toStream($original);
        // The stream adds code, therefore the filesize should increase compared to the original data.
        $this->assertGreaterThan(filesize($original), filesize($stream), 'Invalid filesize provided.');
    }
    
    /**
     * Checks if the stream modifies the loaded code.
     */
    public function testStreamModifiesLoadedCode() {
        $path       = $this->getPath('StreamCheck/Io.php');
        $original   = file_get_contents($path);
        $fromStream = file_get_contents($this->toStream($path));
        $this->assertNotEquals($original, $fromStream);
    }
    
    /**
     * Checks if the stream generates valid PHP code.
     */
    public function testStreamGeneratesValidCode() {
        $this->setExpectedException(null);
        $path = $this->getPath('StreamCheck/Modification/Valid.php');
        // If invalid code is generated then the script will stop or
        // an error or notice will be thrown.
        include($this->toStream($path));
    }
    
    /**
     * Ensures that the stream can be used to load a file via include()
     * if the full file path is passed.
     */
    public function testStreamCanBeUsedToIncludeFileByFullPath() {
        $path = $this->getPath('StreamCheck/Include/FullPath.php');
        include($this->toStream($path));
        $this->assertClassExists('StreamCheck_Include_FullPath');
    }
    
    /**
     * Ensures that the stream can be used to load files via include()
     * by using the include path.
     */
    public function testStreamCanBeUsedToIncludeFileFromIncludePath() {
        $this->changeIncludePath();
        include('StreamCheck/Include/RelativePath.php');
        $this->assertClassExists('StreamCheck_Include_RelativePath');
    }
    
    /**
     * Ensures that the stream does not change the original method names.
     */
    public function testStreamDoesNotChangeOriginalMethodNames() {
        $path = $this->getPath('StreamCheck/Modification/MethodNames.php');
        include($this->toStream($path));
        $this->assertHasMethod('StreamCheck_Modification_MethodNames', 'customMethod');
    }
    
    /**
     * Ensures that the stream does not modify the visibility of the original
     * methods.
     */
    public function testStreamDoesNotChangeMethodVisibility() {
        $path = $this->getPath('StreamCheck/Modification/Visibility.php');
        include($this->toStream($path));
        $class = 'StreamCheck_Modification_Visibility';
        
        $this->assertHasMethod($class, 'myPrivateMethod');
        $privateMethod = new ReflectionMethod($class,  'myPrivateMethod');
        $this->assertTrue($privateMethod->isPrivate(), 'Method is not private anymore.');
        
        $this->assertHasMethod($class, 'myProtectedMethod');
        $privateMethod = new ReflectionMethod($class,  'myProtectedMethod');
        $this->assertTrue($privateMethod->isProtected(), 'Method is not protected anymore.');
        
        $this->assertHasMethod($class, 'myPublicMethod');
        $privateMethod = new ReflectionMethod($class, 'myPublicMethod');
        $this->assertTrue($privateMethod->isPublic(), 'Method is not public anymore.');
    }
    
    /**
     * Ensures that static methods remain static.
     */
    public function testStreamDoesNotRemoveStaticAttributeFromMethods() {
        $path = $this->getPath('StreamCheck/Modification/Static.php');
        include($this->toStream($path));
        $class = 'StreamCheck_Modification_Static';
        
        $this->assertHasMethod($class, 'myStaticMethod');
        $privateMethod = new ReflectionMethod($class,  'myStaticMethod');
        $this->assertTrue($privateMethod->isStatic(), 'Method is not static anymore.');
    }
    
    /**
     * Ensures that the stream does not suppress errors if invalid
     * code is loaded.
     */
    public function testStreamDoesNotSuppressErrors() {
        $this->setExpectedException('PHPUnit_Framework_Error_Notice');
        $path = $this->getPath('StreamCheck/Modification/Notice.php');
        include($this->toStream($path));
    }
    
    /**
     * Ensures that the stream does not modify source code that
     * contains (compile) errors.
     */
    public function testStreamDoesNotModifyCodeWithErrors() {
        $path       = $this->getPath('StreamCheck/Modification/CompileError.txt');
        $original   = file_get_contents($path);
        $fromStream = file_get_contents($this->toStream($path));
        $this->assertEquals($original, $fromStream, 'Stream modified broken code.');
    }
    
    /**
     * Ensures that the __FILE__ constant works correctly in loaded
     * classes.
     */
    public function testFileConstantIsUsableInLoadedFiles() {
        $path = $this->getPath('StreamCheck/FileConstant.php');
        include($this->toStream($path));
        $this->assertClassExists('StreamCheck_FileConstant');
        $check = new StreamCheck_FileConstant();
        $value = $check->getFileConstant();
        $message = '__FILE__ returned an invalid value: ' . $value;
        $this->assertEquals(realpath($path), realpath($value), $message);
    }
    
    /**
     * Ensures that the stream does not modify files that do not contain classes.
     */
    public function testStreamDoesNotModifyFilesThatDoNotContainClasses() {
        $path = $this->getPath('StreamCheck/Modification/NoClass.php');
        $original   = file_get_contents($path);
        $fromStream = file_get_contents($this->toStream($path));
        $this->assertEquals($original, $fromStream, 'Stream modified file without class.');
    }
    
    /**
     * Enusres that the stream does not modify the line numbers of the original code.
     */
    public function testStreamDoesNotChangeLineNumbers() {
        // Ensure that the test fails if the exception is not thrown.
        $this->setExpectedException('RuntimeException');
        include($this->toStream($this->getPath('StreamCheck/Modification/LineNumber.php')));
        $this->assertClassExists('StreamCheck_Modification_LineNumber');
        try {
            $check = new StreamCheck_Modification_LineNumber();
            $check->lineNumber();
        } catch(RuntimeException $e) {
            $this->assertEquals(29, $e->getLine(), 'Stream changed line numbers.');
            throw $e;
        }
    }
    
    /**
     * Ensures that getManager() throws an exception if no manager is available.
     */
    public function testGetManagerThrowsExceptionIfNoManagerIsAvailable() {
        $this->setExpectedException('BadMethodCallException');
        AspectPHP_Stream::getManager();
    }
    
    /**
     * Checks if getManager() returns the provided aspect manager.
     */
    public function testGetManagerReturnsProvidedManager() {
        $manager = $this->createManager();
        AspectPHP_Stream::setManager($manager);
        $this->assertSame($manager, AspectPHP_Stream::getManager());
    }
    
    /**
     * Ensures that setManager() throws an exception if an invalid argument is passed.
     */
    public function testSetManagerThrowsExceptionIfInvalidArgumentIsProvided() {
        $this->setExpectedException('InvalidArgumentException');
        AspectPHP_Stream::setManager(new stdClass());
    }
    
    /**
     * Ensures that hasManager() returns false if no aspect manager is available.
     */
    public function testHasManagerReturnsFalseIfNoManagerIsAvailable() {
        $this->assertFalse(AspectPHP_Stream::hasManager());
    }
    
    /**
     * Ensures that hasManager() returns true if an aspect manager is available.
     */
    public function testHasManagerReturnsTrueIfManagerIsAvailable() {
        AspectPHP_Stream::setManager($this->createManager());
        $this->assertTrue(AspectPHP_Stream::hasManager());
    }
    
    /**
     * Asserts that the class $class has the method with the provided name.
     *
     * @param string $class
     * @param string $method
     */
    protected function assertHasMethod($class, $method) {
        $this->assertClassExists($class);
        $refelection = new ReflectionClass($class);
        $message     = 'Class "' . $class . '" does not provide the method "' . $method . '".';
        $this->assertTrue($refelection->hasMethod($method), $message);
    }
    
    /**
     * Asserts that the class with the provided name was loaded.
     *
     * @param string $class
     */
    protected function assertClassExists($class) {
        $message = 'The class "' . $class . '" was not loaded.';
        $this->assertTrue(class_exists($class, false), $message);
    }
    
    /**
     * Creates an aspect manager.
     *
     * @return AspectPHP_Manager
     */
    protected function createManager() {
        return $this->getMock('AspectPHP_Manager');
    }
    
    /**
     * Returns the path to the test file with the given name.
     *
     * Example:
     * <code>
     * $path = $this->getPath('test.php');
     * </code>
     *
     * Test files are located in the TestData directory.
     * The method does not check if the requested file exists.
     *
     * @param string $testFile
     * @return string
     */
    protected function getPath($testFile) {
        return $this->getTestDataDirectory() . '/' . $testFile;
    }
    
    /**
     * Returns the path to the test data directory.
     *
     * @return string
     */
    protected function getTestDataDirectory() {
        return dirname(__FILE__) . '/TestData';
    }
    
    /**
     * Adds the stream identifier to the given path.
     *
     * Example:
     * <code>
     * // Returns "aspectphp://path/to/file".
     * $path = $this->toStream('path/to/file');
     * </code>
     *
     * @param string $path
     * @return string
     */
    protected function toStream($path) {
        return AspectPHP_Stream::NAME . '://' . $path;
    }
    
    /**
     * Stores the current include path.
     */
    protected function storeIncludePath() {
        $this->previousIncludePath = get_include_path();
    }
    
    /**
     * Restores the include path that was saved by storeIncludePath().
     */
    protected function restoreIncludePath() {
        set_include_path($this->previousIncludePath);
    }
    
    /**
     * Sets the include path to the test data directory and uses the
     * stream to load data from that path.
     */
    protected function changeIncludePath() {
        set_include_path($this->toStream($this->getTestDataDirectory()));
    }
    
    /**
     * Resets the aspect manager.
     */
    protected function resetManager() {
        AspectPHP_Stream::setManager(null);
    }
    
}

?>