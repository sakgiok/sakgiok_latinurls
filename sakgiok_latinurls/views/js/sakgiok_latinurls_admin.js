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


$(document).ready(function () {
    $("#sakgiok_latinurls_actionform").on("submit", function () {
       $("#sakgiok_latinurls_working").show();
       $("button[name=submitsakgiok_latinurlsAction]").attr("disabled", true);
    });
    $("#sakgiok_latinurls-clearbutton").on("click", function (){
        
    });
});