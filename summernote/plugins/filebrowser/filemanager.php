<?php
session_start();


if(!$_COOKIE['_sfm_xsrf']){

	$xsrf = bin2hex(openssl_random_pseudo_bytes(16));
	setcookie('_sfm_xsrf',$xsrf);

}

error_reporting( error_reporting() & ~E_NOTICE );
setlocale(LC_ALL,'en_US.UTF-8');


//Main config
$allow_delete 				= true;
$allow_upload 				= true;
$allow_create_folder 		= true;
$allow_direct_link 			= true;

$disallowed_extensions 	= array('php');
$allowed_extensions 	= array('txt', 'gif', 'jpg', 'jpeg', 'png', 'webp', 'pdf', 'zip', 'doc', 'docx', 'odt', 'xls', 'xlsx');
$images_extensions 		= array('gif', 'jpg', 'jpeg', 'png', 'webp');
$allowed_types 			= array('text/plain', 'image/jpeg', 'image/jpg', 'image/png','image/gif', 'image/webp', 'application/pdf',	'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip');

$custom_brand 		= array("folder" => "/img/brands", "prefix" => "", "size" => "200");
$custom_goods 		= array("folder" => "/img/goods", "prefix" => "", "size" => "1024");
$custom_pages 		= array("folder" => "/img/pages", "prefix" => "", "size" => "1024");
$custom_suppliers 	= array("folder" => "/suppliers", "prefix" => "", "size" => "200", "foldercreate" => "yes");
$custom_prices 		= array("folder" => "/prices", "prefix" => "price", "size" => "200", "foldercreate" => "yes");
$custom_folder 		= array("brands"  => $custom_brand, "goods"  => $custom_goods, "pages"  => $custom_pages, "suppliers"  => $custom_suppliers, "prices"  => $custom_prices);

$hidden_extensions = [];

$PASSWORD = '';
$base_dir = '/static';




$root_dir = $_SERVER["DOCUMENT_ROOT"];
$xsrf 		= $_COOKIE["_sfm_xsrf"];

if($PASSWORD) {

	session_start();
	if(!$_SESSION['_sfm_allowed']) {

		$t = bin2hex(openssl_random_pseudo_bytes(10));
		if($_POST['p'] && sha1($t.$_POST['p']) === sha1($t.$PASSWORD)) {
			$_SESSION['_sfm_allowed'] = true;
			header('Location: ?');
		}
		echo '<html><body><form action=? method=post>PASSWORD:<input type=password name=p /></form></body></html>';
		exit;
	}

}


if(!empty($_POST['folder'])) {

	$get_folder = htmlspecialchars($_POST['folder']);
	$get_subfolder = htmlspecialchars($_POST['subfolder']);
	if (array_key_exists($get_folder, $custom_folder))
	{
		
		if (($custom_folder[$get_folder]["foldercreate"]=="yes") and (!empty($get_subfolder))) {
			@mkdir($root_dir.$base_dir.$custom_folder[$get_folder]["folder"]."/".$get_subfolder);
			$base_dir = $base_dir.$custom_folder[$get_folder]["folder"]."/".$get_subfolder;
		}
		else $base_dir = $base_dir.$custom_folder[$get_folder]["folder"];

	}
	else
		$base_dir = $base_dir;
}


function upload_file($patch, $folder, $name){

	global $images_extensions;
	global $root_dir;
	global $base_dir;
	global $file;

	if (in_array(mb_strtolower(substr(strrchr($patch['file_data']['name'], '.'), 1)), $images_extensions)) {

		include 'ImageResize.php';

		global $custom_folder;

		$size 	= $custom_folder[$folder]["size"];
		$prefix = $custom_folder[$folder]["prefix"];
		$name 	= ($name!="false") ? $name : "image";
		$image 	= new ImageResize($patch['file_data']['tmp_name']);
		$image -> resizeToWidth($size);
		$image -> save($patch['file_data']['tmp_name']);

	}
	else{
		$name 	= ($name!="false") ? $name : "file";
	}

	$file_info 			= pathinfo($patch['file_data']['name']);
	$file_extension = ".".$file_info['extension'];
	$file_dir 			= $file_info['dirname'];
	$filename_add		= "-".date(His);
	//$filename_add		= "-".rand(1000, 9999);
	$filename 			= $prefix.$name.$filename_add.$file_extension;

	var_dump(move_uploaded_file($patch['file_data']['tmp_name'], $root_dir.$base_dir.$file.'/'.$filename));

}




function alert($value, $type, $data){

	if ($type == "error")
		$message = '<div class="alert alert-error" role="alert"><span class="pr-2">'.$value.' </span><button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button></div>';
	elseif ($type == "warning")
		$message = '<div class="alert alert-warning" role="alert"><span class="pr-2">'.$value.' </span><button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button></div>';
	else
		$message = '<div class="alert alert-success" id="alert" role="alert"><span class="pr-2">'.$value.'</span></div><script>setTimeout(function () { $(".alert").alert("close");}, 4000);</script>';

	return json_encode(array("message" => $message, "data" => $data));
}


function formatBytes($bytes, $precision = 1) {

    $units 	= array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes 	= max($bytes, 0);
    $pow 		= floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow 		= min($pow, count($units) - 1);
		$bytes /= pow(1024, $pow);

  return round($bytes, $precision) . ' ' . $units[$pow];

}


function getThumb($path, $images_extensions){

		if (in_array(mb_strtolower(substr(strrchr($path, '.'), 1)), $images_extensions)) {
			$thumb = "<img class='img-thumbnail' src='thumb.php?src=".$path."&size=50x35' data-preview-image='thumb.php?src=".$path."&size=250x200&crop=0' />";
		}
		elseif (mb_strtolower(substr(strrchr($path, '.'), 1)=="pdf")) {
			$thumb = '<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-file-earmark-post" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M4 0h5.5v1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5h1V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2z"/><path d="M9.5 3V0L14 4.5h-3A1.5 1.5 0 0 1 9.5 3zM4 6.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5v-7z"/><path fill-rule="evenodd" d="M4 3.5a.5.5 0 0 1 .5-.5H7a.5.5 0 0 1 0 1H4.5a.5.5 0 0 1-.5-.5z"/></svg>';
		}
		elseif (in_array(mb_strtolower(substr(strrchr($path, '.'), 1)), ['xls', 'xlsx'])) {
			$thumb = '<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-file-earmark-spreadsheet" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M5 10H3V9h10v1h-3v2h3v1h-3v2H9v-2H6v2H5v-2H3v-1h2v-2zm1 0v2h3v-2H6z"/><path d="M4 0h5.5v1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5h1V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2z"/><path d="M9.5 3V0L14 4.5h-3A1.5 1.5 0 0 1 9.5 3z"/></svg>';
		}
		elseif (in_array(mb_strtolower(substr(strrchr($path, '.'), 1)), ['doc', 'docx', 'odt'])) {
			$thumb = '<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-file-earmark-richtext" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M4 0h5.5v1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5h1V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2z"/><path d="M9.5 3V0L14 4.5h-3A1.5 1.5 0 0 1 9.5 3z"/><path fill-rule="evenodd" d="M4.5 12.5A.5.5 0 0 1 5 12h3a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zm0-2A.5.5 0 0 1 5 10h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zm1.639-3.708l1.33.886 1.854-1.855a.25.25 0 0 1 .289-.047l1.888.974V8.5a.5.5 0 0 1-.5.5H5a.5.5 0 0 1-.5-.5V8s1.54-1.274 1.639-1.208zM6.25 6a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5z"/></svg>';
		}
		elseif (mb_strtolower(substr(strrchr($path, '.'), 1)=="zip")) {
			$thumb = '<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-file-zip" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4 0h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2zm0 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H4z"/><path fill-rule="evenodd" d="M6.5 7.5a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v.938l.4 1.599a1 1 0 0 1-.416 1.074l-.93.62a1 1 0 0 1-1.109 0l-.93-.62a1 1 0 0 1-.415-1.074l.4-1.599V7.5zm2 0h-1v.938a1 1 0 0 1-.03.243l-.4 1.598.93.62.93-.62-.4-1.598a1 1 0 0 1-.03-.243V7.5z"/><path d="M7.5 1H9v1H7.5zm-1 1H8v1H6.5zm1 1H9v1H7.5zm-1 1H8v1H6.5zm1 1H9v1H7.5V5z"/></svg>';
		}
		elseif (strrchr($path, '.')==false) {
			$thumb = '<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-folder-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a1.99 1.99 0 0 1 .342-1.31L.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3zm-8.322.12C1.72 3.042 1.95 3 2.19 3h5.396l-.707-.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139z"/></svg>';
		}
		else
			$thumb = '<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-file-earmark" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M4 0h5.5v1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5h1V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2z"/><path d="M9.5 3V0L14 4.5h-3A1.5 1.5 0 0 1 9.5 3z"/></svg>';

	return $thumb;

}


function rmrf($dir) {
	if(is_dir($dir)) {
		$dir = $dir;
		$files = array_diff(scandir($dir), ['.','..']);
		foreach ($files as $file)
			rmrf("$dir/$file");
		rmdir($dir);
	} else {
			unlink($dir);
	}
}


function arraySort($array, $sort){
	if ($sort=="asc") rsort($array);
		else sort($array);
	return $array;
}


function rename_item($newname, $oldname, $allowed_extensions, $root_dir, $base_dir){

	$path_oldname = pathinfo($oldname);
	$olddirname 	= $path_oldname['dirname'];
	$oldextension = $path_oldname['extension'];

	$path_newname = pathinfo($newname);
	$newbasename  = mb_strtolower($path_newname['basename']);
	$newdirname 	= $path_newname['dirname'];

	if ($oldextension != ""){

		if (!in_array($oldextension, $allowed_extensions)) {
				err(403,"File type not allowed (error 1)");
		}

		if ($path_newname['dirname'] == "."){
			$oldname = $root_dir.$oldname;
			$newname = $root_dir."/".$olddirname."/".$newbasename.".".$oldextension;
		}
		else{
			$oldname = $root_dir.$oldname;
			$newname = $root_dir."/".$newdirname."/".$newbasename.".".$oldextension;
		}

	}

	else{

		if ($path_newname['dirname'] == "."){
			$oldname = $root_dir.$base_dir.$oldname;
			$newname = $root_dir.$base_dir.$olddirname."/".$newbasename;
		}
		else{
			$oldname = trim($oldname, "#");
			$oldname = $root_dir.$base_dir.$oldname;
			$newname = $root_dir.$base_dir.$newdirname."/".$newbasename;
		}

	}

	$newname = iconv ('cp1251', 'UTF-8//IGNORE', $newname);

	if (rename($oldname, $newname))
		echo alert("Rename completed successfully!", "success", $file);
	else
		echo alert("Rename error!", "error", $file);

}



function is_recursively_deleteable($d) {

	$stack = [$d];
	while($dir = array_pop($stack)) {
		if(!is_readable($dir) || !is_writable($dir))
			return false;
		$files = array_diff(scandir($dir), ['.','..']);
		foreach($files as $file) if(is_dir($file)) {
			$stack[] = "$dir/$file";
		}
	}
	return true;

}



function get_absolute_path($path) {

	$path 			= str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
  $parts 			= explode(DIRECTORY_SEPARATOR, $path);
  $absolutes 	= [];
  foreach ($parts as $part) {
    if ('.' == $part) continue;
    if ('..' == $part) {
      array_pop($absolutes);
  	} else {
    	$absolutes[] = $part;
    }
  }
  return implode(DIRECTORY_SEPARATOR, $absolutes);

}

function err($code,$msg) {

	http_response_code($code);
	echo json_encode(['error' => ['code'=>intval($code), 'msg' => $msg]]);
	exit;

}

function asBytes($ini_v) {

	$ini_v = trim($ini_v);
	$s = ['g'=> 1<<30, 'm' => 1<<20, 'k' => 1<<10];
	return intval($ini_v) * ($s[mb_strtolower(substr($ini_v,-1))] ?: 1);

}






if(DIRECTORY_SEPARATOR==='\\') $root_dir = str_replace('/',DIRECTORY_SEPARATOR,$root_dir);
$tmp = get_absolute_path($root_dir  .$_REQUEST['file']);
if($tmp === false)
	err(404,'File or Directory Not Found');
if(substr($tmp, 0,strlen($root_dir)) !== $root_dir)
	err(403,"Forbidden");



$file = $_POST['file'] ?: "";

if($_GET['do'] == 'list') {

//echo $root_dir.$base_dir.$file;

	if (is_dir($root_dir.$base_dir.$file)) {

		$allowedSortBy = array("name", "size", "datetime");
		$key      		 = array_search($sortBy,$allowedSortBy);
		$sortBy   		 = $allowedSortBy[$key];
		$sortDir  		 = ($sortDir == 'desc') ? 'DESC' : 'ASC';

		$current 	= htmlspecialchars($_POST['current']);
		$rowCount	= htmlspecialchars($_POST['rowCount']);
		$search		= htmlspecialchars($_POST['searchPhrase']);
		$sortBy		= htmlspecialchars($_POST['sortBy']);
		$sortDir	= htmlspecialchars($_POST['sortDir']);

		$rowot = $rowCount * ($current-1);
		$rowdo = $rowCount;

		$directory 	= $root_dir.$base_dir.$file;
		$result 		= [];

		$files_all 	= array_diff(scandir($directory), ['.','..']);

		if (!empty($search)) {
			$files_all = array_filter($files_all, function($var) use ($search) { return preg_match("/\b$search\b/i", $var); });
		}

		$files = $files_all;

			$k=0;
			if (count($files)>0)

	    foreach($files as $entry)

				if($entry !== basename(__FILE__) && !in_array(mb_strtolower(pathinfo($entry, PATHINFO_EXTENSION)), $hidden_extensions)) {

					$k = $k+1;
					$i = $directory . '/' . $entry;
					$directory = $root_dir.$base_dir.$file;
					$path = str_ireplace($directory, "", $i);
					$stat = stat($directory . '/' . $entry);
					$dt 	= date('d.m.Y H:i:s',$stat['mtime']);

					if (is_dir($i)){


						$path 	= "#".$file.$path;
						$size 	= "";
						$thumb 	= '<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-folder-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a1.99 1.99 0 0 1 .342-1.31L.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3zm-8.322.12C1.72 3.042 1.95 3 2.19 3h5.396l-.707-.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139z"/></svg>';

						$result1[] = [
							'name' 					=> "<a class='name' href='".$path."'>".basename($i)."</a>",
		        	'datetime' 			=> $dt,
		        	'size' 					=> $size,
							'thumb' 				=> $thumb,
							'file'					=> $path,
		        	'is_dir' 				=> is_dir($i),
		        	'is_deleteable' => $allow_delete && ((!is_dir($i) && is_writable($directory)) || (is_dir($i) && is_writable($directory) && is_recursively_deleteable($i))),
		        	'is_readable' 	=> is_readable($root_dir),
		        	'is_writable' 	=> is_writable($root_dir),
		        	'is_executable' => is_executable($i),
		        ];

						$result1 = arraySort($result1, $sortDir);

					}

					else {

						$path  = $base_dir.$file.$path;
						$size  = formatBytes($stat['size']);
						$thumb = getThumb($path, $images_extensions);
						$get_return = htmlspecialchars($_POST['returnid']);

						$result2[] = [
							'name' 					=> "<a class='name' href='#' onclick=\"InsertImage('".$path."', '".$get_return."')\">".basename($i)."</a>",
							'datetime' 			=> $dt,
							'size' 					=> $size,
							'thumb' 				=> $thumb,
							'file'					=> $path,
							'is_dir' 				=> is_dir($i),
							'is_deleteable' => $allow_delete && ((!is_dir($i) && is_writable($directory)) || (is_dir($i) && is_writable($directory) && is_recursively_deleteable($i))),
							'is_readable' 	=> is_readable($root_dir),
							'is_writable' 	=> is_writable($root_dir),
							'is_executable' => is_executable($i),
						];

						$result2 = arraySort($result2, $sortDir);

					}


	    }

			if (empty($result1)) $result1 = [];
			if (empty($result2)) $result2 = [];

			$result = array_merge($result1, $result2);

			$result = array_slice($result, $rowot, $rowdo);


			$outpackage = Array
			(
				"current"  => $current,
				"rowCount" => $rowCount,
				"rows" 		 => $result,
				"total"    => count($files_all)
			);

	} else {
		err(412,"Not a Directory");
	}

	echo json_encode($outpackage);
	exit;

}



elseif ($_POST['do'] == 'delete') {

	if($_POST['confirm']=="yes") {

		if ((!empty($_POST['file'])) and (!empty($base_dir))){

			$filename = trim(htmlspecialchars($_POST['file']), "#");

			$file = $root_dir.$base_dir.$filename;

			if(is_dir($file))
				$file = $root_dir.$base_dir.$filename;
			else
				$file = $root_dir.$filename;

			rmrf($file);

			echo alert("Deletion completed successfully!", "success", $file);

			exit;

		}
		else{

			die("Error");

		}
	}
	else {

		echo alert("Delete confirmation required", "warning", $data);
		exit;

	}

}


elseif ($_POST['do'] == 'mkdir' && $allow_create_folder) {

	$dir = $_POST['name'];
	$dir = str_replace('/', '', $dir);
	if(substr($dir, 0, 2) === '..')
	    exit;
	//chdir($file);
	@mkdir($root_dir.$base_dir.$file."/".htmlspecialchars($_POST['name']));
	echo alert("Folder successfully created!", "success", htmlspecialchars($_POST['name']));
	exit;

}


elseif ($_POST['do'] == 'rename') {

	$oldname = htmlspecialchars($_POST['oldname']);
	$oldname = trim($oldname, "#");
	$newname = htmlspecialchars($_POST['newname']);
	rename_item($newname,$oldname,$allowed_extensions, $root_dir, $base_dir);
	exit;

}



elseif ($_POST['do'] == 'upload' && $allow_upload) {

	var_dump($_POST);
	var_dump($_FILES);
	var_dump($_FILES['file_data']['tmp_name']);

	$file_info 			= pathinfo(mb_strtolower($_FILES['file_data']['name']));
	$file_extension = $file_info['extension'];

	if (!in_array($file_extension, $allowed_extensions)) {
	    err(403,"Upload file type not allowed (error 1)");
	}

	if (!in_array($_FILES['file_data']["type"], $allowed_types)) {
	  	err(403,"Upload file type not allowed (error 2)");
	}

		$get_name 	= str_replace('/', '', $_POST['namefile']);
		$get_name 	= htmlspecialchars($get_name);
		$get_folder = htmlspecialchars($_POST['folder']);
		//$get_subfolder = htmlspecialchars($_POST['subfolder']);


		upload_file($_FILES, $get_folder, $get_name);

	exit;

}



elseif ($_GET['action'] == 'Download') {

	$file 		= htmlspecialchars($_GET['file']);
	$filename = basename($root_dir.$file);

		header('Content-Type: ' . mime_content_type($root_dir.$file));
		header('Content-Length: '. filesize($root_dir.$file));
		header(sprintf('Content-Disposition: attachment; filename=%s', strpos('MSIE',$_SERVER['HTTP_REFERER']) ? rawurlencode($filename) : "\"$filename\"" ));
		ob_flush();
		readfile($root_dir.$file);

	exit;

}


if($_POST) if($_COOKIE['_sfm_xsrf'] !== $_POST['xsrf']) err(403,"XSRF Failure");







$MAX_UPLOAD_SIZE = min(asBytes(ini_get('post_max_size')), asBytes(ini_get('upload_max_filesize')));

?>














<!DOCTYPE html>
<html><head>
<title>File manager</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8">




<!-- CSS only -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
<!-- JS, Popper.js, and jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>



  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-bootgrid/1.3.1/jquery.bootgrid.min.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/jquery-bootgrid/1.3.1/jquery.bootgrid.css" rel="stylesheet">




<script src='thumbpreview.js'></script>

<script>

var XSRF = (document.cookie.match('(^|; )_sfm_xsrf=([^;]*)')||0)[2];

function InsertImage(imgpath, returnid){
	parent.AddFile( imgpath, returnid);
}



function getQueryVariable(variable){
	var query = window.location.search.substring(1);
	var vars = query.split("&");
	for (var i=0;i<vars.length;i++) {
		var pair = vars[i].split("=");
		if(pair[0] == variable){return pair[1];}
	}
	return(false);
}


function renderBreadcrumbs(path) {
	var base 	= "",
			$html = $('<div/>').append( $('<a href=#><i class="fas fa-home"></i></a></div>') );
	$.each(path.split('/'),function(k,v){
		if(v) {
			var v_as_text = decodeURIComponent(v);
			$html.append( $('<span/>').text(' ▸ ') )
				.append( $('<a/>').attr('href','#/'+base+v).text(v_as_text) );
			base += v + '/';
		}
	});
	return $html;
}

function refresh(){
	$("#table").bootgrid("reload");
	$('#breadcrumb').empty().html(renderBreadcrumbs(decodeURIComponent(window.location.hash.substr(1))));
}

$(function(){
	var XSRF = (document.cookie.match('(^|; )_sfm_xsrf=([^;]*)')||0)[2];
	var MAX_UPLOAD_SIZE = <?php echo $MAX_UPLOAD_SIZE ?>;
	var $tbody = $('#list');
	var hash = window.location.hash.substr(1);
	$(window).on('hashchange',refresh).trigger('hashchange');

	$('#mkdir').submit(function(e) {


		hashval 	= decodeURIComponent(window.location.hash.substr(1)),
		$dir 			= $(this).find('[name=name]');
		parametr 	= getQueryVariable("folder");

		e.preventDefault();

		$dir.val().length && $.ajax({
			type: "POST",
			url: "?",
			dataType: "json",
			data: {'do':'mkdir',name:$dir.val(),xsrf:XSRF,folder:parametr,file:hashval}
		}).done(function(data){
				refresh();
				$("div#CreateFolder").modal("hide");
				$(".message").html(data.message);

		})
		.fail(function(data) {
				$("div#CreateFolder").modal("hide");
				$(".message").html(data.message);

		});

		$dir.val('');
		return false;

	});

	$('#rename').submit(function(e) {
			$newname = $(this).find('[name=newname]');
			$oldname = $(this).find('[name=oldname]');

			parametr 	= getQueryVariable("folder");


			e.preventDefault();

		$newname.val().length && $.ajax({
			type: "POST",
			url: "?",
			dataType: "json",
			data: {'do':'rename',newname:$newname.val(),xsrf:XSRF,folder:parametr,oldname:$oldname.val()}
		})
		.done(function(data){
				refresh();
				$("div#modal-rename").modal("hide");
				$(".message").html(data.message);

		})
		.fail(function(data) {
				$("div#modal-rename").modal("hide");
				$(".message").html(data.message);

		});

		$newname.val('');
		return false;

	});

	$('#delete').submit(function(e) {
			$file 		= $(this).find('[name=file]');
			$confirm 	= $("[name='Сonfirm']:checked").val();
			parametr 	= getQueryVariable("folder");

			e.preventDefault();

		$file.val().length && $.ajax({
			type: "POST",
			url: "?",
			dataType: "json",
			data: {'do':'delete',file:$file.val(),xsrf:XSRF,folder:parametr,confirm:$confirm}
		})
		.done(function(data){
				refresh();
				$("div#modal-delete").modal("hide");
				$(".message").html(data.message);

		})
    .fail(function(data) {
        $("div#modal-delete").modal("hide");
				$(".message").html(data.message);

    });

		$file.val('');$("[name='Сonfirm']:checked").filter('[value=yes]').prop('checked', false);
		return false;

	});


<?php if($allow_upload): ?>
	$('#file_drop_target').on('dragover',function(){
		$(this).addClass('drag_over');
		return false;
	}).on('dragend',function(){
		$(this).removeClass('drag_over');
		return false;
	}).on('drop',function(e){
		e.preventDefault();
		var files = e.originalEvent.dataTransfer.files;
		$.each(files,function(k,file) {
			uploadFile(file);
		});
		$(this).removeClass('drag_over');
	});
	$('input[type=file]').change(function(e) {
		e.preventDefault();
		$.each(this.files,function(k,file) {
			uploadFile(file);
		});
	});


	function uploadFile(file) {

		var folder 	= decodeURIComponent(window.location.hash.substr(1));
		var $row 		= renderFileUploadRow(file,folder);
		parametr 		= getQueryVariable("folder");
		parametr1 	= getQueryVariable("subfolder");
		parametr2 	= getQueryVariable("namefile");
		parametr3 	= getQueryVariable("returnid");

		$('#upload_progress').append($row);

		var fd = new FormData();
		fd.append('file_data',file);
		fd.append('file',folder);
		fd.append('xsrf',XSRF);
		fd.append('folder',parametr);
		fd.append('subfolder',parametr1);
		fd.append('namefile',parametr2);
		fd.append('returnid',parametr3);
		fd.append('do','upload');
			var xhr = new XMLHttpRequest();
			xhr.open('POST', "?");
			xhr.onload = function() {
				$row.remove();
	    		refresh();
	  		};
			xhr.upload.onprogress = function(e){
				if(e.lengthComputable) {
					$row.find('.progress').css('width',(e.loaded/e.total*100 | 0)+'%' );
				}
			};
	    xhr.send(fd);
			$('div#UploadFiles').modal('hide');

	}


	function renderFileUploadRow(file,folder) {
		return $row = $('<div/>')
			.append( $('<span class="fileuploadname" />').text( (folder ? folder+'/':'')+file.name))
			.append( $('<div class="progress_track"><div class="progress"></div></div>')  )
	};

<?php endif; ?>

})

</script>




</head>
<body>
<div class="container-fluid">

<div class="row">
	<div class="col-12 col-md-12">

		<div class="table-responsive">
			<table id="table" class="table table-condensed table-hover table-striped bootgrid-table filemanager">
				<thead>
					<tr>
						<th data-column-id="thumb" data-width="5%" data-align="center" data-header-align="center"></th>
						<th data-column-id="name">Name</th>
						<th data-column-id="size" data-sortable="false" data-width="15%">Size</th>
						<th data-column-id="datetime" data-sortable="false" data-width="15%">Date</th>
						<th data-column-id="action" data-formatter="commands" data-sortable="false" data-width="20%">Action</th>
					</tr>
				</thead>
				<tbody id="list">
				</tbody>
			</table>
		</div>

		<script>

			parametr  = getQueryVariable("folder");
			parametr1 = getQueryVariable("subfolder");
			parametr2 = getQueryVariable("namefile");
			parametr3 = getQueryVariable("returnid");


			$("#table").bootgrid({
				ajax: true,
				cache: false,
				sorting: true,
				templates: {
					header: "<div id=\"{{ctx.id}}\" class=\"{{css.header}}\"><div class=\"row\"><div class=\" mr-auto\"> <button class=\"btn btn-primary button-function\" role=\"button\" data-identifier=\"UploadFiles\" data-toggle=\"modal\" data-target=\"#UploadFiles\" data-dismiss=\"modal\" href=\"#\" > Upload</button> <button class=\"btn btn-primary button-function\" role=\"button\" data-target=\"#CreateFolder\" data-toggle=\"modal\" data-dismiss=\"modal\">New folder</button><div class=\"message alert-inline\" role=\"alert\"></div></div><div class=\"float-xs-right\"><p class=\"{{css.search}}\"></p><p class=\"{{css.actions}}\"></p></div></div><div class=\"row  mt-3\"><div id=\"breadcrumb\">&nbsp;</div></div></div>",
				},
				ajaxSettings: {

					statusCode: {
						400: function(data) {
							var data = JSON.parse(data.responseText);
							$(".message").html(data.message);
						}
					}
				},
				post: function ()
				{
						return {
								file: window.location.hash.substr(1),
								folder: parametr,
								subfolder: parametr1,
								namefile: parametr2,
								returnid: parametr3
						};
				},
				url: '?do=list',
				formatters: {

					"commands": function(column, row)
					{
					if (row.is_dir) icon_download = "hidden"; else icon_download = "";
					return "<button file=\"" + row.file + "\" class=\"btn btn-xs btn-default btn-width-1 button-function\" type=\"button\" data-identifier=\"Edit\" data-toggle=\"modal\" data-target=\"#edit\"><svg width=\"1em\" height=\"1em\" viewBox=\"0 0 16 16\" class=\"bi bi-pencil-fill\" fill=\"currentColor\" xmlns=\"http://www.w3.org/2000/svg\"><path fill-rule=\"evenodd\" d=\"M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11l.178-.178z\"/></svg></button> " +
								 "<button file=\"" + row.file + "\" class=\"btn btn-xs btn-default btn-width-1 button-function\"" + icon_download + " type=\"button\" data-identifier=\"Download\" data-toggle=\"modal\" data-target=\"#download\"><svg width=\"1em\" height=\"1em\" viewBox=\"0 0 16 16\" class=\"bi bi-download\" fill=\"currentColor\" xmlns=\"http://www.w3.org/2000/svg\"><path fill-rule=\"evenodd\" d=\"M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z\"/><path fill-rule=\"evenodd\" d=\"M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z\"/></svg></button> " +
								 "<a file=\"" + row.file + "\" class=\"btn btn-xs btn-default btn-width-1 button-function\" type=\"button\" data-identifier=\"Delete\" data-toggle=\"modal\" data-target=\"#delete\" ><svg width=\"1em\" height=\"1em\" viewBox=\"0 0 16 16\" class=\"bi bi-trash-fill\" fill=\"currentColor\" xmlns=\"http://www.w3.org/2000/svg\"><path fill-rule=\"evenodd\" d=\"M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5a.5.5 0 0 0-1 0v7a.5.5 0 0 0 1 0v-7z\"/></svg></a>";
					}
				},
				searchSettings: {
				delay: 100,
				characters: 3
			},
			requestHandler: function (request) {
				var hash = window.location.hash.substr(1);
				if (request.sort)
				{
					request.sortBy = Object.keys(request.sort)[0];
					request.sortDir = request.sort[request.sortBy];
					delete request.sort
				}
				return request;
			}
		}).on("loaded.rs.jquery.bootgrid", function (data) {

				$(this).find(".button-function").click(function (e) {
					var file    	= $(this).attr("file");
					var filename  = $(this).attr("filename");
					var action  	= $(this).attr("data-identifier");

					if (action == "Download") {
					    e.preventDefault();
							window.location.href = '?action=Download&file='+file;
					}

					if (action == "Edit") {
							e.preventDefault();
							filename = file.split('/').pop().split('.').shift();
							$("input[name='newname']").val(filename);
							$("div#modal-rename").modal("show");
							$("input[name='oldname']").val(file);
					}

					if (action == "Delete") {
						
							e.preventDefault();
							$("div#modal-delete").modal("show");
							$("input[name='xsrf']").val(XSRF);
							$("input[name='file']").val(file);
							}

				});
			});

		</script>


	</div>

</div>


<!-- Modal -->
<div class="modal fade" id="CreateFolder" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">New folder</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
				<?php if($allow_create_folder): ?>
				<form action="?" method="post" id="mkdir" />

				<div class="input-group mb-3">
					<input id="dirname" type="text" class="form-control" name="name" placeholder="Folder name" value="" />
					<div class="input-group-append">
				    <input class="btn btn-primary" type="submit" value="Create" />
				  </div>
				</div>

				</form>
				<?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="UploadFiles" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Upload</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
				<div id="upload_progress"></div>
				<?php if($allow_upload): ?>
				<div id="file_drop_target">
				 Drag and drop... or <label class="btn btn-primary" for="my-file-selector">
 				    <input id="my-file-selector" type="file" style="display:none;" multiple>
 				    select files
 				</label>
				</div>
				<?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="modal-rename" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-rename-title">Rename</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div id="modal-rename-body" class="modal-body">
				<form action="?" method="post" id="rename" />
					<div class="input-group mb-3">
						<input type="hidden" name="oldname" value="" />
						<input id="newname" type="text" class="form-control" name="newname" placeholder="New name" value="" />
						<div class="input-group-append">
							<input class="btn btn-primary" type="submit" value="Rename" />
						</div>
					</div>
				</form>
      </div>
    </div>
  </div>
</div>



<div class="modal fade" id="modal-delete" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-delete-title">Confirm deletion</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div id="modal-delete-body" class="modal-body">
				<form action="?" method="post" id="delete" />
					<div class="input-group mb-3">
						<div class="input-group-prepend">
							<label class="btn btn-default">
								<input type="radio" name="Сonfirm" value="yes" autocomplete="off"> Yes
							</label>
						</div>
						<div class="input-group-append">
						<label class="btn btn-default">
							<input type="radio" name="Сonfirm" value="no" autocomplete="off" checked> No
						</label>
						</div>
					</div>
					<div class="modal-footer">
						<input type="hidden" name="xsrf" value="" />
						<input type="hidden" name="file" value="" />
						<input type="hidden" name="action" value="delete" />
						<input class="btn btn-primary btn_input" type="submit" value="Yes" name="delete">
						<button class="btn btn-primary" data-dismiss="modal" type="button">Close</button>
					</div>
				</form>
			</div>
    </div>
  </div>
</div>

</div>
<footer></footer>
</body>
</html>
