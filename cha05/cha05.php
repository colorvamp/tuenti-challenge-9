#!/usr/bin/php
<?php
	class _cha05{
		public $keys = [
			 ''
			,'1','2','3','4','5','6','7','8','9','0'
			,'q','w','e','r','t','y','u','i','o','p'
			,'a','s','d','f','g','h','j','k','l',';'
			,'z','x','c','v','b','n','m',',','.','-'
		];
		public $map = [];
		public $chunks = [];
		function start(){
			array_shift($this->keys);
			$this->chunks = array_chunk($this->keys,10);
			foreach ($this->chunks as $y=>$chunk) {
				foreach ($chunk as $x=>$letter) {
					$this->map[$letter] = ['x'=>$x,'y'=>$y];
				}
			}

			$this->lines = file('php://stdin');
			$this->cases = trim(array_shift($this->lines));

			$this->case = 0;
			while ($this->cases--) {
				$this->case++;
				$this->base = trim(array_shift($this->lines));
				$this->sample = trim(array_shift($this->lines));

				$res = $this->resolve();
				echo 'Case #'.($this->case).': '.$res.PHP_EOL;
			}
		}
		function resolve(){
			$this->sample = strtolower($this->sample);
			$last = substr($this->sample,-1);
			$init = strtolower($this->base);

			$pos1 = $this->map[$init];
			$pos2 = $this->map[$last];

			$x = $pos1['x'] - $pos2['x'];
			$y = $pos1['y'] - $pos2['y'];


			$this->decoded = '';
			foreach (str_split($this->sample) as $letter) {
				if ($letter == ' ') {$this->decoded .= ' ';continue;}
				$pos = $this->map[$letter];
				$pos['x'] += $x;
				$pos['y'] += $y;
				if ($pos['x'] < 0) {$pos['x'] += 10;}
				if ($pos['x'] >= 10) {$pos['x'] = $pos['x'] % 10;}
				if ($pos['y'] < 0) {$pos['y'] += 4;}
				if ($pos['y'] >= 4) {$pos['y'] = $pos['y'] % 4;}
				$shift = $this->chunks[$pos['y']][$pos['x']];
				$this->decoded .= $shift;
			}

			return strtoupper($this->decoded);
		}
	}

	(new _cha05())->start();
