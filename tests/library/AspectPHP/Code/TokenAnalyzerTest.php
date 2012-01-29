<?php

/**
 * AspectPHP_Code_TokenAnalyzerTest
 *
 * @author Matthias Molitor <matthias@matthimatiker.de>
 * @package AspectPHP_Code
 * @subpackage Tests
 * @copyright Matthias Molitor 2012
 * @version $Rev$
 * @since 27.01.2012
 */

/**
 * Initializes the test environment.
 */
require_once(dirname(__FILE__) . '/bootstrap.php');

/**
 * Tests the token analyzer.
 *
 * @author Matthias Molitor <matthias@matthimatiker.de>
 * @package AspectPHP_Code
 * @subpackage Tests
 * @copyright Matthias Molitor 2012
 * @version $Rev$
 * @since 27.01.2012
 */
class AspectPHP_Code_TokenAnalyzerTest extends PHPUnit_Framework_TestCase {
    
    /**
     * Checks if count() returns the number of tokens.
     */
    public function testCountReturnsNumberOfTokens() {
        $analyzer = $this->create(array('1', '2'));
        $this->assertEquals(2, count($analyzer));
    }
    
    /**
     * Enures that offsetExists() returns false if the given token index does not exist.
     */
    public function testOffsetExistsReturnsFalseIfIndexDoesNotExist() {
        $analyzer = $this->create(array('1', '2'));
        $this->assertFalse(isset($analyzer[2]));
    }
    
    /**
     * Ensures that offsetExists() returns true if the provided token index exists.
     */
    public function testOffsetExistsReturnsTrueIfExistingIndexIsProvided() {
        $analyzer = $this->create(array('1', '2'));
        $this->assertTrue(isset($analyzer[1]));
    }
    
    /**
     * Ensures that offsetGet() throws an exception if the given index does not exist.
     */
    public function testOffsetGetThrowsExceptionIfIndexDoesNotExist() {
        $this->setExpectedException('InvalidArgumentException');
        $analyzer = $this->create(array('1', '2'));
        $analyzer[2];
    }
    
    /**
     * Checks if offsetGet() returns the correct token.
     */
    public function testOffsetGetReturnsCorrectToken() {
        $analyzer = $this->create(array('1', '2'));
        $this->assertEquals('1', $analyzer[0]);
    }
    
    /**
     * Ensures that offsetSet() throws an exception.
     */
    public function testOffsetSetThrowsException() {
        $this->setExpectedException('BadMethodCallException');
        $analyzer = $this->create(array('1', '2'));
        $analyzer[0] = '3';
    }
    
    /**
     * Ensures that offsetUnset() throws an exception.
     */
    public function testOffsetUnsetThrowsException() {
        $this->setExpectedException('BadMethodCallException');
        $analyzer = $this->create(array('1', '2'));
        unset($analyzer[0]);
    }
    
    /**
     * Checks if getIterator() returns an instance of Traversable.
     */
    public function testGetIteratorReturnsTraversable() {
        $analyzer = $this->create(array('1', '2'));
        $iterator = $analyzer->getIterator();
        $this->assertInstanceOf('Traversable', $iterator);
    }
    
    /**
     * Checks if the analyzer supports an iteration over the tokens.
     */
    public function testIteratingOverTokensIsPossible() {
        $tokens   = array('1', '2');
        $analyzer = $this->create($tokens);
        foreach( $analyzer as $token ) {
            $this->assertContains($token, $tokens);
        }
    }
    
    /**
     * Ensures that the magic __toString() method returns the original source code.
     */
    public function testToStringReturnsOriginalSourceCode() {
        $source = file_get_contents(__FILE__);
        $analyzer = $this->create($source);
        $this->assertEquals($source, (string)$analyzer);
    }
    
    /**
     * Ensures that findBetween() returns -1 if no token was found.
     */
    public function testFindBetweenReturnsCorrectValueIfTokenWasNotFound() {
        $analyzer = $this->create(array('1', '2', '3'));
        $this->assertEquals(-1, $analyzer->findBetween('4', 0, 2));
    }
    
    /**
     * Ensures that findBetween() returns -1 if the search token not in the defined
     * index range.
     */
    public function testFindBetweenReturnsCorrectValueIfSearchTokenIsNotInRange() {
        $analyzer = $this->create(array('1', '2', '3'));
        $this->assertEquals(-1, $analyzer->findBetween('3', 0, 1));
    }
    
    /**
     * Checks if findBetween() returns the correct token index.
     */
    public function testFindBetweenReturnsCorrectIndex() {
        $analyzer = $this->create(array('1', '2', '3'));
        $this->assertEquals(1, $analyzer->findBetween('2', 0, 2));
    }
    
    /**
     * Checks if findBetween() searches the token at the start index.
     */
    public function testFindBetweenIncludesStartIndex() {
        $analyzer = $this->create(array('1', '2', '3'));
        $this->assertEquals(0, $analyzer->findBetween('1', 0, 2));
    }
    
    /**
     * Checks if findBetween() searches the token at the end index.
     */
    public function testFindBetweenIncludesEndIndex() {
        $analyzer = $this->create(array('1', '2', '3'));
        $this->assertEquals(2, $analyzer->findBetween('3', 0, 2));
    }
    
    /**
     * Ensures that findBetween() searches in descending order if the start index is
     * greater than the end index.
     */
    public function testFindBetweenSearchesInDescendingOrderIfStartIsGreaterThanEnd() {
        $analyzer = $this->create(array('1', '2', '3', '4', '3', '2', '1'));
        $this->assertEquals(2, $analyzer->findBetween('3', 3, 0));
    }
    
    /**
     * Ensures that findBetween() returns -1 if a stop token is encountered during search.
     */
    public function testFindBetweenReturnsCorrectValueIfStopTokenIsEncountered() {
        $analyzer = $this->create(array('1', '2', '3'));
        $this->assertEquals(-1, $analyzer->findBetween('3', 0, 2, array('2')));
    }
    
    /**
     * Ensures that findBetween() throws an exception if an invalid start index is provided.
     */
    public function testFindBetweenThrowsExceptionIfInvalidStartIndexIsProvided() {
        $this->setExpectedException('InvalidArgumentException');
        $analyzer = $this->create(array('1', '2', '3'));
        $analyzer->findBetween('2', -1, 2);
    }
    
    /**
     * Ensures that findBetween() throws an exception if an invalid end index is provided.
     */
    public function testFindBetweenThrowsExceptionIfInvalidEndIndexIsProvided() {
        $this->setExpectedException('InvalidArgumentException');
        $analyzer = $this->create(array('1', '2', '3'));
        $analyzer->findBetween('2', 0, 42);
    }
    
    // TODO findBetween() works with array tokens
    
    /**
     * Checks if findNext() returns the index of the result token.
     */
    public function testFindNextReturnsCorrectValue() {
        $analyzer = $this->create(array('1', '2', '3'));
        $this->assertEquals(2, $analyzer->findNext('3', 0));
    }
    
    /**
     * Ensures that findNext() returns -1 if no token was found.
     */
    public function testFindNextReturnsCorrectValueIfTokenWasNotFound() {
        $analyzer = $this->create(array('1', '2', '3'));
        $this->assertEquals(-1, $analyzer->findNext('4', 0));
    }
    
    /**
     * Ensures that findNext() does not search the provided start index.
     */
    public function testFindNextDoesNotIncludeStartIndex() {
        $analyzer = $this->create(array('1', '2', '3'));
        $this->assertEquals(-1, $analyzer->findNext('1', 0));
    }
    
    /**
     * Ensures that findNext() returns -1 if a stop token is found during search.
     */
    public function testFindNextReturnsCorrectValueIfStopTokenIsEncountered() {
        $analyzer = $this->create(array('1', '2', '3'));
        $this->assertEquals(-1, $analyzer->findNext('3', 0, array('2')));
    }
    
    /**
     * Ensures that findNext() throws an exception if an invalid index is provided.
     */
    public function testFindNextThrowsExceptionIfInvalidIndexIsProvided() {
        $this->setExpectedException('InvalidArgumentException');
        $analyzer = $this->create(array('1', '2', '3'));
        $analyzer->findNext('5', -1);
    }
    
    // TODO findNext() does not search before offset
    // TODO findNext() first match
    
    /**
     * Checks if findPrevious() returns the index of the result token.
     */
    public function testFindPreviousReturnsCorrectValue() {
        $analyzer = $this->create(array('1', '2', '3'));
        $this->assertEquals(0, $analyzer->findPrevious('1', 2));
    }
    
    /**
     * Ensures that findPrevious() returns -1 if no token was found.
     */
    public function testFindPreviousReturnsCorrectValueIfTokenWasNotFound() {
        $analyzer = $this->create(array('1', '2', '3'));
        $this->assertEquals(-1, $analyzer->findPrevious('4', 2));
    }
    
    /**
     * Ensures that findPrevious() does not search the provided start index.
     */
    public function testFindPreviousDoesNotIncludeStartIndex() {
        $analyzer = $this->create(array('1', '2', '3'));
        $this->assertEquals(-1, $analyzer->findPrevious('3', 2));
    }
    
    /**
     * Ensures that findPrevious() returns -1 if a stop token is found during search.
     */
    public function testFindPreviousReturnsCorrectValueIfStopTokenIsEncountered() {
        $analyzer = $this->create(array('1', '2', '3'));
        $this->assertEquals(-1, $analyzer->findPrevious('1', 2, array('2')));
    }
    
    /**
     * Ensures that findPrevious() throws an exception if an invalid index is provided.
     */
    public function testFindPreviousThrowsExceptionIfInvalidIndexIsProvided() {
        $this->setExpectedException('InvalidArgumentException');
        $analyzer = $this->create(array('1', '2', '3'));
        $analyzer->findPrevious('2', 3);
    }
    
    // TODO findPrevious() does not search after offset
    // TODO findPrevious() first match
    
    /**
     * Ensures that findMatchingBrace() returns the correct token index if the
     * braces are not nested.
     */
    public function testFindMatchingBraceReturnsCorrectIndexIfBracesAreNotNested() {
        
    }
    
    /**
     * Ensures that findMatchingBrace() returns the correct token index if the
     * braces are nested.
     */
    public function testFindMatchingBraceReturnsCorrectIndexIfBracesAreNested() {
        
    }
    
    /**
     * Checks if findMatchingBrace() distinguishes between the different brace
     * types and returns the correct closing brace.
     */
    public function testFindMatchingBraceReturnsCorrectClosingBrace() {
        
    }
    
    /**
     * Checks if findMatchingBrace() returns the correct opening brace if the index
     * of a closing brace is provided.
     */
    public function testFindMatchingBraceReturnsCorrectOpeningBrace() {
        
    }
    
    /**
     * Checks if findMatchingBrace() supports parentheses ("(" and ")").
     */
    public function testFindMatchingBraceSupportsParentheses() {
        
    }
    
    /**
     * Ensures that findMatchingBrace() throws an exception if an invalid index
     * is provided.
     */
    public function testFindMatchingBraceThrowsExceptionIfInvalidIndexIsProvided() {
        
    }
    
    /**
     * Ensures that findMatchingBrace() throws an exception if the provided index
     * does not belong to a brace token.
     */
    public function testFindMatchingBraceThrowsExceptionIfNoBraceIndexIsProvided() {
        
    }
    
    /**
     * Ensures that findMatchingBrace() throws an exception if no matching brace
     * was found.
     * No matching braces may be explained by incorrect source code.
     */
    public function testFindMatchingBraceThrowsExceptionIfNoMatchingBraceWasFound() {
        
    }
    
    /**
     * Creates an analyzer that works with the provided tokens.
     *
     * @param array(string|array)|string $tokensOrSource
     * @return AspectPHP_Code_TokenAnalyzer
     */
    protected function create($tokensOrSource) {
        if( is_array($tokensOrSource) ) {
            $tokens = $tokensOrSource;
        } else {
            $tokens = token_get_all($tokensOrSource);
        }
        return new AspectPHP_Code_TokenAnalyzer($tokens);
    }
    
}

?>