<?php
	class _cha15{
		public $mode = 'SUBMIT';
		public $debug = false;
		function start(){
			$this->remote();exit;
			$this->lines = file('php://stdin');
			$this->cases = trim(array_shift($this->lines));

			$this->case = 0;
			while ($this->cases--) {
				$this->case++;
				$this->table = trim(array_shift($this->lines));
				$this->workers = $this->table * 8;
				$this->c = trim(array_shift($this->lines));

				$this->restrictions = [];
				while ($this->c--) {
					$line = trim(array_shift($this->lines));
					$line = explode(' ',$line);
					$this->restrictions[] = $line;
				}

				//if ($this->case != 19) {continue;}

				$res = $this->resolve();
				echo 'Case #'.($this->case).': '.implode(PHP_EOL,$res).PHP_EOL;
			}
		}
		function remote(){
			$fp = fsockopen('52.49.91.111',1888);
			stream_set_timeout($fp, 2);
			fwrite($fp,$this->mode.PHP_EOL);

			$this->cases = trim(fgets($fp,1024));

			$this->case = 0;
			while ($this->cases--) {
				echo $this->case.PHP_EOL;
				$this->case++;
				$this->table = trim(fgets($fp,1024));
				if (empty($this->table)) {$this->table = trim(fgets($fp,1024));}
				if (strpos($this->table,'Invalid table configuration')) {
					var_dump($blob);
					echo $this->table.PHP_EOL;
					exit;
				}

				echo 'reading'.PHP_EOL;
				$blob = $this->table.PHP_EOL;
				$this->c = trim(fgets($fp,20));
				$blob .= $this->c.PHP_EOL;

				$this->restrictions = [];
				while ($this->c--) {
					$line = trim(fgets($fp,1024));
					$blob .= $line.PHP_EOL;
					$line = explode(' ',$line);
					$this->restrictions[] = $line;
				}

				echo 'go resolve'.PHP_EOL;
				$res = $this->resolve();
				fwrite($fp,implode(PHP_EOL,$res).PHP_EOL);
			}

			$winner = trim(fgets($fp,1024));
			echo $winner.PHP_EOL;
			exit;

		}
		function resolve(){
			if ($this->debug) {echo 'La mesa es de '.$this->table.PHP_EOL;}
			$tmp = [];
			foreach ($this->restrictions as $rest) {
				sort($rest);
				$k = implode('-',$rest);
				$tmp[$k] = $rest;
			}
			$this->restrictions = $tmp;

			$this->restgrouping = [];
			foreach ($this->restrictions as $rest) {
				if (!isset($this->restgrouping[$rest[0]]['count'])) {$this->restgrouping[$rest[0]]['count'] = 1;}
				if (!isset($this->restgrouping[$rest[1]]['count'])) {$this->restgrouping[$rest[1]]['count'] = 1;}
				$this->restgrouping[$rest[0]]['center'] = $rest[0];
				$this->restgrouping[$rest[0]]['mates'][] = $rest[1];
				$this->restgrouping[$rest[0]]['count']++;
				$this->restgrouping[$rest[1]]['center'] = $rest[1];
				$this->restgrouping[$rest[1]]['mates'][] = $rest[0];
				$this->restgrouping[$rest[1]]['count']++;
			}

			uasort($this->restgrouping,function($a,$b){
				if ($a['count'] == $b['count']) {
					return 0;
				}
				return ($a['count'] < $b['count']) ? 1 : -1;
			});

			$this->cache = [];
			$this->tables = [];
			$gidx = 0;
			foreach ($this->restgrouping as $k=>$group) {
				$idx = false;
				if (isset($this->cache[$group['mates'][0]])) {
					$idx = $this->cache[$group['mates'][0]];
				}
				if (isset($group['mates'][1]) && isset($this->cache[$group['mates'][1]])) {
					$idx = $this->cache[$group['mates'][1]];
				}
				if (isset($this->cache[$k])) {
					$idx = $this->cache[$k];
				}

				if ($idx === false) {$idx = 'i'.$gidx++;}

				if (empty($this->tables[$idx])) {
					if ($group['count'] == 3) {
						$this->tables[$idx] = $group['mates'][0].','.$k.','.$group['mates'][1];
						$this->cache[$group['mates'][0]] = $idx;
						$this->cache[$group['mates'][1]] = $idx;
						$this->cache[$k] = $idx;
					} else {
						$this->tables[$idx] = $group['mates'][0].','.$k;
						$this->cache[$group['mates'][0]] = $idx;
						$this->cache[$k] = $idx;
					}
				} else {
					if ($this->is_already_done($idx,$group)) {continue;}

					$position = '!('.'(?<right>,'.$k.'$)'.'|(?<left>^'.$k.',)'.')!';
					$r = preg_match($position,$this->tables[$idx],$m);
					if (!$r) {
						/* Puede faltar el del medio */
						$mid_not_found = '!('
							.'(?<right>,('.$group['mates'][0].'|'.$group['mates'][1].')$)'
							.'|(?<left>^('.$group['mates'][0].'|'.$group['mates'][1].'),)'
							.')!';
						if (preg_match($mid_not_found,$this->tables[$idx],$m)) {
							$addto = !empty($m['right']) ? 'right' : 'left';
							$this->tables[$idx] = $addto == 'right' ? $this->tables[$idx].','.$k : $k.','.$this->tables[$idx];
							$this->cache[$k] = $idx;
							if ($this->debug) {echo '[middle] added to '.$addto.': '.$k.' in '.$this->tables[$idx].PHP_EOL;}
							if ($this->is_already_done($idx,$group)) {continue;}

							$r = preg_match($position,$this->tables[$idx],$m);
						}
					}
					

					if (!$r) {
						var_dump($k);
						print_r($group);
						print_r($position);
						var_dump($idx);
						print_r($this->tables);
						echo 'algo raro 1 ';
						exit;
					}
					$addto = !empty($m['right']) ? 'right' : 'left';

					$diff = array_diff($group['mates'],array_keys($this->cache));
					if (count($diff) !== 1) {
						if (empty($diff)) {
							/* Hay que conectar 2 cadenas */
							$this->merge($group);
							continue;
						}
						var_dump($this->tables[$idx]);
						var_dump($diff);
						echo 'algo raro 2';
						exit;
					}
					$diff = reset($diff);

					$this->tables[$idx] = $addto == 'right' ? $this->tables[$idx].','.$diff : $diff.','.$this->tables[$idx];
					$this->cache[$diff] = $idx;
					if ($this->debug) {echo 'added to '.$addto.': '.$diff.' in '.$this->tables[$idx].PHP_EOL;}
				}
			}

			/* INI-Remove duplicates */
			$this->cache2 = [];
			foreach ($this->tables as $k=>$table) {
				$table = explode(',',$table);
				$table = array_unique($table);
				foreach ($table as $node) {
					if (isset($this->cache2[$node])) {
						var_dump($node);
						var_dump($this->cache2[$node]);
						var_dump($k);
						echo 'problema';exit;
					}
					$this->cache2[$node] = $k;
				}
				$this->tables[$k] = [
					 'count'=>count($table)
					,'nodes'=>implode(',',$table)
				];
			}
			/* END-Remove duplicates */

			$this->check_both_caches();

			uasort($this->tables,function($a,$b){
				if ($a['count'] == $b['count']) {
					return 0;
				}
				return ($a['count'] < $b['count']) ? 1 : -1;
			});

			$this->maxworkers = $this->table * 8;
			$counts = array_map(function($n){return $n['count'];},$this->tables);

			$this->c = 0;
			$nidx = 0;
			while($this->c < $this->maxworkers){
				$this->c++;
				if (isset($this->cache[$this->c])) {continue;}
				$idx = 'n'.$nidx++;

				$this->tables[$idx] = [
					 'count'=>1
					,'nodes'=>$this->c
				];
			}

			$counts = array_map(function($n){return $n['count'];},$this->tables);
			$r = $this->kpartitionstart($counts,8);

			$tmp = [];
			foreach ($this->parts as $part) {
				$line = '';
				foreach ($part as $elem) {
					$idx = array_search($elem,$counts);
					$line .= $this->tables[$idx]['nodes'].',';
					unset($counts[$idx]);
				}
				$tmp[] = ['nodes'=>substr($line,0,-1)];
			}
			$this->tables = $tmp;

			$result = array_map(function($n){
				return $n['nodes'];
			},$this->tables);

			return $result;
		}	
		function kpartition($groups = [], $row = 0, $nums = [], $target = 0, $numbersGroups = []){
			if ($row < 0) {
				$this->parts = $numbersGroups;
				return true;
			}
 
			$newsNumberGroups = $numbersGroups;
			$v = $nums[$row--];
			for ($i = 0; $i < count($groups); $i++) {
				if ($groups[$i] + $v <= $target) {
					$newsNumberGroups[$i][] = $v;
					$groups[$i] += $v;
					if ($this->kpartition($groups,$row,$nums,$target,$newsNumberGroups)) {return true;}
					unset($newsNumberGroups[$i][array_search($v,$newsNumberGroups[$i])]);
					$groups[$i] -= $v;
				}
				if ($groups[$i] == 0) {break;}
			}
			return false;
		}
		function kpartitionstart($nums = [], $k = 8){
			$sum = array_sum($nums);
			if ($sum % $k > 0) {return false;}
			$target = $sum / $k;
 
			sort($nums);
			$row = count($nums) - 1;
			if ($nums[$row] > $target ){return false;}
			while ($row >= 0 && $nums[$row] == $target) {
				$row--;
				$k--;
			}
 
			$groups = array_fill_keys(range(0,$k),0);
			return $this->kpartition(array_fill(0, $k, 0),$row,$nums,$target);
		}

		function merge($group = []){
			$str[] = $this->cache[$group['center']];
			$str[] = $this->cache[$group['mates'][0]];
			if (isset($group['mates'][1])) {$str[] = $this->cache[$group['mates'][1]];}
			$str = array_values(array_unique($str));

			$base = false;
			$move = false;
			foreach ($str as $idx) {
				$position = '!('.'(?<right>,'.$group['center'].'$)'.'|(?<left>^'.$group['center'].',)'.')!';
				if (preg_match($position,$this->tables[$idx],$m)) {
					$base = ['idx'=>$idx,'addto'=>!empty($m['right']) ? 'right' : 'left'];
				}
				$position = '!('.'(?<right>,'.$group['mates'][0].'$)'.'|(?<left>^'.$group['mates'][0].',)'.')!';
				$r = preg_match($position,$this->tables[$idx],$m);
				if (!$r && isset($group['mates'][1])) {
					$position = '!('.'(?<right>,'.$group['mates'][1].'$)'.'|(?<left>^'.$group['mates'][1].',)'.')!';
					$r = preg_match($position,$this->tables[$idx],$m);
				}
				if ($r) {
					$move = ['idx'=>$idx,'moveFrom'=>!empty($m['right']) ? 'right' : 'left'];
				}
			}

			if ($move['idx'] == $base['idx']) {
				return true;
			}

			$moveNodes = explode(',',$this->tables[$move['idx']]);
			if ($move['moveFrom'] == 'right') {$moveNodes = array_reverse($moveNodes);}

			foreach ($moveNodes as $node) {
				if ($base['addto'] == 'right') {$this->tables[$base['idx']] .= ','.$node;}
				else {$this->tables[$base['idx']] = $node.','.$this->tables[$base['idx']];}
				$this->cache[$node] = $base['idx'];
			}


			unset($this->tables[$move['idx']]);
			return true;
		}
		function is_already_done($idx,$group){
			$this->already = '';
			if ($group['count'] == 3) {
				$this->already = '!((^|,)'.$group['mates'][0].','.$group['center'].','.$group['mates'][1].'($|,)'
					.'|(^|,)'.$group['mates'][1].','.$group['center'].','.$group['mates'][0].'($|,)'
					.'|^'.$group['center'].','.$group['mates'][0].',[0-9,]+,'.$group['mates'][1].'$'
					.'|^'.$group['center'].','.$group['mates'][1].',[0-9,]+,'.$group['mates'][0].'$'

					.'|^'.$group['mates'][1].',[0-9,]+,'.$group['mates'][0].','.$group['center'].'$'
					.'|^'.$group['mates'][0].',[0-9,]+,'.$group['mates'][1].','.$group['center'].'$'

					.')!';
			} else {
				$this->already = '!((^|,)'.$group['mates'][0].','.$group['center'].'($|,)'
					.'|(^|,)'.$group['center'].','.$group['mates'][0].'($|,))!';
			}
			if (preg_match($this->already,$this->tables[$idx])) {
				//echo 'ya está done'.PHP_EOL;
				return true;
			}
		}
		function check_both_caches(){
			foreach ($this->cache as $node=>$idx) {
				if (!isset($this->cache2[$node]) || $this->cache2[$node] != $idx) {
					var_dump($node);
					echo 'both cache error';
					exit;
				}
			}
		}
		function permute($pool = []): iterable{
			$keys  = array_keys($pool);
			$count = count($pool);
			$max   = str_pad('',$count,1);
			$max   = bindec($max);
			yield $pool;
			while (--$max) {
				$t = sprintf('%0'.$count.'b',$max);
				$t = str_split($t);
				$perm = [];
				$sum = 0;
				foreach ($t as $k=>$i) {
					if (!$i) {continue;}
					$sum += $pool[$keys[$k]];
					//if ($sum > 24) {continue 2;}
					$perm[$keys[$k]] = $pool[$keys[$k]];
				}
				yield $perm;
			}
		}
	}


	(new _cha15())->start();
