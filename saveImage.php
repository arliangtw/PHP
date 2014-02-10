<?php
/**
 * 圖片相似度比較
 *
 * @version     $Id: ImageHash.php 4429 2012-04-17 13:20:31Z jax $
 * @author      jax.hu
 *
 * <code>
 *  //Sample_1
 *  $aHash = ImageHash::hashImageFile('wsz.11.jpg');
 *  $bHash = ImageHash::hashImageFile('wsz.12.jpg');
 *  var_dump(ImageHash::isHashSimilar($aHash, $bHash));
 *
 *  //Sample_2
 *  var_dump(ImageHash::isImageFileSimilar('wsz.11.jpg', 'wsz.12.jpg'));
 * </code>
 */

class ImageHash {

    /**取樣倍率 1~10
     * @access public
     * @staticvar int
     * */
    public static $rate = 2;

    /**相似度允許值 0~64
     * @access public
     * @staticvar int
     * */
    public static $similarity = 80;

    /**圖片類型對應的開啟函數
     * @access private
     * @staticvar string
     * */
    private static $_createFunc = array(
        IMAGETYPE_GIF   =>'imageCreateFromGIF',
        IMAGETYPE_JPEG  =>'imageCreateFromJPEG',
        IMAGETYPE_PNG   =>'imageCreateFromPNG',
        IMAGETYPE_BMP   =>'imageCreateFromBMP',
        IMAGETYPE_WBMP  =>'imageCreateFromWBMP',
        IMAGETYPE_XBM   =>'imageCreateFromXBM',
    );


    /**從檔案建立圖片
     * @param string $filePath 檔案位址路徑
     * @return resource 當成功開啟圖片則回傳圖片 resource ID，失敗則是 false
     * */
    public static function createImage($filePath){
        if(!file_exists($filePath)){ 
        	toLog("file not exists");
        	return false; 
        }

        /*判斷檔案類型是否可以開啟*/
        $type = exif_imagetype($filePath);
        if(!array_key_exists($type,self::$_createFunc)){ 
        	toLog("file type is ".$type."not supper");
        	return false; 
        }

        $func = self::$_createFunc[$type];
        if(!function_exists($func)){ 
        	toLog("this PHP version not supper this function");
        	return false; 
        }

				toLog("createImage OK");
        return $func($filePath);
    }


    /**hash 圖片
     * @param resource $src 圖片 resource ID
     * @return string 圖片 hash 值，失敗則是 false
     * */
    public static function hashImage($src){
        if(!$src){ return false; }

        /*缩小圖片尺寸*/
        $delta = 8 * self::$rate;
        $img = imageCreateTrueColor($delta,$delta);
        imageCopyResized($img,$src, 0,0,0,0, $delta,$delta,imagesX($src),imagesY($src));

        /*計算圖片灰階值*/
        $grayArray = array();
        for ($y=0; $y<$delta; $y++){
            for ($x=0; $x<$delta; $x++){
                $rgb = imagecolorat($img,$x,$y);
                $col = imagecolorsforindex($img, $rgb);
                $gray = intval(($col['red']+$col['green']+$col['blue'])/3)& 0xFF;

                $grayArray[] = $gray;
            }
        }
        imagedestroy($img);

        /*計算所有像素的灰階平均值*/
        $average = array_sum($grayArray)/count($grayArray);

        /*計算 hash 值*/
        $hashStr = '';
        foreach ($grayArray as $gray){
            $hashStr .= ($gray>=$average) ? '1' : '0';
        }

        return self::BinToHex($hashStr);
    }


    /**hash 圖片檔案
     * @param string $filePath 檔案位址路徑
     * @return string 圖片 hash 值，失敗則是 false
     * */
    public static function hashImageFile($filePath){
        $src = self::createImage($filePath);
        $hashStr = self::hashImage($src);
        imagedestroy($src);

        return $hashStr;
    }

    /**比較兩個 hash 值，是不是相似
     * @param string $aHash A圖片的 hash 值
     * @param string $bHash B圖片的 hash 值
     * @return bool 當圖片相似則回傳 true，否則是 false
     * */
    public static function isHashSimilar_1($aHash, $bHash){
    	return isHashSimilar_2($aHash , $bHash , self::$similarity);
    }

    public static function isHashSimilar_2($aHash, $bHash , $similarity){
    	
    	$aHash = self::HexToBin($aHash);
    	$bHash = self::HexToBin($bHash);
    	$aL = strlen($aHash); $bL = strlen($bHash);
    	if ($aL !== $bL){
    		return false;
    	}
    	
    	/*計算容許落差的數量*/
    	$allowGap = $aL*(100-$similarity)/100;
    	
    	/*計算兩個 hash 值的漢明距離*/
    	$distance = 0;
    	for($i=0; $i<$aL; $i++){
    		if ($aHash{$i} !== $bHash{$i}){
    			$distance++;
    		}
    	}
    	
    	return ($distance<=$allowGap) ? true : false;    	 
    }    

    /**比較兩個圖片檔案，是不是相似
     * @param string $aHash A圖片的路徑
     * @param string $bHash B圖片的路徑
     * @return bool 當圖片相似則回傳 true，否則是 false
     * */
    public static function isImageFileSimilar($aPath, $bPath){
        $aHash = ImageHash::hashImageFile($aPath);
        $bHash = ImageHash::hashImageFile($bPath);
        return ImageHash::isHashSimilar_1($aHash, $bHash);
    }
    
    private static $_BIN_TO_HEX = array(
    		'0000'   =>'0',
    		'0001'   =>'1',
    		'0010'   =>'2',
    		'0011'   =>'3',    		
    		'0100'   =>'4',
    		'0101'   =>'5',
    		'0110'   =>'6',
    		'0111'   =>'7',
    		'1000'   =>'8',
    		'1001'   =>'9',
    		'1010'   =>'A',
    		'1011'   =>'B',    		
    		'1100'   =>'C',
    		'1101'   =>'E',
    		'1110'   =>'E',
    		'1111'   =>'F'    		
    );

    private static $_HEX_TO_BIN = array(
    		'0' => '0000',
    		'1' => '0001',
    		'2' => '0010',
    		'3' => '0011',
    		'4' => '0100',
    		'5' => '0101',
    		'6' => '0110',
    		'7' => '0111',
    		'8' => '1000',
    		'9' => '1001',
    		'A' => '1010',
    		'B' => '1011',
    		'C' => '1100',
    		'D' => '1101',
    		'E' => '1110',
    		'F' => '1111'
    );    
    
    public static function BinToHex($hashCode)
    {
    	$strl = strlen($hashCode);
    	$hashCode = str_pad($hashCode, ($strl % 4) + $strl , '0' , STR_PAD_LEFT);
    	$strl = strlen($hashCode);
    	
    	$returnStr = "";
    	
    	for($i=0; $i<$strl; $i = $i+4){
    		$returnStr = $returnStr . self::$_BIN_TO_HEX[$hashCode{$i}.$hashCode{$i+1}.$hashCode{$i+2}.$hashCode{$i+3}];
    	}
    	return $returnStr;
    }
    
    public static function HexToBin($hashCode)
    {
    	$strl = strlen($hashCode);
    	$returnStr = "";
    	 
    	for($i=0; $i<$strl; $i++){
    		$returnStr = $returnStr . self::$_HEX_TO_BIN[$hashCode{$i}];
    	}
    	return $returnStr;
    }    
    
}

class ImageDB{
	
	private static $pdo;
	function __construct(){
		global $dbFile;
		
		self::$pdo = new PDO("sqlite:" . $dbFile);
		toLog("initDB Object Create Table Code=" . 
					self::$pdo->exec(" create table if not exists FILE_LIST "." 
							(HASH_CODE TEXT , SAVE_SRC TEXT , DIRECTIONS TEXT ,  FILE_NAME TEXT , KIND INTEGER , " .
							" EXIF_TIME DATETIME , UP_TIME DATETIME , UP_USER TEXT) ")				
				);
		
		toLog("initDB Object Create Index Code=" .
				self::$pdo->exec(" create index if not exists FILE_LIST_INDEX_1 ON FILE_LIST (KIND) ")
		);

	}
	
	
	function SearchHashCode ($hashCode) {
		//找出相簿裡全部圖檔，跟上傳檔案比對
		$results = self::$pdo->query(' select count(*) from FILE_LIST ');
		if ($results->fetchColumn() > 0) {
			foreach ( self::$pdo->query(' select HASH_CODE from FILE_LIST ') as $row )	
			{
				toLog("Begin Search HashCode");
				//如果相似度到達100%
				if (ImageHash::isHashSimilar_2($hashCode, $row['HASH_CODE'],100) == true )
				{
					toLog("SearchHashCode Search a HashCode return true");
					return true;
				}
				
			}
		
		}
		toLog("SearchHashCode return false");
		return false;
	}
	
	
	function SaveHashCode ($imageDataArray) 
	{
		toLog("insert into DB :" .  implode ( "," , $imageDataArray ) );
		$stmt = self::$pdo->prepare(" INSERT INTO FILE_LIST " . 
				"        ( HASH_CODE ,  SAVE_SRC ,  DIRECTIONS , FILE_NAME ,  KIND , EXIF_TIME , UP_USER , UP_TIME) " .
				" VALUES ( :hashCode , :seaveSrc , :directions , :fileName , :Kind , :exifTime , :upUser , :nowTime ) ");
		$now = new DateTime('now', new DateTimeZone('Asia/Taipei'));
		
		toLog("SaveHashCode insert Table Code=" .
			$stmt->execute( array(':hashCode' => $imageDataArray['hashCode'], 
							':seaveSrc' => $imageDataArray['seaveSrc'],
							':directions' => $imageDataArray['directions'],
					          ':fileName' => $imageDataArray['fileName'],
					          ':Kind' => $imageDataArray['Kind'],
					          ':exifTime' => $imageDataArray['exifTime'],
					          ':upUser' => $imageDataArray['upUser'],
					          ':nowTime' => date_format($now,"Y-m-d H:i:s")
					          ) 
						)
	  );
		
	}
}


function toLog($str)
{
	error_log($str, 0);
}

function getImageDate($file,$file_Date)
{
	$_month = array(
			'Jan'   =>'1',
			'Feb'   =>'2',
			'Mar'   =>'3',
			'Apr'   =>'4',
			'May'   =>'5',
			'Jun'   =>'6',
			'Jul'   =>'7',
			'Aug'   =>'8',
			'Sep'   =>'9',
			'Oct'   =>'10',
			'Nov'   =>'11',
			'Dec'   =>'12',
	);	
	
	
	$exif = read_exif_data($file, 0, true);
	if ($exif == false || !array_key_exists("EXIF",$exif) || !array_key_exists("DateTimeOriginal",$exif["EXIF"])){
		toLog("this File no EXIF tag");
		
		$fileDateArray = explode(" ",$file_Date);
		$fileTimeArray = explode(":",$fileDateArray[4]);
		
		toLog("This FileDate: " . $fileTimeArray[0] ."," . $fileTimeArray[1] ."," . $fileTimeArray[2] ."," .
					   $_month[$fileDateArray[1]] . "," . $fileDateArray[2] ."," . $fileDateArray[3]);
		
		$dateTime = new DateTime('now', new DateTimeZone('Asia/Taipei'));
		//Fri Aug 23 2013 17:19:29 GMT+0800
		$dateTime->setDate($fileDateArray[3], $_month[$fileDateArray[1]], $fileDateArray[2]);
		$dateTime->setTime($fileTimeArray[0],$fileTimeArray[1],$fileTimeArray[1]);
		return $dateTime;
	}else{
		toLog("this File have a EXIF tag");
		return date_create($exif['EXIF']['DateTimeOriginal']);
	}
	 
	
}
//轉碼，這很重要！
function CharConv($str)
{
	global $OS_Charset;
	//unix 不能轉碼，這很重要！
	if ($OS_Charset == "UTF-8") return $str;
	return mb_convert_encoding($str , $OS_Charset , "UTF-8");
}

//負責存檔案 FileSaveImageFile(臨時檔名,檔案日期,分類,副檔名)
function FileSaveImageFile($file_tmp_name,$file_Date,$file_Kind,$file_ext)
{
	//todo: 重建規範，目錄結構規範
	global $pictureSavePath;
	global $directorySepa;
	global $_Kind;
	
	
	toLog("pictureSavePath path: " . $pictureSavePath);
	$title = CharConv($_Kind[$file_Kind]); //轉碼，這很重要！
	$path = $pictureSavePath . $directorySepa . $title; //檔案存放目錄
	toLog('fileTitle type = "' . gettype($title) . '"');
	toLog('fileTitle = "' . $title . '"');
	toLog('title=' . ($title=='2013'?'':$title) );
	toLog('this file will save to' . $path);
	
	if (!file_exists($pictureSavePath)) {
		//連主目錄都沒建立，退回重來
		toLog("path not exists : " . $pictureSavePath);
		return false;
		
	}else{
		toLog("path is exists");
		//建立目錄
		if (file_exists($path)==false && mkdir($path, 0700,true)== false && file_exists($path) == false) {
			toLog("Create Directory Error:" . $path);
			return false;				
		}
		
		//決定存檔檔名
		$ii = 0;
		$saveFileName = $path . $directorySepa . ($title=="2013"?"":($title."_")) . 
							date_format($file_Date,"Ymd") . 
							"_" . date_format($file_Date,"His") .
							"." . $file_ext; 
		//避免檔案覆蓋，如果重名，後面+1
		while (file_exists($saveFileName))
		{
			$ii++;
			$saveFileName = $path . $directorySepa . ($title=="2013"?"":($title."_")) . 
							date_format($file_Date,"Ymd") . 
							"_" . date_format($file_Date,"His") . $ii .
							"." . $file_ext; 
		}
		
		//圖檔從temp區，存入相簿目錄
		toLog('Copy '.$file_tmp_name.' to '.$saveFileName);
		move_uploaded_file($file_tmp_name, $saveFileName);			
		if (!file_exists($saveFileName))
		{
			toLog("save File Error:" . $saveFileName);
			return false;				
		}
		return $saveFileName;

	}
  
	
}

//因為pathinfo處理中文會出錯，所以自己寫一個
function my_pathinfo($path)
{
	$debug = false;
	global $directorySepa;
	$dirname = '';
	$basename = '';
	$extension = '';
	$filename = '';
	$debug?toLog("Begin trace myPathInfo"):"";

	$debug?toLog("path = " . $path):"";
	$pos = strrpos($path, $directorySepa); 
	$debug?toLog("seachSepa = " . $pos):"";

	if($pos !== false) 
	{
		$dirname = substr($path, 0, strrpos($path, $directorySepa));
		$basename = substr($path, strrpos($path, $directorySepa) + 1);
		$debug?toLog("dirname = " . $dirname):"";
		$debug?toLog("basename = " . $basename):"";
	}
	else
	{
		$basename = $path;
		$debug?toLog("basename = " . $basename):"";
  	}


	$ext = strrchr($path, '.'); 
	$debug?toLog("$ext = " . $ext):"";
  	if($ext !== false) {
  		$extension = substr($ext, 1);
  		$debug?toLog("extension = " . $extension):"";
  	}

	$filename = $basename;
	$debug?toLog("End trace myPathInfo"):"";

	return array (
		'dirname' => $dirname,
		'basename' => $basename,
		'extension' => $extension,
		'filename' => $filename
  	);
} 



function main(){
	global $pictureSavePath;
	
	$PathInfo = my_pathinfo(CharConv($_FILES["afile"]["name"]));

	$file_tmp_name = $_FILES["afile"]["tmp_name"];
	$file_name = $PathInfo['filename'];
	$file_newName = "";
	$file_type = $_FILES["afile"]["type"];
	$file_size = $_FILES["afile"]["size"];
	$file_Date = $_POST['fileDate'];
	$file_Kind = $_POST['kind'];
	$file_Ext = $PathInfo['extension'];
	$imageDB = new ImageDB();
	
	toLog("===== status ==========");
	toLog("name: " . $file_name);
	toLog("type: " . $file_type);
	toLog("size: " . $file_size);
	toLog("fileExt: " . $file_Ext);
	toLog("tmp_name: " . $file_tmp_name);
	toLog("Date: " . $file_Date);
	toLog("fileKind: " . $file_Kind);
	toLog("internal_encoding:" . mb_internal_encoding());
	toLog("DIRECTORY_SEPARATOR:" . DIRECTORY_SEPARATOR );	
	toLog("===== status ==========");
	
	toLog("Call createImage");
	$imageObject = ImageHash::createImage($file_tmp_name);
	toLog("createImage return " . (gettype($imageObject)=="boolean"?$imageObject:"Object") ."  End");
	
	if(!$imageObject)
	{
		//todo: 處理raw擋
		toLog("createImage return false");
		//回傳JSON
    		echo '{"return":"error","message":"can not upload raw file"}';				
	}else{
		try{
			//1. 算出上傳檔案的hashCode
			toLog("Call hashImage");
			$hashStr = ImageHash::hashImage($imageObject);
			toLog("hashStr:" . $hashStr);
	
			//找資料庫是否有重複的檔案
			toLog("Call SearchHashCode");
			if ($imageDB->SearchHashCode($hashStr) == true)
			{
				//todo:找到重複檔案
				toLog("find repeat File:");
				echo '{"return":"error","message":"repeat File"}';				
			}else{
				//找日期
				toLog("Call getImageDate");
				$file_Date = getImageDate($file_tmp_name , $file_Date);
				toLog("getImageDate return File Date=" . date_format($file_Date,"Y-m-d H:i:s"));

				//存入相簿目錄
				toLog("Call FileSaveImageFile");
				$file_newName = FileSaveImageFile($file_tmp_name,$file_Date,$file_Kind,$file_Ext);
				toLog("FileSaveImageFile return ". $file_newName);
				if (!$file_newName)
				{
					echo '{"return":"error","message":"save file error"}';				
					//存檔失敗
					throw new Exception("FileSaveImageFile Error");
				}	
				//存資料庫
				$imageDataArray = array(
						"hashCode" => $hashStr,
						"seaveSrc" => $file_newName,
						"directions" => '',
						"fileName" => $file_name,
						"Kind" => $file_Kind,
						"exifTime" => date_format($file_Date,"Y-m-d H:i:s"),
						"upUser" => '',
				);
				toLog("Call SaveHashCode");
				$imageDB->SaveHashCode($imageDataArray);
				
				//回傳JSON
    				echo '{"return":' . json_encode($imageDataArray) . ',"message":"ok"}';				
			}
		}catch(Exception $e){
			toLog($e->getMessage() . " : " . $e->getTraceAsString());
		}
		
		//記憶釋放
		imagedestroy($imageObject);		

	}	
	
}


?>

<?php
	//換主機時，記得調整下列參數，
	$pictureSavePath = '/tmp/home/root/usb/picture';
	$OS_Charset = 'UTF-8'; //BIG5 or UTF-8
	$dbFile = '/tmp/home/root/usb/mydb.sqlite';
	$directorySepa = '/'; //Unix路徑'/' win路徑'\\'
	
	
	$_Kind = array(
			'A'   =>'2013',
			'B'   =>'園藝',
			'C'   =>'我的作品',
			'D'   =>'麵包超人',
	);	
	
	toLog("=========Begin============");
	main();	
	toLog("==========End=============");
?> 