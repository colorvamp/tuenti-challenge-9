#!/usr/bin/php
<?php
	class _cha02{
		public $_map = [];
		function start(){
			$this->lines = file('php://stdin');
			$this->cases = trim(array_shift($this->lines));

			$case = 0;
			while ($this->cases--) {
				$this->nodes = trim(array_shift($this->lines));
				$this->_map = [];
				while ($this->nodes--) {
					$this->line = trim(array_shift($this->lines));
					list($source,$dests) = explode(':',$this->line);
					$dests = explode(',',$dests);
					$this->_map[$source] = $dests;
				}
				$total = $this->explore();
				echo 'Case #'.(++$case).': '.$total.PHP_EOL;
			}
		}
		function explore(){
			$this->_dwarfs = new _dwarfs();
			$this->_dwarfs->matrix = $this->_map;
			$this->_dwarfs->start();
			return $this->_dwarfs->found;
		}
	}

	(new _cha02())->start();


	class _dwarfs{
		public $matrix = [];
		public $found  = 0;
		public $start  = 'Galactica';
		public $target = 'New Earth';
		function start(){
			foreach ($this->matrix[$this->start] as $step) {
				$this->move($step);
			}
		}
		function move($dest,$path = []){
			if (isset($path[$dest])) {return false;}

			$path[$dest] = true;
			if ($dest == $this->target) {
				$this->found++;
				return true;
			}

			foreach ($this->matrix[$dest] as $step) {
				$this->move($step,$path);
			}
		}
	}
