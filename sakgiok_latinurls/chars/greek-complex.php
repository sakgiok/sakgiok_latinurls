<?php

/** Copyright 2019 Sakis Gkiokas
 * This file is part of sakgiok_latinurls module for Prestashop.
 *
 * Sakgiok_latinurls is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Sakgiok_latinurls is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * For any recommendations and/or suggestions please contact me
 * at sakgiok@gmail.com
 *
 *  @author    Sakis Gkiokas <sakgiok@gmail.com>
 *  @copyright 2019 Sakis Gkiokas
 *  @license   https://opensource.org/licenses/GPL-3.0  GNU General Public License version 3
 */
$chars = array(
    '/[αΑ][ιίΙΊ]/u' => 'e',
    '/[οΟΕε][ιίΙΊ]/u' => 'i',
    '/[αΑ][υύΥΎ]([θΘκΚξΞπΠσςΣτTφΦχΧψΨ]|\s|$)/u' => 'af$1',
    '/[αΑ][υύΥΎ]/u' => 'av',
    '/[εΕ][υύΥΎ]([θΘκΚξΞπΠσςΣτTφΦχΧψΨ]|\s|$)/u' => 'ef$1',
    '/[εΕ][υύΥΎ]/u' => 'ev',
    '/[οΟ][υύΥΎ]/u' => 'ou',
    '/(^|\s)[μΜ][πΠ]/u' => '$1b',
    '/[μΜ][πΠ](\s|$)/u' => 'b$1',
    '/[μΜ][πΠ]/u' => 'b',
    '/[νΝ][τΤ]/u' => 'nt',
    '/[τΤ][σΣ]/u' => 'ts',
    '/[τΤ][ζΖ]/u' => 'tz',
    '/[γΓ][γΓ]/u' => 'ng',
    '/[γΓ][κΚ]/u' => 'gk',
    '/[ηΗ][υΥ]([θΘκΚξΞπΠσςΣτTφΦχΧψΨ]|\s|$)/u' => 'if$1',
    '/[ηΗ][υΥ]/u' => 'iu',
    '/[αάΑΆ]/u' => 'a',
    '/[βΒ]/u' => 'v',
    '/[γΓ]/u' => 'g',
    '/[δΔ]/u' => 'd',
    '/[εέΕΈ]/u' => 'e',
    '/[ζΖ]/u' => 'z',
    '/[ηήΗΉ]/u' => 'i',
    '/[θΘ]/u' => 'th',
    '/[ιίϊΐΙΊΪ]/u' => 'i',
    '/[κΚ]/u' => 'k',
    '/[λΛ]/u' => 'l',
    '/[μΜ]/u' => 'm',
    '/[νΝ]/u' => 'n',
    '/[ξΞ]/u' => 'x',
    '/[οόΟΌ]/u' => 'o',
    '/[πΠ]/u' => 'p',
    '/[ρΡ]/u' => 'r',
    '/[σςΣ]/u' => 's',
    '/[τΤ]/u' => 't',
    '/[υύϋΰΥΎΫ]/u' => 'y',
    '/[φΦ]/iu' => 'f',
    '/[χΧ]/u' => 'ch',
    '/[ψΨ]/u' => 'ps',
    '/[ωώΩΏ]/u' => 'o',
);
