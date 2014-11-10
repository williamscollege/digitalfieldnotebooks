/**
 * Created with JetBrains PhpStorm.
 * User: cwarren
 * Date: 10/20/14
 * Time: 10:54 AM
 * To change this template use File | Settings | File Templates.
 */

$(document).ready(function () {
//    alert("edit metadata js loaded");

    $(".ordering-controls-up-down .btn").on("click",function(evt) {
        handleOrderingClick($(this),evt);
    });

    $(".ordering-controls-left-right .btn").on("click",function(evt) {
        handleOrderingClick($(this),evt);
    });

    function handleOrderingClick(targetBtn,evt) {
//        alert("clicked");
        evt.preventDefault();
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
//            alert('basisOrdId is '+basisOrdId);
            newOrd = -1 + Number($("#"+basisOrdId).attr("value"));
//            alert('newOrd is '+newOrd);
        } else if (target.is(':last-child')) {
            var basisOrdId = "new_ordering-" + target.prev().attr("id");
            newOrd = 5 +  Number($("#"+basisOrdId).attr("value"));
        } else {
            var basisOrdIdP = "new_ordering-" + target.prev().attr("id");
            var basisOrdIdN = "new_ordering-" + target.next().attr("id");
            newOrd = .5 * ( Number($("#"+basisOrdIdP).attr("value")) +  Number($("#"+basisOrdIdN).attr("value")));
        }
//        alert('setting ordering at '+"#new_ordering-"+targetId+" to be "+newOrd);
        $("#new_ordering-"+targetId).attr("value",newOrd);
    }
});
