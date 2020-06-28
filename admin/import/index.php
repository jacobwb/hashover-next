<?php namespace HashOver;

// Copyright (C) 2018-2019 Jacob Barkdull
// This file is part of HashOver.
//
// HashOver is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// HashOver is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with HashOver.  If not, see <http://www.gnu.org/licenses/>.


	// == ACTIVATE the ERROR reporting
	ini_set('display_errors',1);ini_set('display_startup_errors',1);error_reporting(E_ALL ^ E_DEPRECATED ^ E_USER_DEPRECATED );

try {
	
	// Do some standard HashOver setup work
	require (realpath ('../../backend/standard-setup.php'));

	// View setup
	require (realpath ('../view-setup.php'));

	// process incoming data
	$foobar = '';
	$error_msg = '';
	$success_msg = '';
	
	if (!empty($_POST['op']) && $_POST['op']=='import_disqus_comments'
			&& !empty($_FILES['zip_file'])){
				
		/* for debug 
		$foobar  .= Misc::var_export($_POST,'$_POST');
		$foobar .= Misc::var_export($_FILES,'$_FILES');
		*/
		
		// remove existing previous uploaded files
		$zip_file = __DIR__.'/disqus.xml.zip';
		if (file_exists($zip_file)) @unlink($zip_file);
		$scan = scandir(__DIR__);
		foreach ($scan as $filename){
			if (preg_match('/(zip|xml)$/i',$filename)) @unlink($filename);
		}
		
		// move the uploaded file
		if (move_uploaded_file($_FILES['zip_file']['tmp_name'], $zip_file)
				&& file_exists($zip_file) && is_readable($zip_file)){
					
			$success_msg = "<p>Successfully uploaded the file: $zip_file (".(filesize($zip_file))." b)</p>";
			
			@chmod($zip_file, 0777);
			
			while ($error_msg==''){
				
				// unzip the uploaded file
				
				$zip_errors = array(
					'10' => 'ZIPARCHIVE::ER_EXISTS (File already exists)',
					'21' => 'ZIPARCHIVE::ER_INCONS (Zip archive inconsistent)',
					'18' => 'ZIPARCHIVE::ER_INVAL',
					'14' => 'ZIPARCHIVE::ER_MEMORY (Malloc failure)',
					'9' => 'ZIPARCHIVE::ER_NOENT (No such file)',
					'19' => 'ZIPARCHIVE::ER_NOZIP (Not a zip archive)',
					'11' => 'ZIPARCHIVE::ER_OPEN (Cannot open file)',
					'5' => 'ZIPARCHIVE::ER_READ (Read error)',
					'4' => 'ZIPARCHIVE::ER_SEEK (Seek error)',
					);
				$zip = new \ZipArchive;
				$res = $zip->open($zip_file);
				if ($res !== true) {
					$error_msg .= "<p>Impossible to open the ZIP file: [ ".$zip_file." ]. <br />ERROR: ".$zip_errors[$res].".</p>";
					continue;
				}
				
				$res = $zip->extractTo(__DIR__);
				if ($res !== true) {
					$error_msg .= "<p>Impossible to extract the content of the ZIP file: [ ".$zip_file." ]. <br /><br />ERROR: ".$zip_errors[$res].".</p>";
					continue;
				}

				$zip->close();
				
				// get trhe filename of the XML file
				$xml_file = '';
				$scan = scandir(__DIR__);
				foreach ($scan as $filename){
					if (preg_match('/(xml)$/i',$filename)) $xml_file = __DIR__.'/'.$filename;
				}
				if ($xml_file=='' || !is_readable($xml_file)) {
					$error_msg .= "<p>The ZIP file not seems to contain an XML file..</p>";
					continue;
				}else{
					$success_msg .= "<p>Successfully extracted the XML file: $xml_file (".(filesize($xml_file))." b)</p>";
				}
				
				// parse the XML file into an array
				$xml_arr = Misc::xml2array(file_get_contents($xml_file));
				if (is_array($xml_arr)){
					$n_theead = !empty($xml_arr['disqus']['thread']) ? count($xml_arr['disqus']['thread']) : 0;
					$n_post = !empty($xml_arr['disqus']['post']) ? count($xml_arr['disqus']['post']) : 0;
					//$success_msg .= Misc::var_export($xml_arr,'Contained <b>'.$n_theead.' hilos</b> con <b>'.$n_post.' comentarios</b>.');
					
				}else{
					$error_msg .= "<p>It was not possible to parse the XML file: <u>$xml_file</u></p>";
					continue;
				}
					
				// build comment and thread arrays to be inserted
				$comments = array();
				$threads = array(); 
				if ($n_theead > 0){
					foreach($xml_arr['disqus']['thread'] as $arr){
						// __attr__ → dsq:id → 8068271327
						// link → value → https://podcastlinux.com/95-Linux-Express/
						if (empty($arr['__attr__']['dsq:id']) || empty($arr['link']['value'])) continue;
						$threads[$arr['__attr__']['dsq:id']] = array(
							'url'=> $arr['link']['value'],
							'title'=> $arr['title']['value']
						);
					}
				}
				
				if ($n_post > 0){
					$used_thread_ids = array();
					foreach($xml_arr['disqus']['post'] as $arr){
						// __attr__ → dsq:id → 8068271327
						// thread → __attr__ → dsq:id → 8068271327
						if (empty($arr['__attr__']['dsq:id']) || empty($arr['thread']['__attr__']['dsq:id'])) continue;
						$thread_id = $arr['thread']['__attr__']['dsq:id'];
						if (!isset($threads[$thread_id])) continue;
						$parent_id = !empty($arr['parent']['__attr__']['dsq:id']) ? $arr['parent']['__attr__']['dsq:id'] : '';
						if (!empty($parent_id) && !isset($posts[$parent_id])) continue;
						$author_name = !empty($arr['author']['name']['value']) ? $arr['author']['name']['value'] : 
							(!empty($arr['author']['username']['value']) ? $arr['author']['username']['value'] : 'Anonymous');
						$isDeleted 	= !empty($arr['isDeleted']['value'] && $arr['isDeleted']['value']=='true') ? true : false;
						$isSpam 	= !empty($arr['isSpam']['value']    && $arr['isSpam']['value']=='true') ? true : false;
						if ($isDeleted || $isSpam) continue;
						
						$posts[$arr['__attr__']['dsq:id']] = array(
							'thread_id'=> $thread_id,
							'parent_id'=> $parent_id,
							'body'=> $arr['message']['value'],
							'date'=> $arr['createdAt']['value'],
							'status'=> '',
							'name'=> $author_name,
						);
						$used_thread_ids[$thread_id] = 1;
					}
				}
				unset($xml_arr); // to save memmory

				// remove not used threads (because in the XML from Disqus come a lkot of URL-pages without comments
				if (count($posts)>0){
					$used_threads = array();
					foreach ($threads as $id=>$arr){
						if (isset($used_thread_ids[$id])) $used_threads[$id] = $arr;
					}
					unset($threads); // to save memmory
					$success_msg .= "<p>This file contain <b>".count($posts)."</b> comments in <b>".count($used_threads)."</b> threads (<a href='#' onclick=\"document.getElementById('xml_content').style.display='block';\">click here to see</a>).</p>"
									."<div id='xml_content' style='display:none;padding:1rem;margin:1rem;background-color:rgb(128,128,128);opacity:0.8;border-radius:5px;'>";
					$success_msg .= Misc::var_export($used_threads,count($used_threads).' threads:');
					$success_msg .= Misc::var_export($posts,count($posts).' posts:');
					$success_msg .= "</div>";
				}else{
					$error_msg .= "<p>Successfully parsed file, but not found any comment ! :-(</p>";
				}
					
				// added to database
				list($n_added,$a_msg) = Misc::add_imported_comments($hashover->setup,$used_threads,$posts);
				$success_msg .= "<p>It has been imported <b>".$n_added['comments']."</b> new comments in <b>".$n_added['threads']."</b> new threads.</p>";
				if (count($a_msg)>0){
					$error_msg .= _var_export($a_msg,'Errors adding '.count($a_msg).' comments');
				}
					
				// display
				$success_msg .= "<hr /><br />";
				
				break;
			}
			
		}else{
			$error_msg = "There was not possible to move the uploaaded file.";
		}
	}

	// Template data
	$disqus_url = 'https://disqus.com/admin/discussions/export/';
	$template = array (
		'title'		=> $hashover->locale->text['import-bt'],
		'sub-title'	=> $hashover->locale->text['import-desc'],
		'import-bt'	=> $hashover->locale->text['import-bt'],
		'import-file-desc'	=> str_replace("<a>","<a href='$disqus_url' target='_blank'>",$hashover->locale->text['import-file-desc']),
		'foobar' 	=> ''.$foobar,
		'error_msg' => $error_msg,
		'success_msg' => $success_msg,
		'form_display' => 'block',
	);

	// if the storage format is not SQL, by now we cannot to run any import
	if ($hashover->setup->dataFormat != 'sql'){
		$template['error_msg'] = "<p style='color:red;text-align:center;'>By now the importation from Disqus only works if Hashover is configured to work with SQL format to store comments.</p>";
		$template['form_display'] = 'none';
	}
	
	// Load and parse HTML template
	echo parse_templates ('admin', 'import.html', $template, $hashover);

} catch (\Exception $error) {
	echo Misc::displayException ($error);
}
