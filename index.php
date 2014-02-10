<?php $questionmark = "?";
echo "<".$questionmark."xml version=\"1.0\" encoding=\"utf-8\"".$questionmark.">";
?>
<!DOCTYPE html>
<html lang="zh-tw">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bootstrap 101 Template</title>
<style type="text/css">
</style>
<!-- Bootstrap -->
<link href="css/bootstrap.css" rel="stylesheet" media="screen">
<script src="js/jquery-1.9.1.min.js"></script>
<script src="js/bootstrap.js"></script>
<script src="js/orther.js"></script>
<script type="text/javascript">
	//不加這行，dropEvent取 dataTransfer時  jquery會報錯誤，OX@#$!#$%.....
	jQuery.event.fixHooks.drop = { props: [ "dataTransfer" ] };
	var contextRoot ;
	var fileList;

	function init()
	{
		if (!my.isHTML5()) 
		{
			my.alert('你的瀏覽器太爛了，換一個吧');
		}
		setInitData();
		setFieldEven();
		$("#KindDIV").hide();
		$("#uploadDIV").show();
	}
	
	function setInitData()
	{
		contextRoot = "<?php echo dirname($_SERVER['PHP_SELF']); ?>";
	}

	function setFieldEven()
	{
		$("#uploadDIV").on("dragover",  function(evt){   evt.preventDefault(); $("#uploadDIV").css({"border":"3px solid  red"}); })
					.on("dragleave", function(evt){   evt.preventDefault(); $("#uploadDIV").css({"border":"0px"}); })
					.on("drop",      function(evt){   dropImageFiles(evt);  $("#uploadDIV").css({"border":"0px"}); });
	}	

	function dropImageFiles(evt)
	{
		evt.preventDefault();
		fileList = evt.dataTransfer.files;
		changeDiv();
	}	
	
	function changeDiv()
	{
		//先暗掉全部元件，再出現Dialog
		
		my.dlg("選一種吧",
                '<button class="btn btn-large dropdown-toggle" data-toggle="dropdown">照片類別<span class="caret"></span></button>' +
                '<ul class="dropdown-menu">' +
                '    <li><a href="#">2013</a></li>' +
                '    <li><a href="#">園藝</a></li>' +
                '    <li><a href="#">我的作品</a></li>' +
                '    <li><a href="#">麵包超人</a></li>' +
                '</ul>'
		);

		$(".modal").css( 'overflow', 'visible' );
		$(".modal-body").css( 'overflow-y', 'visible' );

		$('#myModal .dropdown-menu li a').on('click', function () {
			$('#myModal .dropdown-toggle').text($(this).text());
			$('#myModal').append(
            	'	<div class="modal-footer"> ' +
            	'		<a href="#" class="btn btn-large " data-dismiss="modal" aria-hidden="true" onclick="readFiles(fileList);">送出相片</a> ' +
            	'	</div> ' 
            );
			
		});								
	}

	function readFiles(files)
	{
		if (typeof(files) == 'undefined' ||  files == null || files.length == 0) { alert("耍我啊～沒檔案要我送什麼？"); return; }
    	var acceptedTypes = { 'image/jpeg': true ,'image/x-raw': true ,'image/nef': true };
    	
        //一次傳一個檔案		
    	for (var i = 0; i < files.length; i++) 
    	{
    		if (acceptedTypes[files[i].type] === true)
    		{
        		my.putImage();
			}
			else 
			{
				showMsg("視為垃圾的檔案是不會上傳的，檔名："+files[i].name,false);
			}
		}  
	}	
	
</script>
</head>
<body onload="init();">
<form id="uploadForm" enctype="multipart/form-data" method="post">	
<div id="uploadDIV" class="container">
	<div style="margin: 25% auto 1%; max-width: 200px;">
		<input type="file" id="afile" style="position:absolute;opacity:0;filter:alpha(opacity=0);z-index:-1;" multiple="multiple" accept="image/*" onchange="fileList = this.files; return false;">
		<button class="btn btn-primary btn-block" onclick="$('#afile').click(); changeDiv(); return false;"><h1><i class="icon-camera icon-white"></i> 上傳檔案</h1></button>
		<!---->  
	</div>
</div>
</form>
	
<body>
</html>