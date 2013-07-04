ATutor = ATutor || {};
ATutor.mods = ATutor.mods || {};
ATutor.mods.github_patcher = ATutor.mods.github_patcher || {};


$(document).ready(function() {
    $("#select_files").click(function() {
        $.post('mods/github_patcher/patch_creator.php', {'select_files_to_add': 1}, function(obj) {
            $("#files").append('<div id="inner" style="border-width:thin; border-style:solid; padding: 5px 5px 5px 5px; margin:5px 5px 5px 5px"></div>');
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

