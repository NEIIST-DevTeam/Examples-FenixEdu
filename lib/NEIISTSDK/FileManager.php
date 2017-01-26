<?php
class FileManager {
    private $path;
    private $folder;
    private $maxsize;

    public function __construct($folder, $maxfilesize = 0) { 
        $this->path = array();
        $this->folder = $folder;
        $this->maxsize = $maxfilesize;
    }

    public function getFiles() {
        $dir_content = scandir($this->folder);
        $dir_size = count($dir_content);
        $files = array();
        for($i = 0; $i < $dir_size; $i++) {
            $filename = $dir_content[$i];
            if($filename[0] != '.' && !$this->isFolder($filename)) $files[] = $dir_content[$i];
        }
        return $files;
    }
    
    public function getFolders() {
        $dir_content = scandir($this->folder);
        $dir_size = count($dir_content);
        $files = array();
        for($i = 0; $i < $dir_size; $i++) {
            $filename = $dir_content[$i];
            if($filename[0] != '.' && $this->isFolder($filename)) $files[] = $dir_content[$i];
        }
        return $files;
    }

    public function getFolderName() {
        return basename($this->folder);
    }
    
    public function getFullPath() {
        return $this->folder;
    }

    public function fileExists($filename) {
        return in_array($filename, $this->getFiles(), true);
    }
    
    public function folderExists($folder) {
        return in_array($folder, $this->getFolders(), true);
    }
    
    public function isFolder($filename) {
        return is_dir($this->folder . '/' . $filename);
    }
    
    public function openFolder($folder) {
        if($this->folderExists($folder)) {
            $this->path[] = $this->folder;
            //TODO this is a waste of memory but I have no time to do it better...
            $this->folder .= '/' . $folder;
            return true;
        } else return false;
    }
    
    public function closeFolder() {
        if(count($this->path) > 0) {
            $this->folder = array_pop($this->path);
            return true;
        } else return false;
    }

    public function downloadFile($filename) {
        if(!$this->fileExists($filename)) return -1; //file not found
        $target = $this->folder . '/' . $filename;
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Content-Length: ' . filesize($target));
        if(readfile($target) === false) return -2; //error reading file
        else exit();
    }

    public function getUploadedFiles($varname) {
        $filesvar = $_FILES[$varname];
        $files = array();
        if(is_array($filesvar['error'])) {
            //$_FILES array comes sorted by attribute. This will sort by file.
            foreach($filesvar as $attribute => $filearray) {
                foreach($filearray as $fileindex => $value) {
                    $files[$fileindex][$attribute] = $value;
                }
            }
        } else {
            $files[] = $filesvar;
        }
        return $files;
    }

    public function storeFile($file, $overwrite = false) {
        if(!isset($file['error']) || is_array($file['error'])) {
            //whatever this is, it's not a file...
            return -7;
        }
        $error = $file['error'];
        if($file['size'] < 1) {
            //file too small
            return -6;
        }
        if($error == UPLOAD_ERR_INI_SIZE || $error == UPLOAD_ERR_FORM_SIZE || ($this->maxsize > 0 && $file['size'] > $this->maxsize)) {
            //file too big
            return -5;
        }
        if($error != UPLOAD_ERR_OK) {
            //upload failed
            return -4;
        }
        $filename = basename($file['name']);
        if($filename[0] == '.') {
            //disallow hidden files
            return -3;
        }
        $target = $this->folder . '/' . $filename;
        if(!$overwrite && file_exists($target)) {
            //file exists
            return -2;
        }
        if(!move_uploaded_file($file['tmp_name'], $target)) {
            //move failed or an attack was attempted
            if(is_uploaded_file($file['tmp_name'])) {
                //move failed
                return -1;
            } else {
                //probably an attack
                return 1;
            }
        }
        chmod($target, 0644);
        return 0;
    }
    
    public function writeFile($filename, $data, $overwrite = false) {
        if(!$overwrite && $this->fileExists($filename)) return false;
        $target = $this->folder . '/' . $filename;
        return file_put_contents($target, $data, LOCK_EX);
    }
    
    public function readFile($filename) {
        if(!$this->fileExists($filename)) return false;
        $target = $this->folder . '/' . $filename;
        return file_get_contents($target);
    }
    
    public function readTextFile($filename) {
        if(!$this->fileExists($filename)) return false;
        $target = $this->folder . '/' . $filename;
        return file($target, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }

    public function deleteFile($filename) {
        if(!$this->fileExists($filename)) return false;
        $target = $this->folder . '/' . $filename;
        return unlink($target);
    }
    
    public function newFolder($foldername) {
        if($this->folderExists($foldername)) return false;
        $target = $this->folder . '/' . $foldername;
        return mkdir($target, 0755);
    }
    
    public function renameFolder($newfoldername) {
        if(count($this->path) == 0) return false;
        $oldfoldername = $this->folder;
        $this->closeFolder();
        $target = $this->folder . '/' . $newfoldername;
        $result = rename($oldfoldername, $target);
        if(!$result) return false;
        return $this->openFolder($newfoldername);
    }
    
    public function deleteFolder() {
        if(count($this->path) == 0) return false;
        $dir_content = scandir($this->folder);
        $dir_size = count($dir_content);
        for($i = 0; $i < $dir_size; $i++) {
            $filename = $dir_content[$i];
            if($filename === "." || $filename === "..") continue;
            if($this->isFolder($filename)) {
                $this->openFolder($filename);
                $this->deleteFolder();
            } else {
                $this->deleteFile($filename);
            }
        }
        rmdir($this->folder);
        $this->closeFolder();
    }
}
?>
