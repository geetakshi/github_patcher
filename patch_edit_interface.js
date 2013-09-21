/************************************************************************/
/* ATutor                                                               */
/************************************************************************/
/* Copyright (c) 2002-2013                                              */
/* Inclusive Design Institute                                           */
/* http://atutor.ca                                                     */
/*                                                                      */
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/************************************************************************/
// $Id$

// @author Geetakshi Batra

/*global jQuery*/
/*global ATutor */

ATutor = ATutor || {};
ATutor.mods = ATutor.mods || {};
ATutor.mods.github_patcher = ATutor.mods.github_patcher || {};

/**
* Function to show the unstaged files available for selection by user to add and commit files
* @author    Geetakshi Batra
*/
$(document).ready(function() {
    $("#select_files").click(function() {
        $.post('mods/github_patcher/patch_creator.php', {'select_files_to_add': 1}, function(obj) {
            $("#files").html('<div id="inner" style="border-width:thin; border-style:solid; padding: 5px; margin:5px;"></div>');
            var data = jQuery.parseJSON(obj)
            $.each(data, function(key, value) {
                if (key == 'mod') {
                    $("#inner").append('<h4>Modified Files</h4>');
                }
                else if (key == 'new') {
                    $("#inner").append('<h4>New Files</h4>');
                }
                else {
                    $("#inner").append('<h4>Deleted Files</h4>');
                }
                $.each(value, function(key1, value1) {
                    $("#inner").append('<input type="checkbox" name="'+key+'_select_file[]" value="'+value1+'"/>'+value1+'<br />');
                });
            });
        });
    });
});


myescape = function(/*string*/ str) {
    return str.replace(/(['"\.*+?^${}()|[\]\/\\])/g, "\\$1").replace(/\n/g, '\\n');
}

function show_message() {
    var messageDIV = document.getElementById("messageDIV");
    var i = document.getElementById("messageIFrame");

if (i.contentDocument) {
    var d = i.contentDocument;
} else if (i.contentWindow) {
    var d = i.contentWindow.document;
} else {
    var d = window.frames[id].document;
}
messageDIV.innerHTML = d.body.innerHTML;
};

