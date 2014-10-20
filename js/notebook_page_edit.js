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
        $("#"+target_id).css("background","#f00");

        var structure_id = $(this).val();

        alert("TODO: update options for "+target_id+" based on structure "+structure_id);
    }

    $("#add_new_notebook_page_field_button").click(function(evt) {
        evt.preventDefault();
        if ($(this).hasClass("disabled")) {
            return;
        }

//        alert("TODO:\n4. update value list from selected structure");
        var unique_id = randomString(12);


        var label_holder = $("#add_new_notebook_page_field_button").html();
        $("#add_new_notebook_page_field_button").html("......");
        $("#add_new_notebook_page_field_button").addClass("disabled");

//        $(".notebook_page_fields").css("background-color","#f00");
        $.ajax({
           url: appRootPath()+"/ajax_actions/notebook_page_field.php",
           data: {
                "action": "create",
                "unique": unique_id
           },
           dataType: "json",
           error: function(req,textStatus,err){
                alert("error making ajax request: "+err.toString());
           },
           success: function(data,textStatus,req) {
//               alert("ajax success: "+data.html_output);
                if (data.status == 'success') {
                    var new_li = '<li>'+data.html_output+'</li>';
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

});
