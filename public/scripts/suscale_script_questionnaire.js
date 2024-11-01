var button = null;
var already_print = false;
var systemID = localStorage.getItem("SESSION_SYSTEM_ID");
var userID = localStorage.getItem("SESSION_USER_ID");
var cameFrom = localStorage.getItem("SESSON_CAME_FROM");

jQuery(document).ready(function($){
    $button = $('#sus_button');
    $button.click(function(){
        let temp_json = {
            prima : null,
            seconda : null,
            terza : null,
            quarta : null,
            quinta : null,
            sesta : null,
            settima : null,
            ottava : null,
            nona : null,
            decima : null
        }

        for(i=1;i<=10;i++){
            let temp = document.querySelectorAll('input[name="sus_'+i+'"]');
            for( check of temp ){
                if( check.checked ){
                    let latin = suscale_int_to_latin(i);
                    temp_json[latin] = suscale_get_radio_val(i,check.value);
                }
            }
        }

        if (suscale_check_complete(temp_json)){
            suscale_send_json(temp_json);
            suscale_clear_questionnaire();
        }
        else alert("Please complete before to send");   
    });

    function suscale_int_to_latin(j){
        if(j==1){
            return "prima";
        }
        if(j==2){
            return "seconda";
        }
        if(j==3){
            return "terza";
        }
        if(j==4){
            return "quarta";
        }
        if(j==5){
            return "quinta";
        }
        if(j==6){
            return "sesta";
        }
        if(j==7){
            return "settima";
        }
        if(j==8){
            return "ottava";
        }
        if(j==9){
            return "nona";
        }
        if(j==10){
            return "decima";
        }
    }

    function suscale_get_radio_val(j, value){
        if("sus_"+j+"_1" == value)
            return 1;
        if("sus_"+j+"_2" == value)
            return 2;
        if("sus_"+j+"_3" == value)
            return 3;
        if("sus_"+j+"_4" == value)
            return 4;
        if("sus_"+j+"_5" == value)
            return 5;
    }

    function suscale_check_complete(json_sus){
        for(i = 1;i<=10;i++){
            if(json_sus[suscale_int_to_latin(i)] == null)
                return false;
        }
        return true;
    }

    function suscale_clear_questionnaire(){
        for(i=1;i<=10;i++){
            let temp = document.querySelectorAll('input[name="sus_'+i+'"]');
            for( check of temp ){
                if( check.checked ){
                    check.checked = false;
                }
            }
        }
    }

    function suscale_send_json(json_sus){
        var systemID = localStorage.getItem("SESSION_SYSTEM_ID");
        var userID = localStorage.getItem("SESSION_USER_ID");
            $.ajax({
            url: wp_ajax.ajaxurl,
            type: "POST",
            data: {
                suscale_questionnaire: json_sus,
                action: "suscale_submit",
                _nonce : wp_ajax.nonce["suscale_submit"],
                suscale_systemID: systemID,
                suscale_userID : userID},
            dataType: "json"
        })
        .done(function(data){
            suscale_handle_response(data);
            localStorage.removeItem("SESSION_SYSTEM_ID");
            localStorage.removeItem("SESSION_USER_ID");
            came_temp = localStorage.getItem("SESSION_CAME_FROM");
            localStorage.removeItem("SESSION_CAME_FROM");
            systemID = "";
            userID = "";
            if(confirm("Thanks! Do you want to be redirect soon?"))
                setTimeout(function(){window.location.replace(came_temp);}, 3500);
            
        })
        .fail(function(jqXHR, textStatus){
            // console.log("Request failed: " + textStatus);
            alert(jqXHR["responseText"]);
        })
    }

    function suscale_handle_response(data){
        $from_server = data["response"];
        $grade = data['info'];

        if(!already_print){
            $('#div-sus-button').append("</div><div class='sus_container_result'><span class='sus_field_result' id='sus_result'>Result: " + $from_server + "</span>");
            $('#sus_grade_result').append('<div id="sus_temp_container"><p class="sus_container_grade"><strong>' + $grade + '</strong></p></div>');
            already_print = true;
        }
        else{
            $('.sus_field_result').remove();
            $('.sus_container_result').append("<span class='sus_field_result' id='sus_result'>Result: " + $from_server + "</span>");
            $('#sus_temp_container').remove();
            $('#sus_grade_result').append('<div id="sus_temp_container"><p class="sus_container_grade">' + $grade + '</p></div>');
        }
    }
})