<?php
	class _cha08{
		function start(){
			//user=nill; auth=1c919b2d62b178f3c713bb5431c57cc1
			$md5  = md5('nill', true);
			$auth = '1c919b2d62b178f3c713bb5431c57cc1';
			$chunks = str_split($auth,2);

			$this->secret = '';
			foreach ($chunks as $k=>$ch) {
				$bin = hex2bin($ch);
				$bin = ord($bin);
				$this->secret[$k] = chr($bin - ord($md5[$k]));
			}

			//'8e798f0377c99bc0'
			echo 'Secret is '.$this->secret.PHP_EOL;
			$md5  = md5('admin', true);
			$asd = $this->create_auth_cookie($md5,$this->secret);
			echo 'Admin hash is '.$asd.PHP_EOL;
			exit;
		}
		function create_auth_cookie($userMd5,$authKey){
			$result = '';
			for ($i = 0; $i < strlen($userMd5); $i++) {
				$result .= bin2hex(chr((ord($authKey[$i]) + ord($userMd5[$i])) % 256));
			}
			return $result;
		}
	}

	(new _cha08())->start();
