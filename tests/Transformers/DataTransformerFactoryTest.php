<?php

use PHPUnit\Framework\TestCase;
use MarcL\Transformers\ArrayTransformer;
use MarcL\Transformers\JsonTransformer;
use MarcL\Transformers\SimpleArrayTransformer;
use MarcL\Transformers\XmlTransformer;
use MarcL\Transformers\DataTransformerFactory;

class DataTransformerFactoryTest extends TestCase {
    public function testShouldReturnArrayTransformerWhenArrayPassed() {
		$dataTransformer = DataTransformerFactory::create('array');

        $this->assertInstanceOf(ArrayTransformer::class, $dataTransformer);
    }

    public function testShouldReturnJsonTransformerWhenJsonPassed() {
		$dataTransformer = DataTransformerFactory::create('json');

        $this->assertInstanceOf(JsonTransformer::class, $dataTransformer);
    }

    public function testShouldReturnSimpleArrayTransformerWhenSimplePassed() {
		$dataTransformer = DataTransformerFactory::create('simple');

        $this->assertInstanceOf(SimpleArrayTransformer::class, $dataTransformer);
    }

    public function testShouldReturnXmlTransformerWhenXmlPassed() {
		$dataTransformer = DataTransformerFactory::create('xml');

        $this->assertInstanceOf(XmlTransformer::class, $dataTransformer);
    }

    public function testShouldReturnXmlTransformerAsDefault() {
		$dataTransformer = DataTransformerFactory::create('unknown');

        $this->assertInstanceOf(XmlTransformer::class, $dataTransformer);
    }
}
?>
