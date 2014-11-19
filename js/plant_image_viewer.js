/**
 * Created with JetBrains PhpStorm.
 * User: cwarren
 * Date: 10/20/14
 * Time: 10:54 AM
 * To change this template use File | Settings | File Templates.
 */

$(document).ready(function () {
//    alert("plant image viewer controls js loaded");

    $('body').append('<div id="plantImageViewer" class="modal hide" data-backdrop="false">' +
        '<div class="modal-header"><a href="#" class="close" data-dismiss="modal">&times;</a><h3 class="imageTitle"></h3></div>' +
        '<div class="modal-body"><img id="plantImageViewer-image" src=""/></div>' +
        '<div class="modal-footer">' +
        '<input type="button" id="plantImageViewerClose" class="btn" data-dismiss="modal" value="Close"/>' +
        '</div>' +
        '</div>');


    $(document.body).on('click', '.plant-image', function(evt){
        handlePlantImageClick($(this),evt);
    });

    function handlePlantImageClick(targetImg,evt) {
//        alert("clicked plant image");
        evt.preventDefault();

        var targetImgSrc = targetImg.attr("src");

        console.log("targetImgSrc: "+targetImgSrc);
        var titleStr = targetImgSrc.replace(/^.*\/([^\/]+)$/,"$1");
        console.log("titleStr: "+titleStr);

        $("#plantImageViewer h3.imageTitle").html(titleStr);

        $("#plantImageViewer-image").attr('src',targetImgSrc);
        $('#plantImageViewer').modal({show:'true'});
        $('#plantImageViewer #plantImageViewerClose').focus();
        $('#plantImageViewerClose').off("click");
//        var target = $("#"+targetId);
    }
});
