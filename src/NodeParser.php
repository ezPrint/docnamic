<?php

namespace Docnamic;

use DOMNode;

/**
 * Parser implementation processing placeholder token in DOM nodes
 */
class NodeParser
{
    const LEFT_DELIMITER  = '${';
    const RIGHT_DELIMITER = '}';

    private $expressionPattern;

    public function __construct()
    {
        $this->expressionPattern = sprintf(
            '/%s.*?%s/i',
            preg_quote(self::LEFT_DELIMITER),
            preg_quote(self::RIGHT_DELIMITER)
        );
    }

    /**
     * @param DOMNode $node
     * @return array
     */
    public function parse(DOMNode $node)
    {
        $matches = [];
        preg_match_all($this->expressionPattern, $node->nodeValue, $matches);
        $matches = $matches[0];
        foreach ($matches as &$value) {
            $search = [self::LEFT_DELIMITER, self::RIGHT_DELIMITER];
            $value  = str_replace($search, '', $value);
        }
        return $matches;
    }

    /**
     * @param DOMNode $node
     * @param array   $tokens
     */
    public function replaceTokens(DOMNode $node, array $tokens)
    {
        $search  = [];
        $replace = [];

        foreach ($tokens as $key => $value) {
            if (strpos($value, PHP_EOL) !== false) {
                $paragraphs = explode(PHP_EOL, $value);

                foreach ($paragraphs as $paragraph) {
                    $clone = $node->parentNode->cloneNode(true);
                    $clone->nodeValue = $paragraph;

                    $node->parentNode->parentNode->insertBefore($clone, $node->parentNode);
                }
            } else {
                $search[] = self::LEFT_DELIMITER . $key . self::RIGHT_DELIMITER;
                $replace[] = $value;
            }
        }

        $node->nodeValue = str_replace($search, $replace, $node->nodeValue);
    }
}
