<?php

function full_path()
{
  $s = &$_SERVER;
  $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true:false;
  $sp = strtolower($s['SERVER_PROTOCOL']);
  $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
  $port = $s['SERVER_PORT'];
  $port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
  $host = isset($s['HTTP_X_FORWARDED_HOST']) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
  $host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
  $uri = $protocol . '://' . $host . substr($s['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1);
  $segments = explode('?', $uri, 2);
  $url = $segments[0];
  return $url;
}

$arr_service_type = array('getData', 'getDataList', 'getExecute', 'getOption');
$arr_data_type = array('json', 'xml', 'excel' ,'csv', 'pdf');

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("X-Frame-Options: SAMEORIGIN");

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
  <script type="text/javascript" src="style/jquery-3.1.1.min.js"></script>
  <link rel="stylesheet" href="style/style.css" />
</head>

<body>
	<form id="serviceForm" method="post">
    <button type="button" onclick="generator()">Generator</button>
    <button type="button" onclick="send()">Request Service</button>
		<table width="100%" border="0" class="data_grid">
		  <tr>
        <td width="50%" style="vertical-align: top;">
          <table class="data_grid">
            <tr>
              <td align="right">Service Type: </td>
              <td>
                <select name="serviceType" id="serviceType">
                  <?php foreach ($arr_service_type as $value) {?>
                      <option value="<?php echo $value?>"><?php  echo $value?></option>
                  <?php } ?>
                </select>
              </td>
            </tr>
            <tr>
              <td align="right">Name :</td>
              <td><input type="text" name="serviceName" id="serviceName" value="" /></td>
            </tr>
            <tr>
              <td align="right">Data Type :</td>
              <td>
                <select name="dataType" id="dataType">
                <?php foreach ($arr_data_type as $value) { ?>
                  <option value="<?php echo $value ?>"><?php echo $value ?></option>
                <?php } ?>
                </select>
              </td>
            </tr>
            <tr>
              <td align="right">Option :</td>
              <td>
                <input type="checkbox" name="select_option" id="select_option" value="1" />
              </td>
            </tr>
            <tr id="showOption" style="display: none;">
              <td colspan="2">
                <table>
                  <tr>
                    <td>
                      Option Name
                    </td>
                    <td>
                      <input type="text" name="option_name" id="option_name" value="" />
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2">
                      <span onclick="addOption();"><img src="images/add.png" /></span>

                      <table width="100%" border="1">
                        <thead>
                          <tr>
                            <td align="center">Option</td>
                          </tr>
                        </thead>
                        <tbody id="addOption">

                        </tbody>
                      </table>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>

            <tr id="showExecute" style="display: none;">
              <td>Insert ID</td>
              <td>
                <table>
                  <tr>
                    <td align="right">Table :</td>
                    <td><input type="text" name="table" id="table" value="" /></td>
                  </tr>
                  <tr>
                    <td align="right">PK Name :</td>
                    <td><input type="text" name="field" id="field" value="" /></td>
                  </tr>
                </table>
                <input type="hidden" name="rowsOption" id="rowsOption" value="0" />
              </td>
            </tr>
            <tr>
              <td colspan="2">
                <span onclick="addParams();"><img src="images/add.png" /></span>
                <table width="100%" border="1">
                  <thead>
                    <tr>
                      <td align="center">Params</td>
                      <td align="center">Value</td>
                    </tr>
                  </thead>
                  <tbody id="addParams">

                  </tbody>
                </table>
                <input type="hidden" name="rows" id="rows" value="0" />
              </td>
            </tr>

          </table>

        </td>
        <td width="50%" style="vertical-align: top;">
          <table>
            <tr>
              <td>URL : <span id="url_name"></span></td>
            </tr>
            <tr>
              <td>
                <div style="border:1px solid #999999; margin:5px 0; padding:3px;"><textarea id="query" name="query" rows="15" style="width: 100%; margin: 0; padding: 0; border-width: 0;"></textarea></div>
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

<script type="text/javascript">
  $("#serviceType").change(function(event) {
    if($(this).val()=='getExecute'){
      $("#showExecute").show();
    }else{
      $("#showExecute").hide();
    }
  });

  $("#select_option").change(function(event) {
    //alert($(this).prop('checked'));
    if($(this).prop('checked')==true){
      $("#showOption").show();
    }else{
      $("#showOption").hide();
    }
  });



  function addParams(){
//    var rows = parseInt($("#rows").val()) + 1;
    var rows = $('#addParams').find('tr').length+1;
    var add = '';
    add += '<tr id="addParams_'+rows+'">';
    add += '<td>Name '+rows+': <input type="text" name="paramName_'+rows+'" id="paramName_'+rows+'"></td>';
    add += '<td>Value: <input type="text" name="paramValue_'+rows+'" id="paramValue_'+rows+'"></td>';
    add += '</tr>';

    $("#addParams").append(add);
    $("#rows").val(rows);
  }

  function addOption(){
    var rowsOption = $('#addOption').find('tr').length+1;
//    var rowsOption = parseInt($("#rowsOption").val()) + 1;
    var addOption = '';
    addOption += '<tr id="addOption_'+rowsOption+'">';
    addOption += '<td>Value '+rowsOption+': <input type="text" name="optionValue_'+rowsOption+'" id="optionValue_'+rowsOption+'"></td>';
    addOption += '</tr>';

    $("#addOption").append(addOption);
    $("#rowsOption").val(rowsOption);
  }

  function generator(){
    var type = $("#dataType").val();
    var name = $("#serviceName").val();
    var table = $("#table").val();
    var field = $("#field").val();
    var rows = $("#rows").val();
    var rowsOption = $("#rowsOption").val();
    var url = '<?php echo full_path(); ?>'+$('#serviceType').val()+'.php';
    $('#url_name').html(url);
    //service
    var json = {};
    json.service = {type:type, name:name};
    if($('#serviceType').val()=='getExecute'){
      json.service.insert_id = {table:table, field: field};
    }

    //params
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

    //options
    if(rowsOption>0){
      json.option = {};
      json.option.option_name = $("#option_name").val();
      json.option.param = [];
      for (var i = 1; i <= rowsOption; i++) {
        var optionValue = $("#optionValue_"+i).val();
        if(optionValue==''){
          continue;
        }
        json.option.param.push(optionValue);

      }
    }

    /*json.options = {};
    $('input[name=options]').each(function () {
        if(this.checked){
          json.options[$(this).val()] = 'true';
        }
    });*/
    $("#query").val(JSON.stringify(json));
  }

  function send() {
    $("#showTable").hide();
    if($('#serviceType').val()=='getExecute'){
      show();
    } else {
      switch ($("#dataType").val()){
        case 'json':
        case 'xml':show(); break;
        case 'excel':excel(); break;
        case 'csv':csv(); break;
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