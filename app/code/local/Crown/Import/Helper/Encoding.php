<?php
/**
 * 
 * @category Crown
 * @package Crown_Import
 * @since 1.0.1
 */
class Crown_Import_Helper_Encoding extends Mage_Core_Helper_Abstract{
	
	protected static $win1252ToUtf8 = array (
		128 => "\xe2\x82\xac", 
		130 => "\xe2\x80\x9a", 
		131 => "\xc6\x92", 
		132 => "\xe2\x80\x9e", 
		133 => "\xe2\x80\xa6", 
		134 => "\xe2\x80\xa0", 
		135 => "\xe2\x80\xa1", 
		136 => "\xcb\x86", 
		137 => "\xe2\x80\xb0", 
		138 => "\xc5\xa0", 
		139 => "\xe2\x80\xb9", 
		140 => "\xc5\x92", 
		142 => "\xc5\xbd", 
		145 => "\xe2\x80\x98", 
		146 => "\xe2\x80\x99", 
		147 => "\xe2\x80\x9c", 
		148 => "\xe2\x80\x9d", 
		149 => "\xe2\x80\xa2", 
		150 => "\xe2\x80\x93", 
		151 => "\xe2\x80\x94", 
		152 => "\xcb\x9c", 
		153 => "\xe2\x84\xa2", 
		154 => "\xc5\xa1", 
		155 => "\xe2\x80\xba", 
		156 => "\xc5\x93", 
		158 => "\xc5\xbe", 
		159 => "\xc5\xb8" 
	);
	
	/**
	 * Function Encoding::toUTF8
	 *
	 * This function leaves UTF8 characters alone, while converting almost all non-UTF8 to UTF8.
	 * 
	 * It assumes that the encoding of the original string is either Windows-1252 or ISO 8859-1.
	 *
	 * It may fail to convert characters to UTF-8 if they fall into one of these scenarios:
	 *
	 * 1) when any of these characters:
	 * 	ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞß
	 * 	are followed by any of these:  ("group B")
	 * 	¡¢£¤¥¦§¨©ª«¬­®¯°±²³´µ¶•¸¹º»¼½¾¿
	 * For example:   %ABREPRESENT%C9%BB. «REPRESENTÉ»
	 * The "«" (%AB) character will be converted, but the "É" followed by "»" (%C9%BB) 
	 * is also a valid unicode character, and will be left unchanged.
	 *
	 * 2) when any of these: àáâãäåæçèéêëìíîï  are followed by TWO chars from group B,
	 * 3) when any of these: ðñòó  are followed by THREE chars from group B.
	 *
	 * @since 1.0.1
	 * @param string $text  Any string.
	 * @return string  The same string, UTF8 encoded
	 */
	public function toUTF8($text) {
		
		if (is_array ( $text )) {
			foreach ( $text as $k => $v ) {
				$text [$k] = self::toUTF8 ( $v );
			}
			return $text;
		} elseif (is_string ( $text )) {
			
			$max = strlen ( $text );
			$buf = "";
			for($i = 0; $i < $max; $i ++) {
				$c1 = $text {$i};
				if ($c1 >= "\xc0") {
					$c2 = $i + 1 >= $max ? "\x00" : $text {$i + 1};
					$c3 = $i + 2 >= $max ? "\x00" : $text {$i + 2};
					$c4 = $i + 3 >= $max ? "\x00" : $text {$i + 3};
					if ($c1 >= "\xc0" & $c1 <= "\xdf") {
						if ($c2 >= "\x80" && $c2 <= "\xbf") {
							$buf .= $c1 . $c2;
							$i ++;
						} else {
							$cc1 = (chr ( ord ( $c1 ) / 64 ) | "\xc0");
							$cc2 = ($c1 & "\x3f") | "\x80";
							$buf .= $cc1 . $cc2;
						}
					} elseif ($c1 >= "\xe0" & $c1 <= "\xef") {
						if ($c2 >= "\x80" && $c2 <= "\xbf" && $c3 >= "\x80" && $c3 <= "\xbf") {
							$buf .= $c1 . $c2 . $c3;
							$i = $i + 2;
						} else {
							$cc1 = (chr ( ord ( $c1 ) / 64 ) | "\xc0");
							$cc2 = ($c1 & "\x3f") | "\x80";
							$buf .= $cc1 . $cc2;
						}
					} elseif ($c1 >= "\xf0" & $c1 <= "\xf7") {
						if ($c2 >= "\x80" && $c2 <= "\xbf" && $c3 >= "\x80" && $c3 <= "\xbf" && $c4 >= "\x80" && $c4 <= "\xbf") {
							$buf .= $c1 . $c2 . $c3;
							$i = $i + 2;
						} else {
							$cc1 = (chr ( ord ( $c1 ) / 64 ) | "\xc0");
							$cc2 = ($c1 & "\x3f") | "\x80";
							$buf .= $cc1 . $cc2;
						}
					} else {
						$cc1 = (chr ( ord ( $c1 ) / 64 ) | "\xc0");
						$cc2 = (($c1 & "\x3f") | "\x80");
						$buf .= $cc1 . $cc2;
					}
				} elseif (($c1 & "\xc0") == "\x80") {
					if (isset ( self::$win1252ToUtf8 [ord ( $c1 )] )) {
						$buf .= self::$win1252ToUtf8 [ord ( $c1 )];
					} else {
						$cc1 = (chr ( ord ( $c1 ) / 64 ) | "\xc0");
						$cc2 = (($c1 & "\x3f") | "\x80");
						$buf .= $cc1 . $cc2;
					}
				} else {
					$buf .= $c1;
				}
			}
			return $buf;
		} else {
			return $text;
		}
	}
}