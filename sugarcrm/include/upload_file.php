<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: upload_file.php 55278 2010-03-15 13:45:13Z jmertic $
 * Description:
 ********************************************************************************/
require_once('include/externalAPI/ExternalAPIFactory.php');

class UploadFile
{
	var $field_name;
	var $stored_file_name;
	var $original_file_name;
	var $temp_file_location;
	var $use_soap = false;
	var $file;
	var $file_ext;
	protected static $url = "upload/";

	protected static $filesError = array(
			UPLOAD_ERR_OK => 'UPLOAD_ERR_OK - There is no error, the file uploaded with success.',
			UPLOAD_ERR_INI_SIZE => 'UPLOAD_ERR_INI_SIZE - The uploaded file exceeds the upload_max_filesize directive in php.ini.',
			UPLOAD_ERR_FORM_SIZE => 'UPLOAD_ERR_FORM_SIZE - The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
			UPLOAD_ERR_PARTIAL => 'UPLOAD_ERR_PARTIAL - The uploaded file was only partially uploaded.',
			UPLOAD_ERR_NO_FILE => 'UPLOAD_ERR_NO_FILE - No file was uploaded.',
			5 => 'UNKNOWN ERROR',
			UPLOAD_ERR_NO_TMP_DIR => 'UPLOAD_ERR_NO_TMP_DIR - Missing a temporary folder.',
			UPLOAD_ERR_CANT_WRITE => 'UPLOAD_ERR_CANT_WRITE - Failed to write file to disk.',
			UPLOAD_ERR_EXTENSION => 'UPLOAD_ERR_EXTENSION - A PHP extension stopped the file upload.',
			);

	function UploadFile ($field_name = '')
	{
		// $field_name is the name of your passed file selector field in your form
		// i.e., for Emails, it is "email_attachmentX" where X is 0-9
		$this->field_name = $field_name;
	}

	function set_for_soap($filename, $file) {
		$this->stored_file_name = $filename;
		$this->use_soap = true;
		$this->file = $file;
	}

	/**
	 * Get URL for a document
	 * @deprecated
	 * @param string stored_file_name File name in filesystem
	 * @param string bean_id note bean ID
	 * @return string path with file name
	 */
	public static function get_url($stored_file_name, $bean_id)
	{
		if ( empty($bean_id) && empty($stored_file_name) ) {
            return self::$url;
		}

		return self::$url . $bean_id;
	}

	/**
	 * Get URL of the uploaded file related to the document
	 * @param SugarBean $document
	 * @param string $type Type of the document, if different from $document
	 */
	public static function get_upload_url($document, $type = null)
	{
	    if(empty($type)) {
	        $type = $document->module_dir;
	    }
	    return "index.php?entryPoint=download&type=$type&id={$document->id}";
	}

	/**
	 * Try renaming a file to bean_id name
	 * @param string $filename
	 * @param string $bean_id
	 */
	protected static function tryRename($filename, $bean_id)
	{
	    $fullname = "upload://$bean_id.$filename";
	    if(file_exists($fullname)) {
            if(!rename($fullname,  "upload://$bean_id")) {
                $GLOBALS['log']->fatal("unable to rename file: $fullname => $bean_id");
            }
	        return true;
	    }
	    return false;
	}

	/**
	 * builds a URL path for an anchor tag
	 * @param string stored_file_name File name in filesystem
	 * @param string bean_id note bean ID
	 * @return string path with file name
	 */
	static public function get_file_path($stored_file_name, $bean_id, $skip_rename = false)
	{
		global $locale;

        // if the parameters are empty strings, just return back the upload_dir
		if ( empty($bean_id) && empty($stored_file_name) ) {
            return "upload://";
		}

		if(!$skip_rename) {
    		self::tryRename(rawurlencode($stored_file_name), $bean_id) ||
    		self::tryRename(urlencode($stored_file_name), $bean_id) ||
    		self::tryRename($stored_file_name, $bean_id) ||
    		self::tryRename($locale->translateCharset( $stored_file_name, 'UTF-8', $locale->getExportCharset()), $bean_id);
		}

		return "upload://$bean_id";
	}

	/**
	 * duplicates an already uploaded file in the filesystem.
	 * @param string old_id ID of original note
	 * @param string new_id ID of new (copied) note
	 * @param string filename Filename of file (deprecated)
	 */
	public static function duplicate_file($old_id, $new_id, $file_name)
	{
		global $sugar_config;

		// current file system (GUID)
		$source = "upload://$old_id";

		if(!file_exists($source)) {
			// old-style file system (GUID.filename.extension)
			$oldStyleSource = $source.$file_name;
			if(file_exists($oldStyleSource)) {
				// change to new style
				if(copy($oldStyleSource, $source)) {
					// delete the old
					if(!unlink($oldStyleSource)) {
						$GLOBALS['log']->error("upload_file could not unlink [ {$oldStyleSource} ]");
					}
				} else {
					$GLOBALS['log']->error("upload_file could not copy [ {$oldStyleSource} ] to [ {$source} ]");
				}
			}
		}

		$destination = "upload://$new_id";
		if(!copy($source, $destination)) {
			$GLOBALS['log']->error("upload_file could not copy [ {$source} ] to [ {$destination} ]");
		}
	}

	public function get_upload_error()
	{
	    if(isset($this->field_name) && isset($_FILES[$this->field_name]['error'])) {
	        return $_FILES[$this->field_name]['error'];
	    }
	    return false;
	}

	/**
	 * standard PHP file-upload security measures. all variables accessed in a global context
	 * @return bool True on success
	 */
	public function confirm_upload()
	{
		global $sugar_config;

		if(empty($this->field_name) || !isset($_FILES[$this->field_name])) {
		    return false;
		}

		if($_FILES[$this->field_name]['error'] != UPLOAD_ERR_OK) {
		    if($_FILES[$this->field_name]['error'] != UPLOAD_ERR_NO_FILE) {
                $GLOBALS['log']->error('File upload error: '.self::$filesError[$_FILES[$this->field_name]['error']]);
		    }
		    return false;
		}

		if(!is_uploaded_file($_FILES[$this->field_name]['tmp_name'])) {
			return false;
		} elseif($_FILES[$this->field_name]['size'] > $sugar_config['upload_maxsize']) {
		    $GLOBALS['log']->fatal("ERROR: uploaded file was too big: max filesize: {$sugar_config['upload_maxsize']}");
			return false;
		}

		if(!UploadStream::writable()) {
		    $GLOBALS['log']->fatal("ERROR: cannot write to upload directory");
			return false;
		}

		$this->mime_type = $this->getMime($_FILES[$this->field_name]);
		$this->stored_file_name = $this->create_stored_filename();
		$this->temp_file_location = $_FILES[$this->field_name]['tmp_name'];

		return true;
	}

	function getMimeSoap($filename){

		if( function_exists( 'ext2mime' ) )
		{
			$mime = ext2mime($filename);
		}
		else
		{
			$mime = ' application/octet-stream';
		}
		return $mime;

	}

	function getMime($_FILES_element)
	{
		$filename = $_FILES_element['name'];
        $file_ext = pathinfo($filename, PATHINFO_EXTENSION);

        //If no file extension is available and the mime is octet-stream try to determine the mime type.
        $recheckMime = empty($file_ext) && ($_FILES_element['type']  == 'application/octet-stream');

		if( $_FILES_element['type'] && !$recheckMime) {
			$mime = $_FILES_element['type'];
		} elseif( function_exists( 'mime_content_type' ) ) {
			$mime = mime_content_type( $_FILES_element['tmp_name'] );
		} elseif( function_exists( 'ext2mime' ) ) {
			$mime = ext2mime( $_FILES_element['name'] );
		} else {
			$mime = ' application/octet-stream';
		}
		return $mime;
	}

	/**
	 * gets note's filename
	 * @return string
	 */
	function get_stored_file_name()
	{
		return $this->stored_file_name;
	}

	/**
	 * creates a file's name for preparation for saving
	 * @return string
	 */
	function create_stored_filename()
	{
		global $sugar_config;

		if(!$this->use_soap) {
			$stored_file_name = $_FILES[$this->field_name]['name'];
			$this->original_file_name = $stored_file_name;

			/**
			 * cn: bug 8056 - windows filesystems and IIS do not like utf8.  we are forced to urlencode() to ensure that
			 * the file is linkable from the browser.  this will stay broken until we move to a db-storage system
			 */
			if(is_windows()) {
				// create a non UTF-8 name encoding
				// 176 + 36 char guid = windows' maximum filename length
				$end = (strlen($stored_file_name) > 176) ? 176 : strlen($stored_file_name);
				$stored_file_name = substr($stored_file_name, 0, $end);
				$this->original_file_name = $_FILES[$this->field_name]['name'];
			}
		    $stored_file_name = str_replace("\\", "", $stored_file_name);
		} else {
			$stored_file_name = $this->stored_file_name;
			$this->original_file_name = $stored_file_name;
		}

		$this->file_ext = pathinfo($stored_file_name, PATHINFO_EXTENSION);
        // cn: bug 6347 - fix file extension detection
        foreach($sugar_config['upload_badext'] as $badExt) {
            if(strtolower($this->file_ext) == strtolower($badExt)) {
                $stored_file_name .= ".txt";
                $this->file_ext="txt";
                break; // no need to look for more
            }
        }
		return $stored_file_name;
	}

	/**
	 * moves uploaded temp file to permanent save location
	 * @param string bean_id ID of parent bean
	 * @return bool True on success
	 */
	function final_move($bean_id)
	{
	    $destination = $bean_id;
	    if(substr($destination, 0, 9) != "upload://") {
            $destination = "upload://$bean_id";
	    }
        if($this->use_soap) {
        	if(!file_put_contents($destination, $this->file)){
        	    $GLOBALS['log']->fatal("ERROR: can't save file to $destination");
//FIXME:        		die("ERROR: can't save file to $destination");
                return false;
        	}
		} else {
			if(!UploadStream::move_uploaded_file($_FILES[$this->field_name]['tmp_name'], $destination)) {
			    $GLOBALS['log']->fatal("ERROR: can't move_uploaded_file to $destination. You should try making the directory writable by the webserver");
// FIXME:				die("ERROR: can't move_uploaded_file to $destination. You should try making the directory writable by the webserver");
                return false;
			}
		}
		return true;
	}

	function upload_doc($bean, $bean_id, $doc_type, $file_name, $mime_type)
	{
		if(!empty($doc_type)&&$doc_type!='Sugar') {
			global $sugar_config;
	        $destination = clean_path($this->get_upload_path($bean_id));
	        sugar_rename($destination, str_replace($bean_id, $bean_id.'_'.$file_name, $destination));
	        $new_destination = clean_path($this->get_upload_path($bean_id.'_'.$file_name));

		    try{
                $this->api = ExternalAPIFactory::loadAPI($doc_type);

                if ( isset($this->api) && $this->api !== false ) {
                    $result = $this->api->uploadDoc(
                        $bean,
                        $new_destination,
                        $file_name,
                        $mime_type
                        );
                } else {
                    $result['success'] = FALSE;
                    // FIXME: Translate
                    $GLOBALS['log']->error("Could not load the requested API (".$doc_type.")");
                    $result['errorMessage'] = 'Could not find a proper API';
                }
            }catch(Exception $e){
                $result['success'] = FALSE;
                $result['errorMessage'] = $e->getMessage();
                $GLOBALS['log']->error("Caught exception: (".$e->getMessage().") ");
            }
            if ( !$result['success'] ) {
                sugar_rename($new_destination, str_replace($bean_id.'_'.$file_name, $bean_id, $new_destination));
                $bean->doc_type = 'Sugar';
                // FIXME: Translate
                if ( ! is_array($_SESSION['user_error_message']) )
                    $_SESSION['user_error_message'] = array();

                $error_message = isset($result['errorMessage']) ? $result['errorMessage'] : $GLOBALS['app_strings']['ERR_EXTERNAL_API_SAVE_FAIL'];
                $_SESSION['user_error_message'][] = $error_message;

            }
            else {
                unlink($new_destination);
            }
        }

	}

	/**
	 * returns the path with file name to save an uploaded file
	 * @param string bean_id ID of the parent bean
	 * @return string
	 */
	function get_upload_path($bean_id)
	{
		$file_name = $bean_id;

		// cn: bug 8056 - mbcs filename in urlencoding > 212 chars in Windows fails
		$end = (strlen($file_name) > 212) ? 212 : strlen($file_name);
		$ret_file_name = substr($file_name, 0, $end);

		return "upload://$ret_file_name";
	}

	/**
	 * deletes a file
	 * @param string bean_id ID of the parent bean
	 * @param string file_name File's name
	 */
	static public function unlink_file($bean_id,$file_name = '')
	{
	    if(file_exists("upload://$bean_id$file_name")) {
            return unlink("upload://$bean_id$file_name");
	    }
    }

    public function get_upload_dir()
    {
        return "upload://";
    }

    /**
     * Return real FS path of the file
     * @param string $path
     */
    public static function realpath($path)
    {
       if(substr($path, 0, 9) == "upload://") {
           $path = UploadStream::path($path);
       }
       $ret = realpath($path);
       return $ret?$ret:$path;
    }

    /**
     * Return path of uploaded file relative to uploads dir
     * @param string $path
     */
    public static function relativeName($path)
    {
        if(substr($path, 0, 9) == "upload://") {
            $path = substr($path, 9);
        }
        return $path;
    }
}

class UploadStream
{
    const STREAM_NAME = "upload";
    protected static $upload_dir;

    public static function getDir()
    {
        if(empty(self::$upload_dir)) {
            self::$upload_dir = rtrim($GLOBALS['sugar_config']['upload_dir'], '/\\');
            if(empty(self::$upload_dir)) {
                self::$upload_dir = "upload";
            }
            if(!file_exists(self::$upload_dir)) {
                sugar_mkdir(self::$upload_dir, 0755, true);
            }
        }
        return self::$upload_dir;
    }

    public static function writable()
    {
        return is_writable(self::getDir());
    }

    public function register()
    {
        stream_register_wrapper(self::STREAM_NAME, __CLASS__, 0);
    }

    public static function path($path)
    {
        return self::getDir()."/".substr($path, strlen(self::STREAM_NAME)+3); // cut off upload://
    }

    public static function ensureDir($path, $writable = true)
    {
        $path = self::path($path);
        if(!is_dir($path)) {
           return sugar_mkdir($path, 0755, true);
        }
        return true;
    }

    public function dir_closedir()
    {
        closedir($this->dirp);
    }

    public function dir_opendir ($path, $options )
    {
        $this->dirp = opendir(self::path($path));
        return !empty($this->dirp);
    }

    public function dir_readdir()
    {
        return readdir($this->dirp);
    }

    public function dir_rewinddir()
    {
        return rewinddir($this->dirp);
    }

    public function mkdir($path, $mode, $options)
    {
        return mkdir(self::path($path), $mode, ($options&STREAM_MKDIR_RECURSIVE) != 0);
    }

    public function rename($path_from, $path_to)
    {
        return rename(self::path($path_from), self::path($path_to));
    }

    public function rmdir($path, $options)
    {
        return rmdir(self::path($path));
    }

    public function stream_cast ($cast_as)
    {
        return $this->fp;
    }

    public function stream_close ()
    {
        fclose($this->fp);
        return true;
    }

    public function stream_eof ()
    {
        return feof($this->fp);
    }
   public function stream_flush ()
    {
        return fflush($this->fp);
    }

    public function stream_lock($operation)
    {
        return flock($this->fp, $operation);
    }

    public function stream_open($path, $mode)
    {
        $fullpath = self::path($path);
        if($mode == 'r') {
            $this->fp = fopen($fullpath, $mode);
        } else {
            // if we will be writing, try to transparently create the directory
            $this->fp = @fopen($fullpath, $mode);
            if(!$this->fp && !file_exists(dirname($fullpath))) {
                mkdir(dirname($fullpath), 0755, true);
                $this->fp = fopen($fullpath, $mode);
            }
        }
        return !empty($this->fp);
    }

    public function stream_read($count)
    {
        return fread($this->fp, $count);
    }

    public function stream_seek($offset, $whence = SEEK_SET)
    {
        return fseek($this->fp, $offset, $whence) == 0;
    }

    public function stream_set_option($option, $arg1, $arg2)
    {
        return true;
    }

    public function stream_stat()
    {
        return fstat($this->fp);
    }

    public function stream_tell()
    {
        return ftell($this->fp);
    }
    public function stream_write($data)
    {
        return fwrite($this->fp, $data);
    }

    public function unlink($path)
    {
        unlink(self::path($path));
        return true;
    }

    public function url_stat($path, $flags)
    {
        return @stat(self::path($path));
    }

    public static function move_uploaded_file($upload, $path)
    {
        return move_uploaded_file($upload, self::path($path));
    }
}

