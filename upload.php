<?php $questionmark = "?";
echo "<".$questionmark."xml version=\"1.0\" encoding=\"utf-8\"".$questionmark.">";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>照片管理系統</title>
<style type="text/css">
	 #uploadDIV.div
	 { 
	 	position:relative;
	 }
</style>

<script type="text/javascript" src='./jquery/jquery-1.9.1.min.js'></script>
<script type="text/javascript">
	//不加這行，dropEvent取 dataTransfer時  jquery會報錯誤，OX@#$!#$%.....
	jQuery.event.fixHooks.drop = { props: [ "dataTransfer" ] };
	var contextRoot ;
	var fileList;

	function init()
	{
		if (!isHTML5()) 
		{
			showMsg('你的瀏覽器太爛了，換一個吧',true);
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
					   .on("dragleave", function(evt){   evt.preventDefault(); $("#uploadDIV").css({"border":"1px dotted red"}); })
					   .on("drop",      function(evt){   dropImageFiles(evt);  $("#uploadDIV").css({"border":"1px dotted red"}); });
	}

	function dropImageFiles(evt)
	{
		evt.preventDefault();
		fileList = evt.dataTransfer.files;
		changeDiv();
	}

	function readFiles(files)
	{
	if (typeof(files) == 'undefined' ||  files == null || files.length == 0) { alert("耍我啊～沒檔案要我送什麼？"); return; }
    	
    	function loadStartFunction(evt){
    		showMsg('開始上傳' , false);
    	}
    	
    	function progressFunction(evt){
    	}
    	
    	function transferCompleteFunction(evt){
		var jsonData = JSON.parse(evt.response);
		console.log('Server got:', jsonData);
		if (jsonData.message != "ok") {
			showMsg('出錯了,'+ jsonData.message , false);
		}else{
			showMsg('上傳成功，路徑：'+ jsonData.return.seaveSrc , false);
		}    		
    	}    	    	
    	
    	var acceptedTypes = { 'image/jpeg': true ,'image/x-raw': true ,'image/nef': true };
      //一次傳一個檔案		
    	for (var i = 0; i < files.length; i++) 
    	{
		var xhr = new XMLHttpRequest();
		//xhr.timeout = 3000; timeout 設限
		
		
		
    		xhr.upload.addEventListener("loadstart",  function(e){
      		showMsg('開始上傳' , false);
    		}, false);
    		xhr.upload.addEventListener("progress", function(e){
    		   showMsg('上傳中' , false);	
    	     }, false);
    		xhr.upload.addEventListener("load", function(e){
      		showMsg('上傳結束' , false);
    		},false);
      	
      	/*
		xhr.onload = function() 
		{
			console.log('responseText:',xhr.responseText);
			console.log('response:',xhr.response);          		
			if (this.status == 200) 
			{
				console.log('responseText:',xhr.responseText);
				console.log('response:',xhr.response);
				var jsonData = JSON.parse(this.response);
				console.log('Server got:', jsonData);
				if (jsonData.message != "ok") {
					showMsg('出錯了,'+ jsonData.message , false);
				}else{
					showMsg('上傳成功，路徑：'+ jsonData.return.seaveSrc , false);
				}
			};          		
      	};
      	
		xhr.upload.onprogress = function (event) 
		{
			if (event.lengthComputable) 
			{
				var complete = (event.loaded / event.total * 100 | 0);
           		//progress.value = progress.innerHTML = complete;
           		showMsg('傳送中', false);
         		}
        	}
        	
        	xhr.upload.loadstart = function() {
        		showMsg('開始上傳' , false);
		};
		
        	xhr.loadEnd = function() {
        		showMsg('上傳結束' , false);
		};
		
        	xhr.error = function() {
        		showMsg('上傳失敗' , false);
		};
      	
        	xhr.abort = function() {
        		showMsg('上傳中斷，來自使用者' , false);
		};      	
		*/
		
		
		xhr.open('POST',  contextRoot+"/saveImage.php",true);
				
				
    		if (acceptedTypes[files[i].type] === true)
    		{
			var formData = (!!window.FormData) ? new FormData() : null;
        		if (formData != null) 
        		{
        			formData.append('afile', files[i]);
        			formData.append('fileDate', files[i].lastModifiedDate);
        			formData.append('kind', $("#kind").val());
        			xhr.send(formData);
        			//previewfile(files[i]);
        		}
		}
		else 
		{
			showMsg("視為垃圾的檔案是不會上傳的，檔名："+files[i].name,false);
		}
	}  
  }

	function isHTML5()
	{
		return  (typeof FileReader != 'undefined') &&
				('draggable' in document.createElement('span')) &&
				(!!window.FormData) &&
				("upload" in new XMLHttpRequest);
	} 

	function changeDiv()
	{
		$("#KindDIV").show();
		$("#uploadDIV").hide();
	}
	function showMsg(msg , showDlg) 
	{
		if (showDlg) {
			alert(msg);
		}	   
		$("#msgDIV").html( $("#msgDIV").html() + msg + "<br>"); 
	}
</script>


</head>

<body onload="init();">
	<div id="msgDIV">
	</div>
	<form id="uploadForm" enctype="multipart/form-data" method="post">
	<div id="uploadDIV" style="border:1px dotted red;">
		<div>相片拖過來吧</div>
		<div style="height:200px;line-height:200px;">或是.....</div>
		<div>
			<input type="file" name="afile" style="position:absolute;opacity:0;filter:alpha(opacity=0);" multiple="multiple" accept="image/*"
			       onchange="fileList = this.files; changeDiv(); return false;">
			<input type="button" value="按這裡選檔案" onclick="this.form.file.click();">
		</div>
	</div>
	<div id="KindDIV" style="border:1px dotted red;">
		<div>
			<span>種類：</span>
			<select id="kind">
				<option value="A">2013</option>
				<option value="B">園藝</option>
				<option value="C">我的作品</option>
				<option value="D">麵包超人</option>
			</select>
			<input type="button" value="送出" onclick="readFiles(fileList);">
		</div>
	</div>	
	</form>
	
	
</body>
</html>