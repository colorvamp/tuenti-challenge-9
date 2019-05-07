#!/usr/bin/php
<?php
	class _cha04{
		function start(){
			$this->lines = file('php://stdin');
			$this->cases = trim(array_shift($this->lines));

			$this->case = 0;
			while ($this->cases--) {
				$this->case++;
				$this->samples = trim(array_shift($this->lines));
				$this->data = trim(array_shift($this->lines));
				$this->data = explode(' ',$this->data);

				$res = $this->resolve();
				echo 'Case #'.($this->case).': '.$res.PHP_EOL;
			}
		}
		function resolve(){
			$lcm = $this->lcm(array_values(array_unique($this->data)));

			$persons = 0;
			foreach ($this->data as $elem) {
				$persons += $lcm / $elem;
			}

			$candies = $lcm * count($this->data);
			list($candies,$persons) = $this->simplify($candies,$persons);

			return $candies.'/'.$persons;
		}
		function gcd($a,$b){
			if ($b == 0) {return $a;}
			return $this->gcd($b, $a % $b); 
		}
		function lcm($arr){ 
			$ans = $arr[0];
			$n = count($arr);

			for ($i = 1; $i < $n; $i++) {
				$ans = ((($arr[$i] * $ans)) / ($this->gcd($arr[$i], $ans))); 
			}

			return $ans; 
		}
		function simplify($num,$den) {
			$g = $this->gcd($num,$den);
			return [$num / $g,$den / $g];
		}
	}

	(new _cha04())->start();
