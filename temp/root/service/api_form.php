<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
  <script type="text/javascript" src="jquery-3.1.1.min.js"></script>
</head>

<body>
	<form id="serviceForm" method="post">
		<table width="100%" border="1">
			<tr>
				<td width="50%">
					<table>
						<tr>
							<td align="right">Service Type: </td>
							<td>
								<select name="serviceType" id="serviceType">
									<?php
										$arr_service_type = array('getData', 'getDataList', 'getExecute');
										foreach ($arr_service_type as $value) {?>
											<option value="<?php echo $value?>"><?php  echo $value?></option>
									<?php } ?>
								</select>
							</td>
						</tr>
						<tr>
							<td align="right">Name :</td>
							<td><input type="text" name="serviceName" id="serviceName" value=""></td>
						</tr>
						<tr>
							<td align="right">Data Type :</td>
							<td>
								<select name="dataType" id="dataType">
								<?php
									$arr_data_type = array('json', 'xml', 'excel' ,'csv', 'pdf');
									foreach ($arr_data_type as $value) {?>
									<option value="<?php echo $value ?>"><?php echo $value ?></option>
								<?php } ?>
								</select>
							</td>
						</tr>
						<tr id="showExecute" style="display: none;">
							<td>Insert ID</td>
							<td>
								<table>
									<tr>
										<td align="right">Table :</td>
										<td><input type="text" name="table" id="table" value=""></td>
									</tr>
									<tr>
										<td align="right">PK Name :</td>
										<td><input type="text" name="field" id="field" value=""></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<div onclick="addParams();">+</div>
								<table width="100%" border="1">
									<thead>
										<tr>
											<td align="center">Name</td>
											<td align="center">Value</td>
										</tr>
									</thead>
									<tbody id="addParams">
										
									</tbody>
								</table>
								<input type="hidden" name="rows" id="rows" value="0">
							</td>
						</tr>

					</table>
					<button type="button" onclick="generator()">Generator</button>
				</td>
				<td width="50%">
					<table>
						<tr>
							<td>
								<textarea id="query" name="query" rows="15" cols="100"></textarea>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<table id="showTable" width="100%" style="display: none">
			<tr>
				<td>
					<textarea id="show" rows="15" cols="100"></textarea>
				</td>
			</tr>
		</table>
	</form>
<script type="text/javascript" src="jquery-3.1.1.min.js"></script>
<script type="text/javascript">
	$("#serviceType").change(function(event) {
		if($(this).val()=='getExecute'){
			$("#showExecute").show();
		}else{
			$("#showExecute").hide();
		}
	});

	function addParams(){
		var rows = parseInt($("#rows").val()) + 1;
		var add = '';
		add += '<tr id="addParams">';
		add += '<td>Name: <input type="text" name="paramName_'+rows+'" id="paramName_'+rows+'"></td>';
		add += '<td>Value: <input type="text" name="paramValue_'+rows+'" id="paramValue_'+rows+'"></td>';
		add += '</tr>';

		$("#addParams").append(add);
		$("#rows").val(rows);
	}

	function generator(){
		var type = $("#dataType").val();
		var name = $("#serviceName").val();
		var table = $("#table").val();
		var field = $("#field").val();
		var rows = $("#rows").val();

		var json = {};
		json.service = {type:type, name:name};
		if($('#serviceType').val()=='getExecute'){
			json.service.insert_id = {table:table, field: field};
		}

		if(rows>0){
			json.params = {};
			for (var i = 1; i <= rows; i++) {
				var paramName = $("#paramName_"+i).val();
				var paramValue = $("#paramValue_"+i).val();
				if(paramName=='' || paramValue==''){
					continue;
				}
				json.params[paramName] = paramValue;
				
			}
		}

		$("#query").val(JSON.stringify(json));
		
		$("#showTable").hide();
		if($('#serviceType').val()=='getExecute'){
			show();
		}else{
			switch ($("#dataType").val()){
				case 'json':
				case 'xml':show();
				break;
				case 'excel':excel();
				break;
				case 'csv':csv();
				break;
			}
		}
		
	}

	function show() {
		$("#showTable").show();
	   $.ajax({
	      url : $('#serviceType').val()+'.php',
	      /*dataType: "json",*/
	      headers: { Accept : "application/json; charset=utf-8", "Content-Type": "application/json; charset=utf-8" },
	      async: true,
	      type: "POST",
	      data : $('#query').val(),
	      success: function (data) {
	        $('#show').html(data);
	      },
	      processData: false
	    });
	 }

	function excel() {
	    $.ajax({
	      url : $('#serviceType').val()+'.php',
	      type: "POST",
	      data : $('#query').val(),
	      success: function (data) {
	        window.location = 'temp/data.xlsx';
	      }
	    });
	}

	function csv() {
	    $.ajax({
	      url : $('#serviceType').val()+'.php',
	      type: "POST",
	      data : $('#query').val(),
	      success: function (data) {
	        window.location = 'temp/data.csv';
	      }
	    });
	}
</script>
</body>
</html>