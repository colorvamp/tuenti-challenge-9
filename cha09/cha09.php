<?php
	class _cha09{
		function start(){
			$this->lines = file('php://stdin');
			$this->cases = trim(array_shift($this->lines));

			$this->case = 0;
			while ($this->cases--) {
				$this->case++;
				$this->data = trim(array_shift($this->lines));
				//if ($this->case != 24) {continue;}

				$res = $this->resolve();
				echo 'Case #'.($this->case).': '.$res.PHP_EOL;
			}
		}
		function resolve(){
			$r = preg_match('!^(?<num1>.*) OPERATOR (?<num2>.*) = (?<num3>.*)$!',$this->data,$m);
			if (!$r) {
				/* Algo huele a podrido en Dinamarca */
				exit;
			}
			$debug = false;
			$num1s = $this->split($m['num1'],$debug);
			//echo $m['num1'].PHP_EOL;
			//print_r($num1s);//exit;
			$num2s = $this->split($m['num2'],$debug);
			//echo $m['num2'].PHP_EOL;
			//print_r($num2s);//exit;
			$num3s = $this->split($m['num3'],$debug);
			//echo $m['num3'].PHP_EOL;
			//print_r($num3s);exit;

			foreach ($num1s as $num1) {
				foreach ($num2s as $num2) {
					foreach ($num3s as $num3) {
						if ($num1 + $num2 == $num3) {return $num1.' + '.$num2.' = '.$num3;}
						if ($num1 * $num2 == $num3) {return $num1.' * '.$num2.' = '.$num3;}
						if ($num1 - $num2 == $num3) {return $num1.' - '.$num2.' = '.$num3;}
					}
				}
			}
		}
		function split($num,$debug = false){
			$data = str_replace(
				 ["一","二","三","四","五","六","七","八","九","十","百","千","万"]
				,[',1',',2',',3',',4',',5',',6',',7',',8',',9',',10',',100',',1000',',10000']
				,$num);
			if ($debug) {echo $data.PHP_EOL;}
			$nums = explode(',',$data);
			$nums = array_values(array_filter($nums));

			$_dwarfs = new _dwarfs();
			$_dwarfs->matrix = $nums;
			$_dwarfs->start();

			return $_dwarfs->results;
		}
	}

	class _dwarfs{
		public $matrix = [];
		public $preresults = [];
		public $results = [];
		function reset(){
			$this->preresults = [];
			$this->results = [];
		}
		function start(){
			foreach ($this->matrix as $step=>$dummy) {
				$sub = $this->matrix;
				$this->move($step,$sub);
			}

			foreach ($this->preresults as $pre) {
				$total = 0;
				$haspre = false;
				foreach ($pre as $k=>$num) {
					if ($haspre != false) {
						$total += $haspre * $num;
						$haspre = false;
						continue;
					}
					if (isset($pre[$k + 1]) && ($pre[$k + 1] % 10) === 0 && ($num % 10) !== 0) {
						$haspre = $num;
						continue;
					}
					$total += $num;
				}
				$this->results[] = $total;
			}
			$this->results = array_unique($this->results);
		}
		function move($dest,$path = [],$result = []){
			if (($path[$dest] % 10) == 0) {
				if ($path[$dest] == 10000 && empty($result)) {
					/* 10k nunca puede ir solo, al menos debe llevar un 1 */
					return false;
				}

				if ($path[$dest] == 100 && array_intersect($result,[10])) {return false;}
				if ($path[$dest] == 1000 && array_intersect($result,[10,100])) {return false;}
				if ($path[$dest] == 10000 && array_intersect($result,[10,100,1000])) {return false;}
				if (!empty($result)) {
					$last = end($result);
					if ($last == 1 && $path[$dest] != 10000) {return false;}
				}
			} else {
				if (!empty($result)) {
					$last = end($result);
					if (($last % 10) !== 0) {return false;}
				}
			}

			$result[] = $path[$dest];
			unset($path[$dest]);

			if (empty($path)) {
				$this->preresults[] = $result;
				return false;
			}

			foreach ($path as $step=>$dummy) {
				$this->move($step,$path,$result);
			}
		}
	}

	(new _cha09())->start();
