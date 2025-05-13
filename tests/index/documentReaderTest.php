<?php
namespace Tests\Index;

use PHPUnit\Framework\TestCase;
use App\util\documentReader;

/**
 * @coversDefaultClass \App\util\documentReader
 */
class DocumentReaderTest extends TestCase
{

    
    protected $reader;

    protected function setUp(): void
    {
        $this->reader = new documentReader(__DIR__ . '/../fixtures/documents');
    }
    /**
     * @covers ::getAll
     */
    public function testGetAllReturnsExpectedContent()
    {
        $generator = $this->reader->getAll();
        $results = iterator_to_array($generator);
        
        $this->assertNotEmpty($results, 'No documents found. Check if:'
            . PHP_EOL . '- Documents directory exists at: ' . realpath(__DIR__ . '/../../documents')
            . PHP_EOL . '- Crawler has been run first'
            . PHP_EOL . '- Files have valid serialized data');
        
        foreach ($results as $item) {
            
            $this->assertIsArray($item);
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('url', $item);
            $this->assertArrayHasKey('body', $item);
            
            
            $this->assertTrue($this->validateDocumentEntry($item), 
                "Invalid document structure: " . print_r($item, true));
        }
    }


private function validateDocumentEntry(array $doc): bool
    {
        return isset($doc['id'], $doc['url'], $doc['body'])
            && is_int($doc['id'])
            && is_string($doc['url'])
            && is_string($doc['body']);
    }
}