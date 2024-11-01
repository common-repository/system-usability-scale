jQuery(document).ready(function($){
	var macro_system = "no_macro_required";

	$.ajax({
		url : wp_ajax.ajaxurl,
		type : "POST",
        data: {
            action: "suscale_loadAllSystems",
            _nonce: wp_ajax.nonce["suscale_loadAllSystems"]
            },
        dataType: "json"
	}).done(function(){
		console.log("Updated");
	}).fail(function(){
		console.log("Error during loading");
	});

	$.ajax({
		url : wp_ajax.ajaxurl,
		type : "POST",
		data : {
			action: "suscale_loadMacros",
			_nonce: wp_ajax.nonce['suscale_loadMacros']
		},
		dataType : "json"
	}).done(function(data){
		var $macro_append = $("#sus_list_macro");
		$macro_append.append('<span><input type="hidden" id="macro_selected" value=""></span>');
		var $macro_selected = $("#macro_selected");
		$macro_append.append('<div id="container_macro_systems" class="dropdown-content">');
		$container = $("#container_macro_systems");
		html = "<a id='macro_all'></a>"
		$container.append(html);
		$("#macro_all").text("All systems");
		$("#macro_all").click(function(){
			$("#macro_name").text("All systems");
			macro_system = "no_macro_required";
			$container.toggle();
			$("#sus_reload_stats").click();
		})
		for(i = 0; i<data.length;i++){
			let name = data[i]['macroSystem'];
			html = "<a id=macro_"+i+">"+name+"</a>"
			$container.append(html);
			$("#macro_"+i).click(function(){
				$text = $(this).text();
				$macro_selected.text($text);
				$("#macro_name").text($text);
				macro_system = $text;
				$container.toggle();
				$("#sus_reload_stats").click();
			})
			$("#macro_name").text("");
		}
		$macro_append.append("</div>");
		$(".dropbtn").click(function(){
			$container.toggle();
		})
		$("#macro_name").text("All systems");
		$("#sus_reload_stats").click();
	}).fail(function(jqXHR, textStatus){
		alert("Server: " + textStatus);
	});

	var first_insert = true;
	$("#sus_reload_stats").on("click", function(){
		$.ajax({
			url: wp_ajax.ajaxurl,
	    	type: "POST",
	    	data: {
	    		action: "suscale_loadSpecificMacro",
	    		suscale_macro : macro_system,
	    		_nonce : wp_ajax.nonce["suscale_loadSpecificMacro"]
    		},
	    	dataType: "json"
			}).done(function(data){
				$avg = data['avg_sus'];
				$scores = data['scores'];
				$systems = data['keys'];
				$total = data['num_sus'];
				$grade = data['grade_total'];

				if(first_insert){
					first_insert = false;
					$('#sus_total_values').append($total);
					$('#sus_avg_all').append($avg);
					$('#sus_grade_all').append($grade);

					for (let i = $systems.length - 1; i >= 0; i--) {
						$system = $systems[i];
						$temp = $scores[$system];
						$name = $temp['sys_name'];
						html = '<div class="sus_container_system"><p id="sus_system-'+$system+'">System ID: <strong>'+$system+'</strong>    |    System name: <strong>'+$name+'</strong></p><table><tr class="sus_first_row"><th>AVG</th><th>Grade</th></tr><tr class="sus_value_row"><td id="sus_avg-'+$system+'">'+$temp["sys_avg"]+'</td><td id="sus_grade-'+$system+'">'+$temp["sys_grade"]+'</td></tr></table></div>';
						$("#sus_container_results").append(html);
					}

				}else{

					$('#sus_total_values').empty();
					$('#sus_avg_all').empty();
					$("#sus_container_results").empty();
					$('#sus_grade_all').empty();

					for (let i = $systems.length - 1; i >= 0; i--) {
						$system = $systems[i];
						$temp = $scores[$system];
						$name = $temp['sys_name'];
						html = '<div class="sus_container_system"><p id="sus_system-'+$system+'">System ID: <strong>'+$system+'</strong>    |    System name: <strong>'+$name+'</strong></p><table><tr class="sus_first_row"><th>AVG</th><th>Grade</th></tr><tr class="sus_value_row"><td id="sus_avg-'+$system+'">'+$temp["sys_avg"]+'</td><td id="sus_grade-'+$system+'">'+$temp["sys_grade"]+'</td></tr></table></div>';
						//html = '<div class="sus_container_system"><p id="sus_system-'+$system+'">System num: '+$system+'</p><table><tr class="sus_first_row"><th>AVG</th><th>Grade</th></tr><tr class="sus_value_row"><td id="sus_avg-'+$system+'">'+$temp["sys_avg"]+'</td><td id="sus_grade-'+$system+'">'+$temp["sys_grade"]+'</td></tr></table></div>'
						$("#sus_container_results").append(html);
					}
					$('#sus_grade_all').append($grade);
					$('#sus_total_values').append($total);
					$('#sus_avg_all').append($avg);

				}

			}).fail(function(jqXHR, textStatus){
				console.log("Server: " + textStatus);
		})
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
			window.open(data['redirect_url'], "_blank", "width=640,height=480");
		}).fail(function(jqKXHR, textStatus){
			alert("Usable to load readme.");
		})
    })
	
});