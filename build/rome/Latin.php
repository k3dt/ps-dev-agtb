<?php

class Latin{
	
	function __construct($rome, $translationPath, $baseDir){
		$this->translationPath = $translationPath;
		$this->rome = $rome;
		$this->baseDir = realpath($baseDir);
		if(empty($this->startPath))$this->startPath = $this->baseDir;
		
	}
	
	function updateGit(){
		$this->cwd = getcwd();
		 if(!file_exists($this->translationPath)){
                        chdir(dirname($this->translationPath));
                        passthru("git clone git@github.com:sugarcrm/translations");
         }
		//chdir($this->translationPath);
                chdir(realpath("$this->cwd" ."/". "$this->translationPath"));
		passthru("git pull origin master");
	}
	
	function copyFiles($path){
		require_once('config_override.php');
		$langConfig = array();
		$dir = new DirectoryIterator($path);
		foreach ($dir as $fileInfo) {
    		if($fileInfo->isDot()) continue;
    		if($fileInfo->isDir()){
    			 $this->copyFiles($fileInfo->getPathname());
    		}else{
    			foreach($this->rome->config['builds'] as $flav=>$build){
    				
					if(empty($build['languages']))continue;
					foreach($build['languages'] as $lang){
	   					if(strpos($fileInfo->getFilename(), $lang. '.') !== false){
    						$path = $fileInfo->getPathname();
    						$path = realpath($path);
    						$path = str_replace($this->baseDir . '/','', $path);
    						$this->rome->setOnlyOutput($flav);
    						$this->rome->setStartPath($this->startPath);
    						$en_usPath =$this->rome->buildPath . '/' . $flav . '/'. str_replace($lang . '.', 'en_us.',$this->rome->cleanPath($this->baseDir . '/' . $path));
    						if(file_exists($en_usPath)){
    							$this->rome->buildFile($this->baseDir . '/' . $path, $this->startPath);
    							
    						}
	   					}
	   					$langConfig[$lang] = (!empty($sugar_config['languages'][$lang]))?$sugar_config['languages'][$lang]:$lang;
	   					
					}
					file_put_contents($this->rome->buildPath . '/' . $flav . '/sugarcrm/install/lang.config.php', '<?php' . "\n" . '$config["languages"]=' . var_export($langConfig, true)  . ';');
					
    			}
    		}
		}	
		
	}
	
	function copyTranslations(){
		$this->updateGit();
		
		$tmp_path=realpath("$this->cwd" ."/". "$this->translationPath");
		$this->copyFiles($tmp_path);
		chdir($this->cwd);
	} 
	
	
}




?>
