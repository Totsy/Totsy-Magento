
	runAjaxFunction = function(postUrl,alldata){

		new Ajax.Request(postUrl, {
			method: "post",
		  	parameters: alldata,
		  	onSuccess: function(response) {
				window.location.href = window.location.href;
			}
		});
	}

	formSubmit = function(item,errorText,postUrl){

		var firstSelect = $(item+'-select');
		if(firstSelect.selectedIndex == 0){
			alert(errorText);
			return false;
		}

		var secondSelect = $(item+'-form-additional').down('select');
		if(secondSelect.selectedIndex == 0){
			alert(errorText);
			return false;
		}

		var alldata = new Hash();
		var way = firstSelect.options[firstSelect.selectedIndex].value;
		var list = secondSelect.options[secondSelect.selectedIndex].value;;
		alldata.set('way',way);
		alldata.set('list',list);
		alldata.set('start',$('start').value);
		alldata.set('limit',$('limit').value);

		runAjaxFunction(postUrl, alldata);
	}



	submitMyHooks = function(postUrl){

		var alldata = new Hash();
		$$("table#webHooksGrid_table tr").each(function(row, ind){
			if(ind == 1 || ind == 0){
				return;
			}
			var data = new Hash();
			var i = 0;
			var list_id = '';
			if(row.select("td").length){
		    	row.select("td").each(function(td, index){
					if(i == 0){
						list_id = td.innerHTML.trim();
						data.set('list_id',list_id);
					}if(i > 1){
			        	td.select("input").each(function(check, index){
							var value = 0;
		                    if(check.checked){
		                    	value = 1;
		                    }
				        	data.set(check.name,value);
			        	});
					}
					i++;
		   		});

		    }
	   		alldata.set(list_id,data.toQueryString());
		});
		runAjaxFunction(postUrl, alldata);
	}
