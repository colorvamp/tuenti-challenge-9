<?php
	//https://www.youtube.com/watch?v=sYCzu04ftaY
	//https://gist.github.com/akosma/9058c43c76da2e6691637b1332058ddc

	class _cha10{
		public $file_cache = 'cache.json';
		function start(){
			$this->get_risas();
			$this->get_gcds();

			$n = $this->risas['shanaehudson'];
			$p = key($this->gcds);
			$q = bcdiv($n,$p);

			$c = 0;
			foreach ([/*3,5,17,257,*/65537] as $e) {
				$data = shell_exec('python key.py "'.$n.'" "'.$e.'" "'.$p.'" "'.$q.'"');
				$file = 'id_rsa'.$c++;
				file_put_contents($file,$data);
				chmod($file,0600);
			}
			exit;	
		}
		function get_risa($name = ''){
			$idrsa = file_get_contents('evilcorppubkeys/home/'.$name.'/.ssh/id_rsa.pub');
			$num = trim(shell_exec('python unpack.py "'.$idrsa.'"'));
			$this->risas[$name] = $num;
		}
		function get_risas(){
			$folders = glob('evilcorppubkeys/home/*');
			$this->risas = [];
			foreach ($folders as $folder) {
				$name = basename($folder);
				$idrsa = file_get_contents($folder.'/.ssh/id_rsa.pub');
				$num = trim(shell_exec('python unpack.py "'.$idrsa.'"'));
				$this->risas[$name] = $num;
			}
		}
		function get_gcds(){
			$this->gcds = [];
			$this->pairs = [];
			$total = count($this->risas);
			$count = 0;
			foreach ($this->risas as $name1=>$rsa1) {
				if ($name1 !== 'shanaehudson') {continue;}
				foreach ($this->risas as $name2=>$rsa2) {
					if ($name1 == $name2) {continue;}
					$node = [$name1,$name2];
					sort($node);
					$node = implode(',',$node);
					if (isset($this->pairs[$node])) {continue;}
					$this->pairs[$node] = true;
					
					$cd = $this->bcgcd($rsa1,$rsa2);
					if ($cd == 1) {continue;}
					//if (!$this->is_prime($cd)) {continue;}
					
					$this->gcds[$cd][] = $name1;
					$this->gcds[$cd][] = $name2;
					return true;
				}
				$count++;
				echo $count.'/'.$total.PHP_EOL;
			}
			foreach ($this->gcds as $k=>&$chunk) {
				$chunk = array_unique($chunk);
			}
			unset($chunk);
			file_put_contents($this->file_cache,json_encode($this->gcds));
		}
		function keys($p, $q, $e) {
			$n = bcmul($p, $q);
			$p_1 = bcsub($p, 1);
			$q_1 = bcsub($q, 1);
			$lambda = $this->bclcm($p_1, $q_1);
			assert(1 < $e, "e must be bigger than 1");
			assert($e < $lambda, "e must be smaller than lambda");
			assert($this->bcgcd($e, $lambda) == 1, "GCD(e, lambda) must be 1");
			$d = $this->mod_inv($e, $lambda);
			assert(bcmul($e, $d) % $lambda == 1, "e * d MOD lambda must be 1");
			$public = [$e, $n];
			$private = [$d, $n];
			return [
				 'public'=>$public
				,'private'=>$private
			];
		}
		function bchexdec($hex){
			$dec = 0;
			$len = strlen($hex);
			for ($i = 1; $i <= $len; $i++) {
				$dec = bcadd($dec, bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i))));
			}
			return $dec;
		}
		function bcdechex($dec) {
			$last = bcmod($dec, 16);
			$remain = bcdiv(bcsub($dec, $last), 16);

			if($remain == 0) {
				return dechex($last);
			} else {
				return $this->bcdechex($remain).dechex($last);
			}
		}
		function bcgcd($a,$b){
			if ($b == 0) {return $a;}
			return $this->bcgcd($b, bcmod($a, $b));
		}
		function bclcm ($n, $m) {
			return bcmul($m, bcdiv($n, $this->bcgcd($n, $m)));
		}
		function gcd($a,$b){
			if ($b == 0) {return $a;}
			return $this->gcd($b, $a % $b); 
		}
		function mod_inv($a,$b) {
			$b0 = $b;
			$x0 = 0;
			$x1 = 1;
			if ($b == 1) return 1;
			while ($a > 1) {
				$q = floor(bcdiv($a, $b));
				$t = $b;
				$b = bcmod($a, $b);
				$a = $t;
				$t = $x0;
				$x0 = bcsub($x1, bcmul($q, $x0));
				$x1 = $t;
			}
			if ($x1 < 0) $x1 = bcadd($x1, $b0);
			return $x1;
		}
		function is_prime($n){for($i=~-$n**.5|0;$i&&$n%$i--;);return!$i&$n>2|$n==2;}

	}

	(new _cha10())->start();
