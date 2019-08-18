{** Copyright 2019 Sakis Gkiokas
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
 *}

{if $help_ajax == true}
    <link rel="stylesheet" type="text/css" href="{$css_file}" />
{/if}
{if $help_ajax == false}
    <div class="bootstrap" id="sg_latinurlshelpblock">
        <div class="panel">
            <div class="panel-heading" onclick="$('#sg_latinurls_help_panel').slideToggle();">
                <i class="icon-question"></i>
                {$help_title|escape:'htmlall':'UTF-8'}  <span style="text-transform: none;font-style: italic;">({$help_sub|escape:'htmlall':'UTF-8'})</span>
                {if $update.res == 'update'}
                    &nbsp;<span class="sg_latinurls_update_title_success">ΒΡΕΘΗΚΕ ΜΙΑ ΕΝΗΜΕΡΩΣΗ!</span>
                {elseif $update.res == 'current'}
                    &nbsp;<span class="sg_latinurls_update_title_current">ΔΕΝ ΒΡΕΘΗΚΕ ΕΝΗΜΕΡΩΣΗ!</span>
                {elseif $update.res == 'error'}
                    &nbsp;<span class="sg_latinurls_update_title_error">ΣΦΑΛΜΑ ΚΑΤΑ ΤΟΝ ΕΛΕΓΧΟ ΕΝΗΜΕΡΩΣΗΣ!</span>
                {/if}
            </div>
        {/if}
        <div id="sg_latinurls_help_panel"{if $help_ajax == false && $hide == true} style="display: none;"{/if}>
            <div class="sg_latinurls_update">
                {if $update.res != '' && $update.res != 'error'}
                    {if $update.res =='current'}
                        <div class="sg_latinurls_update_out_current">
                            <p>Το πρόσθετο είναι ενημερωμένο στην τελευταία έκδοση.</p>
                        </div>
                    {elseif $update.res=='update'}
                        <div class="sg_latinurls_update_out_success">
                            <p>Μια νέα ενημέρωση είναι διαθέσιμη: <a href="{$update.download_link}">{$update.download_link}</a> </p>
                        </div>
                    {/if}
                {elseif $update.res == 'error'}
                    <div class="sg_latinurls_update_out_error">
                        <p>Σφάλμα κατά τον έλεγχο για ενημέρωση: {$update.out}</p>
                    </div>
                {/if}
                <div class="sg_latinurls_update_form">
                    <form action="{$href}" method="post">
                        <button type="submit" name="sg_latinurls_check_update">
                            <i class="icon-refresh"></i>
                            Έλεγχος για ενημέρωση
                        </button>
                    </form>
                </div>
            </div>
            <div class="sg_latinurls_help_title">
                <p>{$module_name|escape:'htmlall':'UTF-8'} - v{$module_version|escape:'htmlall':'UTF-8'}</p>
            </div>
            <div class="sg_latinurls_help_body">
                <p>Αυτή το πρόσθετο σάς επιτρέπει να μετατρέψετε όλες τις μη λατινικές διευθύνσεις των προϊόντων σε λατινικές. Αυτό μπορεί να γίνει όταν προσθέτετε ένα προϊόν ή κατά απάιτηση για ήδη καταχωρημένα προϊόντα.</p>
                <p>&copy;2019 Σάκης Γκιόκας. Αυτό το πρόσθετο είναι εντελώς δωρεάν, με άδεια χρήσης <a href="https://opensource.org/licenses/GPL-3.0" target="_blank">GNU General Public License version 3</a>.</p>
                <p>Περισσότερες πληροφορίες: <a href="{$update.info_link}" target="_blank">{$update.info_link}</a></p>
                <p>Github repository: <a href="{$update.github_link}" target="_blank">{$update.github_link}</a></p>
            </div>
            <div class="sg_latinurls_donate_body">
                <p>Αυτό το πρόσθετο είναι εντελώς δωρεάν και μπορείτε να το χρησιμοποιήσετε χωρίς κανένα περιορισμό, όπως περιγράφεται στην άδεια χρήσης του. Στην περίπτωση πάντως που θέλετε να με κεράσετε μια μπύρα, μπορείτε να χρησιμοποιήσετε το παρακάτω κουμπί.</p>
                <div class="sg_latinurls_donate_form">
                    <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                        <input type="hidden" name="cmd" value="_s-xclick">
                        <input type="hidden" name="hosted_button_id" value="94VTWMDKGAFX4">
                        <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                    </form>
                </div>
            </div>
        </div>
        {if $help_ajax == false}
        </div>
    </div>
{/if}