<?php
namespace Czim\FileHandling\Variant\Strategies;

use Czim\FileHandling\Support\Image\Optimizer;
use SplFileInfo;

class ImageOptimizeStrategy extends AbstractImageStrategy
{

    /**
     * @var imageOptimizer
     */
    protected $imageOptimizer;


    /**
     * @param Optimizer $optimizer
     */
    public function __construct(Optimizer $optimizer)
    {
        $this->optimizer = $optimizer;
    }


    /**
     * Performs manipulation of the file.
     *
     * @return bool
     */
    protected function perform()
    {
        $spl = new SplFileInfo($this->file->path());

        return (bool) $this->optimizer->optimize($spl, $this->options);
    }

}
