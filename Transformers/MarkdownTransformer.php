<?php

namespace Modules\ClickupIntegration\Transformers;

use League\HTMLToMarkdown\ElementInterface;
use League\HTMLToMarkdown\HtmlConverter;
use Modules\ClickupIntegration\Transformers\Converters\ImageConverter;

/**
 * Custom converter class to proxy HTML to Markdown conversions
 */
class MarkdownTransformer extends HtmlConverter
{
    /**
     * Constructs a new instance
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct([
            'strip_tags' => true
        ] + $options);
    }

    /**
     * Intercepts markdown conversion to add support for base64 image transformation
     *
     * @param ElementInterface $element
     *
     * @return string
     */
    protected function convertToMarkdown(ElementInterface $element): string
    {
        $tag = $element->getTagName();

        switch ($tag) {
            case 'img':
                $markdown = $element->isDescendantOf('a')
                            # Nested images in link won't be allowed
                            ? ''
                            # Custom markdown images to use base64 encoded string
                            : (new ImageConverter())->convert($element);
                break;
            case 'a':
                # Cleaning encoded urls for proper markdown it
                $markdown = html_entity_decode($element->getValue());
                break;
            default:
                # Other elements will transform as usual
                $markdown = parent::convertToMarkdown($element);
        }

        return $markdown;
    }
}
