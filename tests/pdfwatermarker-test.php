<?php

$parent_directory = dirname(__FILE__);

require_once($parent_directory . "/../vendor/setasign/fpdf/fpdf.php");
require_once($parent_directory . "/../vendor/setasign/fpdi/fpdi.php");
require_once($parent_directory . "/../pdfwatermarker/pdfwatermarker.php");
require_once($parent_directory . "/../pdfwatermarker/pdfwatermark.php");

/**
 * Class PDFWatermarker_test
 */
class PDFWatermarker_test extends PHPUnit_Framework_TestCase
{
    /** @var PDFWatermark */
    public $watermark;

    /** @var PDFWatermarker */
    public $watermarker;

    /** @var string */
    public $outputFile;

    /** @var string */
    public $parentDirectory;

    /**
     * Set to true in case you want to regenerate reference output files
     *
     * @var bool
     */
    protected $createReference = false;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->parentDirectory = dirname(__FILE__);
    }

    /**
     * @inheritdoc
     */
    public function tearDown()
    {
        parent::tearDown();

        if (!$this->createReference) {
            if (file_exists($this->outputFile)) {
                unlink($this->outputFile);
            }
        }
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            ['output-default-position', null],
            ['output-from-jpg', null, 'jpg'],
            ['output-as-background', null, 'png', true],
            ['output-specific-pages', null, 'png', false, [3, 5]],
            ['output-topright-position', 'topright'],
            ['output-topleft-position', 'topleft'],
            ['output-bottomright-position', 'bottomright'],
            ['output-bottomleft-position', 'bottomleft'],
            ['output-custom-position', [50, 100]],
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param        $filename
     * @param        $position
     * @param string $watermarkType
     * @param bool   $asBackground
     * @param array  $pageRange
     */
    public function testWatermark($filename, $position, $watermarkType = 'png', $asBackground = false, $pageRange = [])
    {
        $reference = $this->parentDirectory . '/../assets/' . $filename . '.pdf';
        $inputFile = $this->parentDirectory . '/../assets/test' . (count($pageRange) ? '-multipage' : '') . '.pdf';
        $this->outputFile = $this->parentDirectory . '/../assets/test-output.pdf';

        if ($this->createReference) {
            $this->outputFile = $reference;
            if (file_exists($reference)) {
                unlink($reference);
            }
        }

        $watermark = new PDFWatermark($this->parentDirectory . '/../assets/star.' . $watermarkType);
        if ($asBackground) {
            $watermark->setAsBackground();
        }
        if (!is_null($position)) {
            $watermark->setPosition($position);
        }
        $waterMarker = new PDFWatermarker($inputFile, $this->outputFile, $watermark);
        if (count($pageRange)) {
            $waterMarker->setPageRange($pageRange[0], $pageRange[1]);
        }
        $waterMarker->savePdf();

        $this->assertTrue(file_exists($this->outputFile) === true);

        if (!$this->createReference) {
            $this->assertTrue(filesize($reference) === filesize($this->outputFile));
        }
    }
}