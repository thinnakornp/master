<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <script type="text/javascript" src="jquery-3.1.1.min.js"></script>
</head>
<body>
<form id="serviceForm" method="post">
  <div>
  Service name :
  <select id="service">
    <option value="getData">getData</option>
    <option value="getDataList">getDataList</option>
    <option value="getExecute">getExecute</option>
  </select>
  </div>
  Json :
  <div><textarea id="query" name="query" rows="15" cols="150">{
  "service": {
    "name":"?",
    "type":"json",
    "insert_id": {
      "table": "?",
      "field": "?"
    }
  },
  "params": {
    "?":"?"
  }
}</textarea></div>
  <button type="button" onclick="show()">Show</button>
  <button type="button" onclick="excel()">Excel</button>
  <button type="button" onclick="csv()">CSV</button>
</form>

  <table id="excelDataTable" border="1" width="100%">
  </table>

<script>
  function show() {
    $.ajax({
      url : $('#service').val()+'.php',
      dataType: "json",
      headers: { Accept : "application/json; charset=utf-8", "Content-Type": "application/json; charset=utf-8" },
      async: true,
      type: "POST",
      data : $('#query').val(),
      success: function (data) {
        var cdata= JSON.stringify(eval(data));
        var jsonObject = jQuery.parseJSON(cdata);
        var tbl_body = "";

        $.each(jsonObject, function(i, item) {
          if(item=='[object Object]')
          {
            if(i==0) {
              tbl_body+= '<tr>';
              $.each(item, function(k, list) {
                tbl_body+='<th>'+k+'</th>';
              });
              tbl_body+= '</tr>';
            }
            tbl_body+= '<tr>';
            $.each(item, function(k, list) {
              tbl_body+='<td>'+list+'</td>';
            });
            tbl_body+= '</tr>';
          }
          else
          {
            tbl_body+= '<tr><td>'+i+'</td><td>'+item+'</td></tr>';
          }
        });
        $('#excelDataTable').html(tbl_body);
      },
      processData: false
    });
  }

  function excel() {
    $.ajax({
      url : $('#service').val()+'.php',
      type: "POST",
      data : $('#query').val(),
      success: function (data) {
        window.location = 'temp/data.xlsx';
      }
    });
  }

  function csv() {
    $.ajax({
      url : $('#service').val()+'.php',
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