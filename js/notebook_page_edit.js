/**
 * Created with JetBrains PhpStorm.
 * User: cwarren
 * Date: 10/20/14
 * Time: 10:54 AM
 * To change this template use File | Settings | File Templates.
 */

$(document).ready(function () {
//    alert("edit notebook page js loaded");


    $('#edit-delete-notebook-page-control').click(function(evt) {
        evt.preventDefault();
        dfnUtil_launchConfirm("Are you sure you want to delete this entire notebook page, including all of its specimens?",handleDeletePage);
    });

    function handleDeletePage() {
//        alert("handling delete");
        window.location = $('#edit-delete-notebook-page-control').attr("href");
    }

    //--------------------------------------------------------------------

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

                    // now that the stuff has been added to the DOM, don't forget to connect the handler to update term values when a structure is selected
                    $("#notebook_page_field-label_metadata_structure_id_"+unique_id).change(metadataStructureSelectionHandler);

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

    //--------------------------------------------------------------------

    function togglePageFieldDeletionListEntryFor(db_id) {

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
        togglePageFieldDeletionListEntryFor(db_id);
    });

    //--------------------------------------------------------------------

    $("#add_new_specimen_button").click(function(evt){
        evt.preventDefault();
        if ($(this).hasClass("disabled")) {
            return;
        }

        var unique_id = randomString(12);

        var notebook_page_id = $("#notebook_page_id").attr("value");

        var label_holder = $("#add_new_specimen_button").html();
        $("#add_new_specimen_button").html("......");
        $("#add_new_specimen_button").addClass("disabled");

//        $(".notebook_page_fields").css("background-color","#f00");
        $.ajax({
            url: appRootPath()+"/ajax_actions/specimen.php",
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
                    var new_li = '<li class="list-item-new-specimen">'+data.html_output+'</li>';
                    $("#add_new_specimen_button").parent().after(new_li);
                    var created_ids = $("#created_specimen_ids").attr("value");
                    if (created_ids.length > 0) {
                        created_ids += ",";
                    }
                    created_ids += unique_id;
                    $("#created_specimen_ids").attr("value",created_ids);
                } else {
                    dfnUtil_setTransientAlert("error",data.status+": "+data.note,$("#add_new_specimen_button"));
                }
            },
            complete: function(req,textStatus) {
                $("#add_new_specimen_button").html(label_holder);
                $("#add_new_specimen_button").removeClass("disabled");
            }
        });
    });

    //--------------------------------------------------------------------

    function toggleSpecimenDeletionListEntryFor(db_id) {
        var current_delete_list = $("#deleted_specimen_ids").attr("value");
        var haystack = ","+current_delete_list+",";

        var needle = ","+db_id+",";
        var needle_index = haystack.indexOf(needle);

        if (needle_index == -1) { // not there, so add it
            if (current_delete_list.length > 0) {
                $("#deleted_specimen_ids").attr("value",current_delete_list+","+db_id);
            } else {
                $("#deleted_specimen_ids").attr("value",db_id);
            }
        } else {
            var needle_free_haystack = haystack.replace(needle,',');
            var new_delete_list = needle_free_haystack.replace(/^,|,$/gm,'');
            $("#deleted_specimen_ids").attr("value",new_delete_list);
        }
    }

    $(".button-mark-specimen-for-delete").click(function(evt){
//        alert('button-mark-specimen-for-delete clicked');
        evt.preventDefault();
        var dom_id = $(this).attr("data-for_dom_id");
        var db_id = $(this).attr("data-specimen_id");
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
        toggleSpecimenDeletionListEntryFor(db_id);
        return false;
    });

    //--------------------------------------------------------------------

    $(".add-specimen-image-button").click(function(evt) {
//        dfnUtil_setTransientAlert('error','specimen image support not yet implemented',$(this));
        evt.preventDefault();
        var for_specimen = $(this).attr('data-for-specimen');
        $(this).hide();
        $('#specimen-image-upload-form-for-'+for_specimen).show();

    });

    var uploadFileInfo = {};
    $(".specimen-image-file-picker").on('change',prepareUpload);
    function prepareUpload(evt) {
        console.dir(evt.target);
        uploadFileInfo[evt.target.id] = evt.target.files[0];

        var label = $("label[for='"+evt.target.id+"']");
        console.dir(label);
        label.text( evt.target.files[0].name);

        // then jump the focus to the upload button
        $(this).next().next().focus();

    }
    function clearUploadForSpecimen(specimenId) {
        var domId = 'specimen-image-file-for-'+specimenId;
        uploadFileInfo[domId] = '';
        resetFormField($('#'+domId));
        $("#"+domId+"-label").text('Choose File');
//        console.dir(uploadFileInfo);
    }

    function showLoadingSpinner(specimenId) {
        $("#specimen-image-upload-form-for-"+specimenId).after('<div id="loadingSpinner"><img src="'+appRootPath()+'/img/ajax-loader.gif"/> Loading...</div>');
    }

    function hideLoadingSpinner(specimenId) {
        $("#loadingSpinner").remove();
    }

    $(".specimen-image-upload-do-it-button").click(function(evt) {
//        console.dir(uploadFileInfo);
        evt.preventDefault();
        var specimenId = $(this).attr('data-for-specimen');
        var filesKey = 'specimen-image-file-for-'+specimenId;
        if (! uploadFileInfo[filesKey]) {
            dfnUtil_setTransientAlert('error','no file to upload',$(this));
            return false;
        }
        var data = new FormData();
        data.append('upload_file', uploadFileInfo[filesKey]);
        data.append('for_specimen',specimenId);

        // SHOW LOADING SPINNER
        showLoadingSpinner(specimenId);

        $.ajax({
            url: appRootPath()+'/ajax_actions/specimen_image.php?action=image_upload',
            type: 'POST',
            data: data,
            cache: false,
            dataType: 'json',
            processData: false, // Don't process the files
            contentType: false, // Set content type to false as jQuery will tell the server its a query string request
            success: function(data, textStatus, jqXHR)
            {
                if(typeof data.error === 'undefined')
                {
                    // Success, so update the DOM with the new info
//                    alert('SUCCESS');
//                    $("#list_item-specimen_"+specimenId).css('background-color','red');
//                    console.dir(data);
                    if (data.status == 'success') {
                        dfnUtil_setTransientAlert('success','new image added',$("#specimen-image-upload-submit-for-"+specimenId));
                        $('#list_item-specimen_'+specimenId+' ul.specimen-images li:first-child').after(data.html_output);

                        // attach the ordering handlers (don't know why the existing 'on' delcaration isn't catching it.... oh well, just making it work....)
                        // and the save ordering button linkage
//                        $('#list_item-specimen_'+specimenId+' ul.specimen-images li:nth-child(2) .ordering-controls-left-right .btn').on("click",function(evt) {
////                            alert('clicked');
//                            handleOrderingClick($(this),evt);
//                            $('#save-specimen-image-ordering-for-'+$(this).parent().parent().parent().attr('data-specimen_id')).show();
//                        });

                        // clear and hide these controls by clicking the cancel button
                        $('#specimen-image-upload-form-for-'+specimenId+' .specimen-image-upload-cancel-button').click();
                    } else {
                        dfnUtil_setTransientAlert('error',data.note,$("#specimen-image-upload-submit-for-"+specimenId));
//                        console.dir(data);
                        // just clear the file input control
                        clearUploadForSpecimen(specimenId);
                    }
                }
                else
                {
                    // Handle errors here
                    alert('ERRORS: ' + data.error);
                    console.log('ERRORS: ' + data.error);
                    console.dir(data);
                    // just clear the file input control
                    clearUploadForSpecimen(specimenId);
                }
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                // Handle errors here
                alert('ERROR: ' + textStatus);
                console.log('ERROR: ' + textStatus);
                console.dir(jqXHR);
                clearUploadForSpecimen(specimenId);
            },
            complete: function(jqXHR,textStatus)
            {
                // HIDE LOADING SPINNER
                hideLoadingSpinner(specimenId);
            }
        });

    });

    $(".specimen-image-upload-cancel-button").click(function(evt) {
        evt.preventDefault();
        var specimenId = $(this).attr('data-for-specimen');
//        resetFormField($('#specimen-image-file-for-'+specimenId));
        clearUploadForSpecimen(specimenId);

        $('#specimen-control-add-image-for-'+specimenId).show();
        $('#specimen-image-upload-form-for-'+specimenId).hide();
    });

    //--------------------------------------------------------------------

//    $("li.specimen-image .ordering-controls-left-right .btn").on("click",function(evt) {
//        $('#save-specimen-image-ordering-for-'+$(this).parent().parent().parent().attr('data-specimen_id')).show();
//    });

    $(document.body).on('click', "li.specimen-image .ordering-controls-left-right .btn", function(evt){
        $('#save-specimen-image-ordering-for-'+$(this).parent().parent().parent().attr('data-specimen_id')).show();
    });


    $('.specimen-save-image-ordering-button').on("click",function(evt) {
        dfnUtil_setTransientAlert('error','specimen image ordering saving yet implemented',$(this));
        $(this).hide();
        evt.preventDefault();
    });

    //--------------------------------------------------------------------

    var PARAM_handleDeleteSpecimenImage_dom_elt = '';
    var PARAM_handleDeleteSpecimenImage_dom_evt = '';
    $(".button-delete-specimen-image").on("click",function(evt) {
        PARAM_handleDeleteSpecimenImage_dom_elt = $(this);
        PARAM_handleDeleteSpecimenImage_dom_evt = evt;
        dfnUtil_launchConfirm("Are you sure you want to delete that image?",handleDeleteSpecimenImage)
//        handleDeleteSpecimenImage($(this),evt);
        evt.preventDefault();
    });

    function handleDeleteSpecimenImage() {
        var dom_elt = PARAM_handleDeleteSpecimenImage_dom_elt;
        var evt = PARAM_handleDeleteSpecimenImage_dom_evt;
        dfnUtil_setTransientAlert('error','specimen image deletion not yet implemented',dom_elt);
    }

});
