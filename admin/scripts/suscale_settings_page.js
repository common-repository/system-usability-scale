var queue = {};

function suscale_reset_btn(systemID){
    delete queue[systemID];
    jQuery("#name_sys_"+systemID).val("");
    button = jQuery("#btn_sys_"+systemID);
    button.attr("value", "Change");
    button.attr("onclick", "suscale_enqueue_new_name("+systemID+")");

}

function suscale_enqueue_new_name(systemID){
    if(jQuery("#name_sys_"+systemID).val() != ""){
        queue[systemID] = jQuery("#name_sys_"+systemID).val();
        button = jQuery("#btn_sys_"+systemID);
        button.attr("value", "Reset");
        button.attr("onclick", "suscale_reset_btn("+systemID+")");
    }
}

function suscale_update_names(){
    if(Object.keys(queue).length != 0){
        jQuery.ajax({
            url: wp_ajax.ajaxurl,
            type:"POST",
            data: {
                action:"suscale_updateNames",
                _nonce : wp_ajax.nonce["suscale_updateNames"],
                suscale_queue: queue },
            dataType: "json"
        }).done(function(data){
            for (var key in queue){
                if(data.hasOwnProperty(key)){
                    $this_btn = jQuery("#btn_sys_"+key);
                    $this_text = jQuery("#name_sys_"+key);
                    $this_text.attr("placeholder", data[key]);
                    $this_text.val("");
                    $this_btn.click();
                }

            }
            queue = {};
        }).fail(function(jqXHR, textStatus){
        console.log("Request failed: " + textStatus);
        })
    }              
}

function suscale_delete(systemID){
    var susDeleteBySys = "true";
    if(window.confirm("Are you sure you want to delete system "+systemID+"?")){
        if(window.confirm("Do you want to delete the sus submitted to this system?")){
            susDeleteBySys = "true";
        }else{
            susDeleteBySys = "false";
        }
        
        jQuery.ajax({
            url : wp_ajax.ajaxurl,
            type : "POST",
            data : {
                action : "suscale_deleteSystem",
                _nonce : wp_ajax.nonce["suscale_deleteSystem"],
                suscale_systemToDelete : systemID,
                suscale_deleteSubmitQuests : susDeleteBySys},
            dataType : "json"
        }).done(function(data){
            jQuery("#sus_row_"+systemID).remove();
        }).fail(function(jqXHR, textStatus){
            alert("ERROR: "+textStatus);
        })
    }else{
        return;
    }

}

jQuery(document).ready(function($){
    $.ajax({
        url: wp_ajax.ajaxurl,
        type: "POST",
        data: {
            action: "suscale_loadAllSystems",
            _nonce: wp_ajax.nonce["suscale_loadAllSystems"]
            },
        dataType: "json"
    }).done(function(data){
        var $table = $("#sus_load_table");
        for(let i=data.length-1;i>=0;i--){
            var systemID = data[i]["systemID"];
            var systemName = data[i]["systemName"];
            
            var html = '<tr id="sus_row_'+systemID+'"><td class="sus_tab_ele"><a href="'+window.location.origin+'/?page_id='+systemID+'">'+systemID+'</a></td><td><div class="row_actions"><span><input id="name_sys_'+systemID+'" type="text" placeholder="'+systemName+'"/> </span> | <span><input type="button" id="btn_sys_'+systemID+'" value="Change" onclick="suscale_enqueue_new_name('+systemID+')"/></span> | <span><input type="button" value="Delete" id="del_sys_'+systemID+'" onclick="suscale_delete('+systemID+')"/></span></div></td></tr>';
            
            $table.append(html);
        }
    }).fail(function(jqXHR, textStatus){
        console.log(textStatus);
    });

    $("#sus_srch_name").keyup(function(event){
        if(event.key === "Enter"){
            $("#sus_srch_btn").click();
        }
    })    
    $("#sus_srch_macro").keyup(function(event){
        if(event.key === "Enter"){
            $("#sus_srch_btn").click();
        }
    })

    $("#sus_btn_unregistered").click(function(){
        let $user_field = $("#sus_srch_name");
        let $_button = $("#sus_btn_unregistered");
        let check = $_button[0]['checked'];
        if(check){
            $user_field.attr('disabled', true);
        }else{
            $user_field.attr('disabled', false);
        }
    })

    $("#sus_btn_default_macro").click(function(){
        let $macro_field = $("#sus_srch_macro");
        let $_button = $("#sus_btn_default_macro");
        let check = $_button[0]['checked'];
        if(check){
            $macro_field.attr('disabled', true);
        }else{
            $macro_field.attr('disabled', false);
        }
    })


    $("#sus_srch_btn").click(function(){
        var $only_unreg = $("#sus_btn_unregistered")[0]['checked'];
        var $default_macro = $("#sus_btn_default_macro")[0]['checked'];
        var macro = "";
        var username = "";
        let only_unreg = false;

        if($default_macro){
            macro = "NO-MACRO-ASSIGNED";
        }else{
            macro = $("#sus_srch_macro").val();
        }

        if($only_unreg){
            only_unreg = true;
        }else{
            username = $("#sus_srch_name").val();
        }
        if(username.length > 0 || only_unreg || macro.length >= 0){
           $.ajax({
                url: wp_ajax.ajaxurl,
                type: "POST",
                data: {
                    action: "suscale_search",
                    _nonce : wp_ajax.nonce["suscale_search"],
                    suscale_onlyUnregistered : only_unreg,
                    suscale_searchName: username,
                    suscale_searchMacro : macro},
                dataType: "json"     
            }).done(function(data){
                $("#sus_delete_me").remove();
                $this = $("#table_result");
                $this.empty();
                $text_area = $("#sus_srch_name");
                let new_placeholder = $text_area.val();
                if(new_placeholder.length == 0){
                    new_placeholder = " - ALL USERS - "
                }
                if($only_unreg){
                    new_placeholder = " - UNREGISTERED - ";
                }
                if(macro == ""){
                    macro = " - ALL MACROS - ";
                }
                $this.parent().prepend("<span id='sus_delete_me'>User: <em><strong>"+new_placeholder+"</strong></em>  |  Macro: <em><strong>"+macro+"</strong></em></span>");
                $this.append('<tr class="alternate"><th><strong>System ID</strong></th><th><strong>Score</strong></th></tr>');
                if(data.length > 0){
                    for (var i = data.length - 1; i >= 0; i--) {
                        temp_html = '<tr><td class="sus_tab_ele">'+data[i]["systemID"]+'</td><td>'+data[i]["score"]+'</div></td></tr>';
                        $this.append(temp_html);
                    }                    
                }
            }).fail(function(jqXHR, textStatus){
                $this = $("#table_result");
                $this.empty();
            })
        }
    });

    $("#readme").click(function(){
        $.ajax({
            url : wp_ajax.ajaxurl,
            type : "POST",
            data : {
               action: "suscale_openReadme",
               _nonce : wp_ajax.nonce["suscale_openReadme"]                
            },
            dataType : "json"
        }).done(function(data){
            window.open(data, "_blank", "width=640,height=480");
        }).fail(function(jqKXHR, textStatus){
            alert("Unable to load readme.");
        })
    })
})