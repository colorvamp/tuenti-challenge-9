#!/usr/bin/php
<?php
	class _cha03{
		function start(){
			$this->lines = file('php://stdin');
			$this->cases = trim(array_shift($this->lines));

			$case = 0;
			while ($this->cases--) {
				$this->info = trim(array_shift($this->lines));
				list($this->width,$this->height,$this->count_folds,$this->count_punches) = explode(' ',$this->info);
				$this->folds = [];
				$this->punches = [];
				while ($this->count_folds--) {
					$this->line = trim(array_shift($this->lines));
					$this->folds[] = $this->line;
				}
				while ($this->count_punches--) {
					$this->line = trim(array_shift($this->lines));
					list($x,$y) = explode(' ',$this->line);
					$this->punches[] = ['x'=>$x,'y'=>$y];
				}

				$this->folding_at_home();
				usort($this->punches,function($a,$b){
					if ($a['x'] == $b['x']) {return $a['y'] - $b['y'];}
					return $a['x'] - $b['x'];
				});

				$txt = array_map(function($n){return $n['x'].' '.$n['y'];},$this->punches);

				echo 'Case #'.(++$case).':'.PHP_EOL
					.implode(PHP_EOL,$txt).PHP_EOL;
			}
		}
		function flat_x(){
			$this->flat = [];
			$w = $this->width;
			while (--$w > -1) {$this->flat[$w] = [];}
			foreach ($this->punches as $punch) {
				$this->flat[$punch['x']][] = $punch['y'];
			}
		}
		function flat_y(){
			$this->flat = [];
			$h = $this->height;
			while (--$h > -1) {$this->flat[$h] = [];}
			foreach ($this->punches as $punch) {
				$this->flat[$punch['y']][] = $punch['x'];
			}
		}
		function expand_y(){
			$this->punches = [];
			foreach ($this->flat as $y=>$xs) {
				foreach ($xs as $x) {
					$this->punches[] = ['x'=>$x,'y'=>$y];
				}
			}
		}
		function expand_x(){
			$this->punches = [];
			foreach ($this->flat as $x=>$ys) {
				foreach ($ys as $y) {
					$this->punches[] = ['x'=>$x,'y'=>$y];
				}
			}
		}


		function folding_at_home(){
			foreach ($this->folds as $fold) {
				if ($fold == 'T') {$this->fold_at_top();}
				if ($fold == 'B') {$this->fold_at_bottom();}
				if ($fold == 'L') {$this->fold_at_left();}
				if ($fold == 'R') {$this->fold_at_right();}
			}
		}
		function fold_at_top(){
			$this->flat_y();
			$this->tmp = [];
			krsort($this->flat);
			$i = 0;
			foreach ($this->flat as $y=>$xs) {
				$this->tmp[$i++] = $xs;
				$this->tmp[$y + $this->height] = $xs;
			}
			$this->flat = $this->tmp;
			$this->expand_y();
			$this->height *= 2;
		}
		function fold_at_left(){
			$this->flat_x();
			$this->tmp = [];
			krsort($this->flat);
			$i = 0;
			foreach ($this->flat as $x=>$ys) {
				$this->tmp[$i++] = $ys;
				$this->tmp[$x + $this->width] = $ys;
			}
			$this->flat = $this->tmp;
			$this->expand_x();
			$this->width *= 2;
		}
		function fold_at_bottom(){
			$this->flat_y();
			$this->tmp = [];
			krsort($this->flat);
			$i = 0;
			foreach ($this->flat as $y=>$xs) {
				$this->tmp[$y] = $xs;
				$this->tmp[$i++ + $this->height] = $xs;
			}
			$this->flat = $this->tmp;
			$this->expand_y();
			$this->height *= 2;
		}
		function fold_at_right(){
			$this->flat_x();
			$this->tmp = [];
			krsort($this->flat);
			$i = 0;
			foreach ($this->flat as $x=>$ys) {
				$this->tmp[$x] = $ys;
				$this->tmp[$i++ + $this->width] = $ys;
			}
			$this->flat = $this->tmp;
			$this->expand_x();
			$this->width *= 2;
		}
	}

	(new _cha03())->start();
