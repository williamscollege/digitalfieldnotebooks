/**
 * Created with JetBrains PhpStorm.
 * User: cwarren
 * Date: 10/20/14
 * Time: 10:54 AM
 * To change this template use File | Settings | File Templates.
 */

$(document).ready(function () {
//    alert("edit metadata js loaded");

    $('#edit-delete-metadata-structure-control').click(function(evt) {
        evt.preventDefault();
        dfnUtil_launchConfirm("Are you sure you want to delete this metadata type, including all of its sub-types?",handleDeleteMetadataStructure);
    });

    function handleDeleteMetadataStructure() {
//        alert("handling delete");
        window.location = $('#edit-delete-metadata-structure-control').attr("href");
    }

    $(".ordering-controls-up-down .btn").on("click",function(evt) {
        handleOrderingClick($(this),evt);
    });

    function handleOrderingClick(targetBtn,evt) {
//        alert("clicked");
        var targetId = targetBtn.attr("data-for-dom-id");
        var target = $("#"+targetId);
        if (targetBtn.hasClass("ordering-button-earlier")) {
            if (target.is(':nth-child(2)')) { // first in list after the add button can't go any earlier
                return;
            }
            target.prev().before(target);
        } else {
            if (target.is(':last-child')) { // last in list can't go any later
                return;
            }
            target.next().after(target);
        }

        var newOrd = 0;
        if (target.is(':nth-child(2)')) {
            var basisOrdId = "new_ordering-" + target.next().attr("id");
            newOrd = -1 + Number($("#"+basisOrdId).attr("value"));
        } else if (target.is(':last-child')) {
            var basisOrdId = "new_ordering-" + target.prev().attr("id");
            newOrd = 5 +  Number($("#"+basisOrdId).attr("value"));
        } else {
            var basisOrdIdP = "new_ordering-" + target.prev().attr("id");
            var basisOrdIdN = "new_ordering-" + target.next().attr("id");
            newOrd = .5 * ( Number($("#"+basisOrdIdP).attr("value")) +  Number($("#"+basisOrdIdN).attr("value")));
        }
        $("#new_ordering-"+targetId).attr("value",newOrd);
    }


    //--------------------------------------------------------------------


    //--------------------------------------------------------------------
//
//    function togglePageFieldDeletionListEntryFor(db_id) {
//
//        var current_delete_list = $("#deleted_page_field_ids").attr("value");
//        var haystack = ","+current_delete_list+",";
//
//        var needle = ","+db_id+",";
//        var needle_index = haystack.indexOf(needle);
//
//        if (needle_index == -1) { // not there, so add it
//            if (current_delete_list.length > 0) {
//                $("#deleted_page_field_ids").attr("value",current_delete_list+","+db_id);
//            } else {
//                $("#deleted_page_field_ids").attr("value",db_id);
//            }
//        } else {
//            var needle_free_haystack = haystack.replace(needle,',');
//            var new_delete_list = needle_free_haystack.replace(/^,|,$/gm,'');
//            $("#deleted_page_field_ids").attr("value",new_delete_list);
//        }
//    }

//    $(".button-mark-pagefield-for-delete").click(function(evt){
//        evt.preventDefault();
//        var dom_id = $(this).attr("data-for_dom_id");
//        var db_id = $(this).attr("data-notebook_page_field_id");
////        alert("handle mark for delete for "+dom_id+" (visual, and add to the hidden list)");
//
//        if ($("#"+dom_id).hasClass('delete-marked')) {
//            $("#"+dom_id).removeClass('delete-marked');
//            $("#"+dom_id+" button i").addClass("icon-remove-sign");
//            $("#"+dom_id+" button i").removeClass("icon-plus-sign");
//            $(this).removeClass("btn-info");
//            $(this).addClass("btn-danger");
//            $(this).attr("title",$(this).attr("data-do-mark-title"));
//        } else {
//            $("#"+dom_id).addClass('delete-marked');
//            $("#"+dom_id+" button i").removeClass("icon-remove-sign");
//            $("#"+dom_id+" button i").addClass("icon-plus-sign");
//            $(this).removeClass("btn-danger");
//            $(this).addClass("btn-info");
//            $(this).attr("title",$(this).attr("data-remove-mark-title"));
//
//        }
//        togglePageFieldDeletionListEntryFor(db_id);
//    });

    //--------------------------------------------------------------------

//    $("#add_new_specimen_button").click(function(evt){
//        evt.preventDefault();
//        if ($(this).hasClass("disabled")) {
//            return;
//        }
//
//        var unique_id = randomString(12);
//
//        var notebook_page_id = $("#notebook_page_id").attr("value");
//
//        var label_holder = $("#add_new_specimen_button").html();
//        $("#add_new_specimen_button").html("......");
//        $("#add_new_specimen_button").addClass("disabled");
//
////        $(".notebook_page_fields").css("background-color","#f00");
//        $.ajax({
//            url: appRootPath()+"/ajax_actions/specimen.php",
//            data: {
//                "action": "create",
//                "unique": unique_id,
//                "notebook_page_id": notebook_page_id
//            },
//            dataType: "json",
//            error: function(req,textStatus,err){
//                alert("error making ajax request: "+err.toString());
//            },
//            success: function(data,textStatus,req) {
////               alert("ajax success: "+data.html_output);
//                if (data.status == 'success') {
//                    var new_li = '<li class="list-item-new-specimen">'+data.html_output+'</li>';
//                    $("#add_new_specimen_button").parent().after(new_li);
//                    var created_ids = $("#created_specimen_ids").attr("value");
//                    if (created_ids.length > 0) {
//                        created_ids += ",";
//                    }
//                    created_ids += unique_id;
//                    $("#created_specimen_ids").attr("value",created_ids);
//                } else {
//                    dfnUtil_setTransientAlert("error",data.status+": "+data.note,$("#add_new_specimen_button"));
//                }
//            },
//            complete: function(req,textStatus) {
//                $("#add_new_specimen_button").html(label_holder);
//                $("#add_new_specimen_button").removeClass("disabled");
//            }
//        });
//    });
//
//    //--------------------------------------------------------------------
//
//    function toggleSpecimenDeletionListEntryFor(db_id) {
//        var current_delete_list = $("#deleted_specimen_ids").attr("value");
//        var haystack = ","+current_delete_list+",";
//
//        var needle = ","+db_id+",";
//        var needle_index = haystack.indexOf(needle);
//
//        if (needle_index == -1) { // not there, so add it
//            if (current_delete_list.length > 0) {
//                $("#deleted_specimen_ids").attr("value",current_delete_list+","+db_id);
//            } else {
//                $("#deleted_specimen_ids").attr("value",db_id);
//            }
//        } else {
//            var needle_free_haystack = haystack.replace(needle,',');
//            var new_delete_list = needle_free_haystack.replace(/^,|,$/gm,'');
//            $("#deleted_specimen_ids").attr("value",new_delete_list);
//        }
//    }
//
//    $(".button-mark-specimen-for-delete").click(function(evt){
////        alert('button-mark-specimen-for-delete clicked');
//        evt.preventDefault();
//        var dom_id = $(this).attr("data-for_dom_id");
//        var db_id = $(this).attr("data-specimen_id");
////        alert("handle mark for delete for "+dom_id+" (visual, and add to the hidden list)");
//
//        if ($("#"+dom_id).hasClass('delete-marked')) {
//            $("#"+dom_id).removeClass('delete-marked');
//            $("#"+dom_id+" button i").addClass("icon-remove-sign");
//            $("#"+dom_id+" button i").removeClass("icon-plus-sign");
//            $(this).removeClass("btn-info");
//            $(this).addClass("btn-danger");
//            $(this).attr("title",$(this).attr("data-do-mark-title"));
//        } else {
//            $("#"+dom_id).addClass('delete-marked');
//            $("#"+dom_id+" button i").removeClass("icon-remove-sign");
//            $("#"+dom_id+" button i").addClass("icon-plus-sign");
//            $(this).removeClass("btn-danger");
//            $(this).addClass("btn-info");
//            $(this).attr("title",$(this).attr("data-remove-mark-title"));
//
//        }
//        toggleSpecimenDeletionListEntryFor(db_id);
//        return false;
//    });
//
//    //--------------------------------------------------------------------
//
//    $(".add-specimen-image-button").click(function(evt) {
//        dfnUtil_setTransientAlert('error','specimen image support not yet implemented',$(this));
//        evt.preventDefault();
//    });
});
