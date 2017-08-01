<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>report data</title>
	<link rel="stylesheet" type="text/css" href="/assets/jquery-easyui-1.5/themes/default/easyui.css">
	<link rel="stylesheet" type="text/css" href="/assets/jquery-easyui-1.5/themes/icon.css">
	<script type="text/javascript" src="/assets/jquery-easyui-1.5/jquery.min.js"></script>
	<script type="text/javascript" src="/assets/jquery-easyui-1.5/jquery.easyui.min.js"></script>
</head>
<body class="easyui-layout">

	<div data-options="region:'north'" style="height:50px"></div>
	<div data-options="region:'center',title:'Main Title',iconCls:'icon-ok'">
		<div class="easyui-tabs" data-options="fit:true,border:false,plain:true">
			<div title="DataGrid" style="padding:5px">
				<table id="dg" class="easyui-datagrid"
					   data-options="url:'/ajax/data',method:'get',singleSelect:true,pageSize:20,fit:true,fitColumns:true,toolbar:'#tb'" rownumbers="true" pagination="true">
					<thead>
					<tr>
						<th data-options="field:'datetime'" sortable="true" width="10">时间</th>
						<th data-options="field:'area'" width="5">地区</th>
						<th data-options="field:'title'" width="25">描述</th>
						<th data-options="field:'address'" width="40">地址</th>
						<th data-options="field:'flood'" width="30">楼层</th>
						<th data-options="field:'tag'" width="30">描述</th>
						<th data-options="field:'price_info'" width="20">价格详情</th>
						<th data-options="field:'price'" sortable="true" width="8">价格</th>
					</tr>
					</thead>
				</table>
			</div>
		</div>
	</div>

    <div id="tb" style="padding:5px;height:auto">
        <div>
            日期: <input id="datetime" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser" style="width:120px">
            &nbsp;
            地区:
            <select id="area" class="easyui-combobox" panelHeight="auto">
                <?php foreach($cities as $city) :?>
                    <?='<option value="'.$city.'">'.$city.'</option>'?>
                <?php endforeach;?>
            </select>
            <a href="#" class="easyui-linkbutton" iconCls="icon-search" id="search">Search</a>
        </div>
    </div>

    <Script type="application/javascript">
        $(function(){
            
            $('#search').on('click', function(){
                $('#dg').datagrid('load', {
                    datetime: $('#datetime').datebox('getValue'),
                    area: $('#area').combobox('getValue')
                });
            });
        })

        function myformatter(date){
            var y = date.getFullYear();
            var m = date.getMonth()+1;
            var d = date.getDate();
            return y+'-'+(m<10?('0'+m):m)+'-'+(d<10?('0'+d):d);
        }
        function myparser(s){
            if (!s) return new Date();
            var ss = (s.split('-'));
            var y = parseInt(ss[0],10);
            var m = parseInt(ss[1],10);
            var d = parseInt(ss[2],10);
            if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
                return new Date(y,m-1,d);
            } else {
                return new Date();
            }
        }
    </Script>

</body>