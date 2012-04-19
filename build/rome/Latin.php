<?php

class Latin{

	function __construct($rome, $translationPath, $baseDir, $ver){
		$this->translationPath = $translationPath;
		$this->rome = $rome;
		$this->baseDir = realpath($baseDir);
		$this->ver = $ver;
		if(empty($this->startPath))$this->startPath = $this->baseDir;

	}

	function updateGit(){
		$this->cwd = getcwd();
		 if(!file_exists($this->translationPath)){
                        chdir(dirname($this->translationPath));
                        passthru("git clone git@github.com:sugarcrm/translations");
         }
        chdir(realpath($this->cwd ."/". $this->translationPath));
		
		passthru("git checkout master");
		passthru("git fetch -a");
		passthru("git reset --hard");
		if(preg_match("/(\d+).(\d+).(\d+)(.*)/", $this->ver, $matchesVer)){
			$translationBranch = $matchesVer[1] . "_" . $matchesVer[2];
			exec("git branch -r", $remoteBranches);

			if (preg_grep("/$translationBranch/", $remoteBranches)) {
				passthru("git checkout origin/" . $translationBranch);
			} else {
				passthru("git checkout origin/" . "master");
			} 
		}else{
		    passthru("git checkout master");
		    passthru("git pull origin master");
		}
	}

	function copyFiles($path){
		require($this->cwd ."/". $this->translationPath . '/config_override.php');
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
    						$this->rome->setOnlyBuild($flav);
    						$this->rome->setStartPath($this->startPath);
    						$en_usPath =$this->rome->buildPath . '/' . $flav . '/'. str_replace($lang . '.', 'en_us.',$this->rome->cleanPath($this->baseDir . '/' . $path));
    						if(file_exists($en_usPath)){
    							$this->rome->buildFile($this->baseDir . '/' . $path, $this->startPath);

    						}
	   					}
	   					$langConfig[$lang] = (!empty($sugar_config['languages'][$lang]))?$sugar_config['languages'][$lang]:$lang;

					}
					$license = $this->rome->config['license'][$flav];
					file_put_contents($this->rome->buildPath . '/' . $flav . '/sugarcrm/install/lang.config.php', '<?php' . "\n$license\n" . '$config["languages"]=' . var_export($langConfig, true)  . ';');

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
