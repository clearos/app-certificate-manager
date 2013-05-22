<?php

/**
 * Certificate manage javascript helper.
 *
 * @category   apps
 * @package    certificate-manager
 * @subpackage javascript
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/certificate_manager/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('certificate_manager');

///////////////////////////////////////////////////////////////////////////////
// J A V A S C R I P T
///////////////////////////////////////////////////////////////////////////////

header('Content-Type:application/x-javascript');
?>

$(document).ready(function() {

    // Prep
    //-----

    $("#webconfig_restarting").hide();

    // Wizard next button handling
    //----------------------------

    $("#wizard_nav_next").click(function(){
        if ($(location).attr('href').match('.*\/add\/ca$') != null)
            $('form#certificate_form').submit();
        else if ($(location).attr('href').match('.*\/certificate') != null)
            window.location = '/app/base/wizard/next_step';
    });

    // Hook to check status when webconfig restarts after certificate change
    //----------------------------------------------------------------------

    if ($(location).attr('href').match('browser\/warning$') != null)
        checkWebconfig();
});


function checkWebconfig() {
    $.ajax({
        url: '/app/certificate_manager/browser/check',
        method: 'GET',
        dataType: 'json',
        success : function(payload) {
            showWebconfigStatus(payload.wait);
            window.setTimeout(checkWebconfig, 1000);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            showWebconfigStatus(true);
            window.setTimeout(checkWebconfig, 1000);
        }
    });
}

function showWebconfigStatus(wait) {
    if (wait) {
        if ( typeof showWebconfigStatus.counter == 'undefined' ) {
            $("#webconfig_restarting").show();

            // Wizard buttons
            $('#theme_wizard_nav_next').hide();
            $('#theme_wizard_nav_previous').hide();
        }
    } else {
        $("#webconfig_restarting").hide();

        // Wizard buttons
        $('#theme_wizard_nav_next').show();
        $('#theme_wizard_nav_previous').show();
    }
}

// vim: ts=4 syntax=javascript
