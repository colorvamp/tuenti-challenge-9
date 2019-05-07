<?php
	class _cha13{
		public $file_almanac = 'almanac.data';
		function start(){
			$this->lines = file('php://stdin');
			$this->cases = trim(array_shift($this->lines));
			$this->almanac();

			$this->case = 0;
			while ($this->cases--) {
				$this->case++;
				$line = trim(array_shift($this->lines));
				$line = explode(' ',$line);

				$this->gold = array_shift($line);
				$this->find = array_shift($line);
				array_shift($line);
				$this->skills = $line;
				//if ($this->case != 2) {continue;}

				$res = $this->resolve();
				echo 'Case #'.($this->case).': '.$res.PHP_EOL;
			}
		}
		function almanac(){
			$lines = file($this->file_almanac);
			$this->characters = [];
			$this->fusions = [];

			$line = trim(array_shift($lines));
			list($c,$f) = explode(' ',$line);

			while ($c--) {
				$line  = trim(array_shift($lines));
				$line  = explode(' ',$line);
				$name  = array_shift($line);
				$level = array_shift($line);
				$cost  = array_shift($line);
				$dummy = array_shift($line);
				$skills = $line;

				$this->characters[$name] = [
					 'name'=>$name
					,'level'=>$level
					,'cost'=>$cost
					,'skills'=>$skills
				];
			}

			while ($f--) {
				$line  = trim(array_shift($lines));
				$line  = explode(' ',$line);
				$name  = array_shift($line);
				$chars = $line;

				sort($chars);
				$fname = implode('-',$chars);
				/* fname es opcional */
				$this->fusions[$name][$fname] = [
					 'chars'=>$chars
				];
			}
		}
		function resolve(){
			$_dwarfs = new _dwarfs();
			$_dwarfs->characters = $this->characters;
			$_dwarfs->fusions = $this->fusions;
			$_dwarfs->gold = $this->gold;
			$_dwarfs->find = $this->find;
			$_dwarfs->skills = $this->skills;
			return $_dwarfs->start();
		}
	}

	class _dwarfs{
		public $matrix = [];
		public $characters = [];
		public $gold = 0;
		public $find = '';
		public $skills = [];
		public $total = false;
		public $cache = [];
		function start(){
			//echo 'Necesito ['.implode(',',$this->skills).']'.PHP_EOL;
			//echo 'From ['.$this->find.'] Gold ['.$this->gold.']'.PHP_EOL;

			if (!empty($this->skills)
			 && !empty($this->characters[$this->find]['skills'])) {
				$this->skills = array_diff($this->skills,$this->characters[$this->find]['skills']);
				$this->skills = array_values($this->skills);
			}

			$res = $this->move($this->find,'init',$this->skills,0,[]);
			//print_r($res);exit;
			$cost_ok = !empty($res['cost']) && $res['cost'] <= $this->gold;
			$skills_ok = count(array_intersect($res['skills'],$this->skills)) == count($this->skills);
			return $cost_ok && $skills_ok ? $res['cost'] : 'IMPOSSIBLE';
		}
		function move($char,$from,$skills = [],$level = 0,$path = [],$info = []){
			if (!isset($this->fusions[$char])) {
				/* Si no hay fusiones que nos puedan dar una mejora de pasta
				 * en el momento que no hay skills es porq probablemente es un
				 * pair y necesitamos retornar el coste */
				return [
					 'cost'=>$this->characters[$char]['cost']
					,'skills'=>$this->characters[$char]['skills']
				];
			}

			$cost = false;
			$skls = [];
			$nextSkills = array_diff($skills,$this->characters[$char]['skills']);
			$nextLevel = $level + 1;
			foreach ($this->fusions[$char] as $f=>$fusion) {
				if (empty($nextSkills)) {
					$_name = $f;
					if (isset($this->cache[$_name])) {
						$sum = $this->cache[$_name]['sum'];
						$tsk = $this->cache[$_name]['tsk'];
						$cost_ok = $sum <= $this->gold && ($cost === false || $sum < $cost);
						if ($cost_ok) {
							$cost = $sum;
							$skls = array_unique(array_merge($tsk,$this->characters[$char]['skills']));
						}
						continue;
					}

					$fbase0 = $this->move($fusion['chars'][0],$f,$nextSkills,$nextLevel,array_merge($path,[$f]));
					$fbase1 = $this->move($fusion['chars'][1],$f,$nextSkills,$nextLevel,array_merge($path,[$f]));
					if ($fbase0['cost'] === false) {echo 'problema';exit;}
					if ($fbase1['cost'] === false) {echo 'problema';exit;}

					$sum = $fbase0['cost'] + $fbase1['cost'];
					$tsk = array_unique(array_merge($fbase0['skills'],$fbase1['skills']));

					$this->cache[$_name]['sum'] = $sum;
					$this->cache[$_name]['tsk'] = $tsk;

					//echo 'Cost of '.$level.' : '.$sum.' '.PHP_EOL;
					$cost_ok = ($cost === false || $sum < $cost);
					$skills_ok = array_merge($fbase0['skills'],$fbase1['skills']);
					$skills_ok = array_values(array_unique($skills_ok));
					$skills_ok = count(array_intersect($skills_ok,$skills)) == count($skills);
					if ($cost_ok && $skills_ok) {
						$cost = $sum;
						$skls = array_unique(array_merge($tsk,$this->characters[$char]['skills']));
					}
					continue;
				}

				$powersets = $this->powerSet($nextSkills);
				$perms = [];
				foreach ($powersets as $powerset) {
					if (count($powerset) < count($nextSkills)) {
						$complementary = array_values(array_diff($nextSkills,$powerset));
						$perms[] = [$powerset,$complementary];
						//$perms[] = [$complementary,$powerset];
					} else {
						$perms[] = [$powerset,[]];
						$perms[] = [[],$powerset];
					}
				}

				foreach ($perms as $perm) {
					$_name = $f.'-'.json_encode($perm);
					if (true && isset($this->cache[$_name])) {
						$sum = $this->cache[$_name]['sum'];
						$tsk = $this->cache[$_name]['tsk'];
						$cost_ok = ($cost === false || $sum < $cost);
						$skills_ok = count(array_intersect($tsk,$nextSkills)) == count($nextSkills);
						if ($cost_ok && $skills_ok) {
							$cost = $sum;
							$skls = array_unique(array_merge($tsk,$this->characters[$char]['skills']));
						}
						continue;
					}

					$fbase0 = $this->move($fusion['chars'][0],$f,$perm[0],$nextLevel,array_merge($path,[$f]));
					$fbase1 = $this->move($fusion['chars'][1],$f,$perm[1],$nextLevel,array_merge($path,[$f]));
					if ($fbase0['cost'] === false) {echo 'problema';exit;}
					if ($fbase1['cost'] === false) {echo 'problema';exit;}

					$sum = $fbase0['cost'] + $fbase1['cost'];
					$tsk = array_values(array_unique(array_merge($fbase0['skills'],$fbase1['skills'])));

					$this->cache[$_name]['sum'] = $sum;
					$this->cache[$_name]['tsk'] = $tsk;

					//echo 'Cost of '.$level.' '.json_encode($perm).': '.$sum.' '.PHP_EOL;
					$cost_ok = ($cost === false || $sum < $cost);
					$skills_ok = count(array_intersect($tsk,$nextSkills)) == count($nextSkills);

					if ($cost_ok && $skills_ok) {
						$cost = $sum;
						$skls = array_unique(array_merge($tsk,$this->characters[$char]['skills']));
					}
				}
			}

			$_exists_char = isset($this->characters[$char]['cost']);
			$_less_cost = ($cost !== false && $cost > $this->characters[$char]['cost']);
			$_skills_ok = count(array_intersect($this->characters[$char]['skills'],$skills)) == count($skills);
			if ($_exists_char
			 && ($cost === false || ($_skills_ok && $_less_cost)) ) {
				/* Si no hay fusiones que nos puedan dar una mejora de pasta
				 * en el momento que no hay skills es porq probablemente es un
				 * pair y necesitamos retornar el coste */
				return [
					 'cost'=>$this->characters[$char]['cost']
					,'skills'=>$this->characters[$char]['skills']
				];
			}

			return [
				 'cost'=>$cost
				,'skills'=>$skls
			];
		}
		function powerSet($in,$minLength = 1) { 
			$in = array_values($in);
			$count = count($in);
			$members = pow(2,$count);
			$return = [];
			for ($i = 0; $i < $members; $i++) {
				$b = sprintf("%0".$count."b",$i);
				$out = [];
				for ($j = 0; $j < $count; $j++) {
					if ($b{$j} == '1') $out[] = $in[$j];
				}
				if (count($out) >= $minLength) {
					$return[] = $out;
				}
			}
			return $return;
		}
		function permute($pool = []): iterable{
			$keys  = array_keys($pool);
			$count = count($pool);
			$max   = str_pad('',$count,1);
			$max   = bindec($max);
			yield $pool;
			while( --$max ){
				$t = sprintf('%0'.$count.'b',$max);
				$t = str_split($t);
				$perm = [];
				foreach( $t as $k=>$i ){
					if( !$i ){continue;}
					$perm[$keys[$k]] = $pool[$keys[$k]];
				}
				yield $perm;
			}
		}
	}






	(new _cha13())->start();
