/**
 * Created with JetBrains PhpStorm.
 * User: cwarren
 * Date: 10/20/14
 * Time: 10:54 AM
 * To change this template use File | Settings | File Templates.
 */

$(document).ready(function () {
//    alert("edit notebook page js loaded");

    function metadataStructureSelectionHandler() {
        var base_id = $(this).attr("id");
//        alert("'called metadataStructureSelectionHandler on " + base_id);
        var target_id = base_id.replace("label_metadata_structure_id","value_metadata_term_value_id");

        var structure_id = $(this).val();

        $("#"+target_id).prop("disabled", true);

        $.ajax({
            url: appRootPath()+"/ajax_actions/metadata_structure.php",
            data: {
                "action": "value_options",
                "metadata_structure_id": structure_id
            },
            dataType: "json",
            error: function(req,textStatus,err){
                alert("error making ajax request: "+err.toString());
                console.dir(req);
            },
            success: function(data,textStatus,req) {
//               alert("ajax success: "+data.html_output);
                if (data.status == 'success') {
                    $("#"+target_id).html(data.html_output);
                } else {
                    dfnUtil_setTransientAlert("error",data.status+": "+data.note,$(this));
                }
            },
            complete: function(req,textStatus) {
                $("#"+target_id).prop("disabled", false);
            }
        });
    }

    $("#add_new_notebook_page_field_button").click(function(evt) {
        evt.preventDefault();
        if ($(this).hasClass("disabled")) {
            return;
        }

//        alert("TODO:\n4. update value list from selected structure");
        var unique_id = randomString(12);

        var notebook_page_id = $("#notebook_page_id").attr("value");

        var label_holder = $("#add_new_notebook_page_field_button").html();
        $("#add_new_notebook_page_field_button").html("......");
        $("#add_new_notebook_page_field_button").addClass("disabled");

//        $(".notebook_page_fields").css("background-color","#f00");
        $.ajax({
           url: appRootPath()+"/ajax_actions/notebook_page_field.php",
           data: {
                "action": "create",
                "unique": unique_id,
                "notebook_page_id": notebook_page_id
           },
           dataType: "json",
           error: function(req,textStatus,err){
                alert("error making ajax request: "+err.toString());
           },
           success: function(data,textStatus,req) {
//               alert("ajax success: "+data.html_output);
                if (data.status == 'success') {
                    var new_li = '<li class="list-item-new-page-field">'+data.html_output+'</li>';
                    $("#add_new_notebook_page_field_button").parent().after(new_li);
                    var created_ids = $("#created_page_field_ids").attr("value");
                    if (created_ids.length > 0) {
                        created_ids += ",";
                    }
                    created_ids += unique_id;
                    $("#created_page_field_ids").attr("value",created_ids);

//                    $("#notebook_page_field-label_metadata_structure_id_"+unique_id).css("background","#f00");
                    $("#notebook_page_field-label_metadata_structure_id_"+unique_id).change(metadataStructureSelectionHandler);
//                    notebook_page_field-value_metadata_term_value_id_b8jXdC7fpTqH

                } else {
                    dfnUtil_setTransientAlert("error",data.status+": "+data.note,$("#add_new_notebook_page_field_button"));
               }
           },
           complete: function(req,textStatus) {
               $("#add_new_notebook_page_field_button").html(label_holder);
               $("#add_new_notebook_page_field_button").removeClass("disabled");
           }
        });

    });

    $('#edit-delete-notebook-page-control').click(function(evt) {
        evt.preventDefault();
        dfnUtil_launchConfirm("Are you sure you want to delete this entire notebook page, including all of its specimens?",handleDeletePage);
    });

    function handleDeletePage() {
//        alert("handling delete");
        window.location = $('#edit-delete-notebook-page-control').attr("href");
    }

    function toggleDeletionListEntryFor(db_id) {

        var current_delete_list = $("#deleted_page_field_ids").attr("value");
        var haystack = ","+current_delete_list+",";

        var needle = ","+db_id+",";
        var needle_index = haystack.indexOf(needle);

        if (needle_index == -1) { // not there, so add it
            if (current_delete_list.length > 0) {
                $("#deleted_page_field_ids").attr("value",current_delete_list+","+db_id);
            } else {
                $("#deleted_page_field_ids").attr("value",db_id);
            }
        } else {
            var needle_free_haystack = haystack.replace(needle,',');
            var new_delete_list = needle_free_haystack.replace(/^,|,$/gm,'');
            $("#deleted_page_field_ids").attr("value",new_delete_list);
        }
    }

    $(".button-mark-pagefield-for-delete").click(function(evt){
        evt.preventDefault();
        var dom_id = $(this).attr("data-for_dom_id");
        var db_id = $(this).attr("data-notebook_page_field_id");
//        alert("handle mark for delete for "+dom_id+" (visual, and add to the hidden list)");

        if ($("#"+dom_id).hasClass('delete-marked')) {
            $("#"+dom_id).removeClass('delete-marked');
            $("#"+dom_id+" button i").addClass("icon-remove-sign");
            $("#"+dom_id+" button i").removeClass("icon-plus-sign");
            $(this).removeClass("btn-info");
            $(this).addClass("btn-danger");
            $(this).attr("title",$(this).attr("data-do-mark-title"));
        } else {
            $("#"+dom_id).addClass('delete-marked');
            $("#"+dom_id+" button i").removeClass("icon-remove-sign");
            $("#"+dom_id+" button i").addClass("icon-plus-sign");
            $(this).removeClass("btn-danger");
            $(this).addClass("btn-info");
            $(this).attr("title",$(this).attr("data-remove-mark-title"));

        }
        toggleDeletionListEntryFor(db_id);
    });

});
