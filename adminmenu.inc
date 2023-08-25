<?php
// Copyright (c) 2012 Claus Tondering.  E-mail: claus@ezer.dk.
//
// This code is distributed under an MIT License:
//
// Permission is hereby granted, free of charge, to any person obtaining a copy of this software and
// associated documentation files (the "Software"), to deal in the Software without restriction,
// including without limitation the rights to use, copy, modify, merge, publish, distribute,
// sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in all copies or
// substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING
// BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
// NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
// DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

// This file contains a class and function to display the amininistrator menu.


require_once 'html.inc';
require_once 'database.inc';

// The Credentials class contains information about a user who has logged in.
// When an object of this class is constructed, the user ID is taken from the $_SESSION information.
// If none is available, the user is not logged in.
class Credentials {
    public $error; // Error information
    public $user; // Fields retrieved from the users table in the database

    // Constructor
    function __construct() {
        global $db_prefix;

        if (!session_id())
            session_start();

        // Look up the user's credentials in the database
        if (isset($_SESSION['picdb_user']) && $_SESSION['picdb_user']>0) {
            $rs = exec_sql("SELECT * FROM {$db_prefix}users WHERE id={$_SESSION['picdb_user']}");
            $this->user = mysqli_fetch_object($rs);
            $this->error = null;
        }
        else {
            $this->error = 'You are not logged in';
            $this->user = null;
        }
    }

    // Returns true if this user is logged in
    function is_user() {
        return !is_null($this->user);
    }

    // Returns true if this user has administrator rights
    function is_admin() {
        return $this->is_user() && $this->user->isadmin;
    }

    // Returns true if this user has administrator rights or has a user id equal to $userid
    function is_admin_or_me($userid) {
        return $this->is_admin() || $userid==$this->user->id;
    }
}

// $usermenu contains the items to be listed in the administrative menu for all authenticated users.
// It maps file name to menu text.
$usermenu = array(
    'img.php' => 'Main page',
    'upload.php' => 'Upload pictures',
    'manpic.php' => 'Manage unpublished pictures', 
    'uploaddesc.php' => 'Upload picture descriptions',
    'editcat.php' => 'Edit category descriptions',
    'editcatref.php' => 'Upload Bible References for Category',
    'checkpic.php' => 'Check published pictures',
    'rescan.php' => 'Scan again for Bible references',
    'editbibleurl.php' => 'Edit Bible reference URLs',
    'edit_authors.php' => 'Edit photographers',
    // 'Edit my profile' will be added in display_admin_menu()
    );

// $adminmenu contains the items to be listed in the administrative menu for all users with administrator rights.
// It maps file name to menu text.
$adminmenu = array(
    'edit_users.php' => 'Edit users',
    );

// Displays the administrative menu for a user with the specified credentials.
function display_admin_menu($credentials)
{
    global $usermenu, $adminmenu, $last_comp;

    if (is_null($credentials->user))
        print "<p><a class=\"makebutton\" href=\"login.php\">Login</a></p>\n";
    else {
        $usermenu["edit_one_user.php?id={$credentials->user->id}"] = 'Edit my profile';
        echo <<<END
          <script type="text/javascript">//<![CDATA[
            $(function(){

                $('.linklist').hover(
                    function() {
                        $(this).addClass('llactive');
                    }, 
                    function() {
                        $(this).removeClass('llactive');
                    } 
                );
                });
          //]]></script>
END;

        html_attr_b('div','id="adminmenu" class="ui-corner-all"');
        html('h1','Administrative menu:');
        html_1('hr');
        html_class('p','logininfo',"You are logged in as<br/>{$credentials->user->first_name} {$credentials->user->last_name}");
        html_1('hr');
        foreach ($usermenu as $file => $text) {
            if ($file == $last_comp)
                html_class('span','llthis ui-corner-all',$text);
            else
                html_attr('a',"class=\"menuitem\" href=\"$file\"",
                          shtml_class('span','linklist',$text));
        }
        if ($credentials->is_admin()) {
            foreach ($adminmenu as $file => $text) {
                if ($file == $last_comp)
                    html_class('span','llthis',$text);
                else
                    html_attr('a',"class=\"menuitem\" href=\"$file\"",
                              shtml_class('span','linklist',$text));
            }
        }

        print "<p><a class=\"makebutton\" href=\"login.php?func=logout\">Log out</a></p>\n";

        html_e('div');
    }
}

?>