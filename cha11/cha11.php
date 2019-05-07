<?php
	class _cha11{
		function start(){
			$this->lines = file('php://stdin');
			$this->cases = trim(array_shift($this->lines));

			$this->case = 0;
			while ($this->cases--) {
				$this->case++;
				$this->cmoons = trim(array_shift($this->lines));
				$this->moons = [];

				$distance = trim(array_shift($this->lines));
				$distance = explode(' ',$distance);
				foreach ($distance as $k=>$d) {
					$this->moons[$k]['d'] = $d;
				}
				$initials = trim(array_shift($this->lines));
				$initials = explode(' ',$initials);
				foreach ($initials as $k=>$i) {
					$this->moons[$k]['i'] = $i;
				}
				$orbitals = trim(array_shift($this->lines));
				$orbitals = explode(' ',$orbitals);
				foreach ($orbitals as $k=>$o) {
					$this->moons[$k]['p'] = $o;
				}
				$material = trim(array_shift($this->lines));
				$material = explode(' ',$material);
				foreach ($material as $k=>$m) {
					$this->moons[$k]['u'] = $m;
				}

				$this->cargo = trim(array_shift($this->lines));
				$this->range = trim(array_shift($this->lines));
				//if ($this->case != 5) {continue;}

				$res = $this->resolve();
				sort($res);
				if (empty($res)) {$res = ['None'];}
				echo 'Case #'.($this->case).': '.implode(' ',$res).PHP_EOL;
			}
		}
		function resolve(){
			//echo 'Max Range: '.$this->range.PHP_EOL;
			//echo 'Max Cargo: '.$this->cargo.PHP_EOL;
			//print_r($this->moons);
			$_dwarfs = new _dwarfs();
			$_dwarfs->matrix = $this->moons;
			$_dwarfs->maxrange = $this->range;
			$_dwarfs->maxcargo = $this->cargo;
			$_dwarfs->start();
			return $_dwarfs->fpath;
		}
	}

	class _dwarfs{
		public $matrix = [];
		public $maxrange = 0;
		public $maxcargo = 0;
		public $fpath = [];
		public $max = 0;
		function distance($a,$b,$hours = false){
			if ($a === false) {
				/* From planet */
				$arc_b = $this->matrix[$b]['i'];
				if ($hours) {
					$orb_b = (2 * pi() / $this->matrix[$b]['p']);
					$arc_b += ($orb_b * $hours);
				}

				$ax = 0;
				$ay = 0;

				$bx = $this->matrix[$b]['d'] * cos($arc_b);
				$by = $this->matrix[$b]['d'] * sin($arc_b);

				$d = sqrt(pow(($ax - $bx),2) + pow(($ay - $by),2));
				//echo 'From (t='.$hours.') [Planet] ('.$ax.','.$ay.') to ['.$b.'] ('.number_format($bx,2).','.number_format($by,2).'): '.$d.PHP_EOL;
				return $d;
			}

			if ($b === false) {
				/* Return to planet */
				$arc_a = $this->matrix[$a]['i'];
				if ($hours) {
					$orb_a = (2 * pi() / $this->matrix[$a]['p']);
					$arc_a += ($orb_a * $hours);
				}

				$ax = $this->matrix[$a]['d'] * cos($arc_a);
				$ay = $this->matrix[$a]['d'] * sin($arc_a);

				$bx = 0;
				$by = 0;

				$d = sqrt(pow(($ax - $bx),2) + pow(($ay - $by),2));
				return $d;
			}


			$arc_a = $this->matrix[$a]['i'];
			$arc_b = $this->matrix[$b]['i'];
			if ($hours) {
				$orb_a = (2 * pi() / $this->matrix[$a]['p']);
				$orb_b = (2 * pi() / $this->matrix[$b]['p']);

				$arc_a += ($orb_a * $hours);
				$arc_b += ($orb_b * $hours);
			}


			$ax = $this->matrix[$a]['d'] * cos($arc_a);
			$ay = $this->matrix[$a]['d'] * sin($arc_a);

			$bx = $this->matrix[$b]['d'] * cos($arc_b);
			$by = $this->matrix[$b]['d'] * sin($arc_b);

			$d = sqrt(pow(($ax - $bx),2) + pow(($ay - $by),2));
			/*echo 'From (t='.$hours.') '
				.'['.$a.'] ('.number_format($ax,2).','.number_format($ay,2).','.number_format($arc_a,2).') to '
				.'['.$b.'] ('.number_format($bx,2).','.number_format($by,2).','.number_format($arc_b,2).'): '.$d.PHP_EOL;//*/
			return $d;
		}
		function start(){
			foreach ($this->matrix as $step=>$dummy) {
				$d = $this->distance(false,$step,0);
				if ($d > $this->maxrange) {continue;}
				if ($this->matrix[$step]['u'] > $this->maxcargo) {continue;}

				$this->move($step,[],0,$d);
			}
		}
		function move($dest,$path = [],$total = 0,$range = 0,$time = 0,$info = []){
			if (isset($path[$dest])) {return false;}
			$time += 6;

			$path[$dest] = $this->matrix[$dest]['u'];
			$total += $this->matrix[$dest]['u'];

			//$info[] = ['moon'=>$dest,'range'=>$range,'cargo'=>$total];
			foreach ($this->matrix as $step=>$dummy) {
				if (isset($path[$step])) {continue;}
				if ($total + $this->matrix[$step]['u'] > $this->maxcargo) {continue;}

				$d = $this->distance($dest,$step,$time);
				$tmp = $range + $d;
				if ($tmp > $this->maxrange) {continue;}

				$this->move($step,$path,$total,$tmp,$time,$info);
			}

			$d = $this->distance($dest,false,$time);
			$tmp = $range + $d;
			if ($tmp > $this->maxrange) {return false;}
			if ($total > $this->max) {
				$this->max = $total;
				$this->fpath = $path;
				//print_r($info);
			}
		}
	}

	(new _cha11())->start();
