<?php

namespace Modules\ClickupIntegration\Transformers\Converters;

use League\HTMLToMarkdown\Converter\ImageConverter as BaseImageConverter;
use League\HTMLToMarkdown\ElementInterface;
use Exception;

class ImageConverter extends BaseImageConverter
{
    /**
     * Transforms an Image element to a Markdown representation in base64 instead of url
     *
     * @param ElementInterface $element
     * @return string
     */
    public function convert(ElementInterface $element): string
    {
        try {
            $base64 = $this->srcToBase64($element->getAttribute('src'));
        } catch (Exception) {
            $base64 = '';
        }

        return "![]({$base64})";
    }

    /**
     * Converts a image url to a base64 string representation for an image
     *
     * @param string $src
     * @return string
     */
    private function srcToBase64($src)
    {
        # Checking if the source comes from storage to retrieve its content
        if (strpos($src, 'storage/attachment') !== false) {
            $filePath   = join('/', ['app', strstr(parse_url($src, PHP_URL_PATH), 'attachment')]);
            $src        = storage_path($filePath);
        }

        $destination    = $this->compressImage($src);
        $base64         = base64_encode(file_get_contents($destination));

        return "data:image/jpeg;base64,{$base64}";
    }

    /**
     * Compresses an image using GD and returns the path to the output file
     *
     * Retrieves:
     * > $destination: Path to the file/url where contents will be read
     *
     * @param string $source
     * @param integer $quality
     *
     * @return string
     */
    private function compressImage($source, $quality = 50)
    {
        $image = imagecreatefromstring(file_get_contents($source));
        $destination = tempnam(sys_get_temp_dir(), 'fs_tmp_image');

        # Optimizes and store on new destination, then frees memory
        imagejpeg($image, $destination, $quality);
        imagedestroy($image);

        return $destination;
    }
}
