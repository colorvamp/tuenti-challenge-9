#!/usr/bin/php
<?php
	class _cha07{
		function start(){
			$this->lines = file('php://stdin');
			$this->cases = trim(array_shift($this->lines));

			$this->case = 0;
			while ($this->cases--) {
				$this->case++;
				$this->samples = trim(array_shift($this->lines));
				$this->data = [];
				while ($this->samples--) {
					$this->data[] = trim(array_shift($this->lines));
				}

				$this->samples = trim(array_shift($this->lines));
				$this->modified = [];
				while ($this->samples--) {
					$this->modified[] = trim(array_shift($this->lines));
				}


				$this->body = implode($this->data);
				$this->body = explode('------',$this->body);
				$this->prefix = $this->body[0].'---';
				$this->chain  = '---'.$this->body[1];

				$this->newc = implode($this->modified);
				$this->newc = explode('------',$this->newc);
				$this->prefxc = $this->newc[0].'---';
				$this->origc  = '---'.$this->newc[1];
				

				$res = $this->resolve();
				echo 'Case #'.($this->case).': '.trim($res).PHP_EOL;
			}
		}
		function addWidth(){
			$this->width += 1;
			$this->testc = $this->prefxc.str_pad('',($this->width + 1),"0",STR_PAD_LEFT).$this->origc;
		}
		function resolve(){
			//$this->prefix = "From: Mr Darth Vader;To: Luke Skywalker;---";
			//$this->prefxc = "From: Jabba the Hutt;To: Luke Skywalker;---";

			//$this->chain = "---I'm your father.";
			//$this->origc = "---I'm your father.";
			$this->testc = $this->prefxc.$this->origc;
			$this->hash_chain = $this->notSoComplexHash($this->prefix.$this->chain);

			$this->padding = strlen($this->prefix);
			$this->width = -1;
			//print_r($this->hash_chain);
			$this->calc();
			$this->order();

			//var_dump($this->width);
			//print_r($this->testc);
			//print_r($this->notSoComplexHash($this->testc));
			return substr($this->testc,$this->padding,$this->width + 1);
		}
		function calc(){
			foreach (range(0,15) as $c) {
				$value = $this->column_diff($c);
				while ($c < $this->padding) {$c += 16;}

				$idx = $c;
				if ($value < 0) {$value += 256;}
				if ($value != 0 && ($idx - $this->padding) > $this->width) {
					$this->addWidth();
					return $this->calc();
				}

				while ($value > 74) {
					$this->testc[$idx] = chr(122);
					$value -= 74;
					$value = (($value + 128) % 256) - 128;
					if ($value < 0) {$value += 256;}
					$idx += 16;

					if ($value != 0 && ($idx - $this->padding) > $this->width) {
						$this->addWidth();
						return $this->calc();
					}
				}

				$value = $this->column_diff($idx % 16);
				if ($value < 0) {$value += 256;}

				$this->testc[$idx] = chr($value + ord($this->testc[$idx]));
			}
		}
		function order(){
			foreach (range(0,15) as $c) {
				while ($c < $this->padding) {$c += 16;}
				$tmp = [];
				while ($c <= $this->width + $this->padding) {
					$tmp[$c] = $this->testc[$c];
					$c += 16;
				}
				asort($tmp);

				$keys = array_keys($tmp);
				sort($keys);
				$final = array_combine($keys,$tmp);
				foreach ($final as $pos=>$value) {
					$this->testc[$pos] = $value;
				}
			}
		}
		function column_diff($c = 0){
			return $this->hash_chain[$c] - $this->column_sum($c);
		}
		function column_sum($c = 0){
			$l = strlen($this->testc);
			$nan = 0;
			do {
				$nan += ord($this->testc[$c]);
				$nan = (($nan + 128) % 256) - 128;
				$c += 16;
			} while ($c < $l);

			return $nan;
		}
		function paint(){
			$chars = str_split($this->test);
			$chunks = array_chunk($chars,16);
			foreach ($chunks as $chunk) {
				foreach ($chunk as $char) {
					echo str_pad($char,4,' ',STR_PAD_LEFT).' | ';
				}
				echo PHP_EOL;
			}
			foreach ($this->modfhash as $char) {
				echo str_pad('',4,'-',STR_PAD_LEFT).'-+-';
			}
			echo PHP_EOL;
			foreach ($this->modfhash as $char) {
				echo str_pad($char,4,' ',STR_PAD_LEFT).' | ';
			}
			echo PHP_EOL;
			exit;
		}
		function notSoComplexHash($str = ''){
			$str = str_split($str);
			$hash = array_fill_keys(range(0,15),0);
			for ($i = 0; $i < count($str); $i++) {
				$nan = $hash[$i % 16] + ord($str[$i]);
				$nan = (($nan + 128) % 256) - 128;
				$hash[$i % 16] = $nan;
			}
			return $hash;
		}
	}

	(new _cha07())->start();
