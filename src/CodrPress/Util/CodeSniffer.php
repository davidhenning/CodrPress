<?php

namespace CodrPress\Util;

class CodeSniffer {

    public function __construct() {

    }

    public function scan($text) {
        $text = preg_replace_callback('{
            (?:\n|\A)
            # 1: Opening marker
            (
                ~{3,}|`{3,} # Marker: three tilde or more.
            )

            [ ]?(\w+)?(?:,[ ]?(\d+))?[ ]* \n # Whitespace and newline following marker.

            # 3: Content
            (
                (?>
                    (?!\1 [ ]* \n)	# Not a closing marker.
                    .*\n+
                )+
            )

            # Closing marker.
            \1 [ ]* \n
        }xm',
        array($this, '_transform'), $text);

        return $text;
    }

    protected function _transform($matches) {
        $codeblock = $matches[4];
        $codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);
        $codeblock = preg_replace_callback('/^\n+/',
            array(&$this, '_doFencedCodeBlocks_newlines'), $codeblock);
        //$codeblock = "<pre><code>$codeblock</code></pre>";
        //$cb = "<pre><code";
        $cb = empty($matches[3]) ? "<pre><code" : "<pre class=\"linenums:$matches[3]\"><code";
        $cb .= empty($matches[2]) ? ">" : " class=\"language-$matches[2]\">";
        $cb .= "$codeblock</code></pre>";

        return $cb;
    }
}
