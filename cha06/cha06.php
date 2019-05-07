#!/usr/bin/php
<?php
	class _cha06{
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

				//if ($this->case > 24) {continue;}
				//if ($this->case != 8) {continue;}
				$res = $this->resolve();
				echo 'Case #'.($this->case).': '.trim($res).PHP_EOL;
			}
		}
		function resolve(){
			if (count($this->data) == 1 && strlen($this->data[0]) == 1) {return $this->data[0];}
			if (count($this->data) == 1 && strlen($this->data[0]) > 1) {return 'AMBIGUOUS';}
			$this->orig = $this->data;
			//print_r($this->data);

			$this->all_letters = [];
			foreach ($this->data as $row) {
				$letters = str_split($row);
				$this->all_letters = array_merge($this->all_letters,$letters);
				$this->all_letters = array_values(array_unique($this->all_letters));
			}

			$this->rels = [];
			$this->data = array_reverse($this->data);

			$loneletters = [];
			foreach ($this->data as $word) {
				if (strlen($word) == 1) {
					$loneletters[] = $word;
				}
			}

			foreach ($loneletters as $k=>$letter) {
				if (!isset($loneletters[$k + 1])) {continue;}
				$this->rels[$loneletters[$k + 1]]['lt'][] = $letter;
			}

			$pool = array_shift($this->data);
			$pool = str_split($pool);
			while (($comp = array_shift($this->data))) {
				$comp = str_split($comp);

				$this->comparewords($pool /*anterior*/,$comp);
				$pool = $comp;
			}

			if (count($this->rels) < count($this->all_letters) - 1) {
				return 'AMBIGUOUS';
			}

			$this->order = [];
			$this->rels = array_reverse($this->rels);
			$tmp = [];
			foreach ($this->rels as $letter=>$rel) {
				$tmp[$letter] = [
					 'letter'=>$letter
					,'lt'=>array_values(array_unique($rel['lt']))
				];
			}
			$this->rels = $tmp;

			if (count($this->rels) > 0) {
				foreach ($this->rels as $letter=>$rel) {
					$r = $this->rel($letter);
					if ($r == 'AMBIGUOUS') {return $r;}
				}
				//print_r($this->orig);
				//print_r($this->rels);
				//exit;

				$this->inv = array_reverse($this->order);
				foreach ($this->inv as $k=>$letter) {
					if (!isset($this->inv[$k + 1])) {continue;}
					$next = $this->inv[$k + 1];
					if (!in_array($letter,$this->rels[$next]['lt'])) {
						return 'AMBIGUOUS';
						var_dump($letter);
						print_r($this->rels[$next]);
						exit;
					}
				}

				$this->order = array_unique($this->order);
				//$this->validate();
				return implode(' ',$this->order);
			}

			$this->result = '';
			foreach ($this->rels as $letter=>$rel) {
				$this->result .= $rel['lt'][0].' '.$letter.' ';
			}
			return $this->result;
		}
		function validate(){
			$str = '$func = function(';
			foreach ($this->order as $letter) {
				$str .= '$'.$letter.',';
			}
			$str = substr($str,0,-1).'){'.PHP_EOL;
			$count = 0;
			foreach ($this->orig as $word) {
				$word = str_split($word);
				$str .= '$count'.$count++.' = base_convert(str_pad(';
				foreach ($word as $letter) {
					$str .= '$'.$letter.'.';
				}
				$str = substr($str,0,-1);
				$str .= ','.count($this->order).',"0"),36,10);'.PHP_EOL;
			}

			$str .= 'return (';
			for ($i = 1;$i < $count;$i ++) {
				$str .= '($count'.($i-1).' < $count'.$i.') && ';
			}
			$str = substr($str,0,-4).');};';
			eval($str);

			$keys = array_keys($this->order);
			foreach ($keys as $k=>&$val) {
				$val = base_convert(($val + 1),10,36);
			}
			unset($val);

			$done = $func(...$keys);
			var_dump($done);
			exit;
		}
		function rel($letter = ''){
			if (!isset($this->rels[$letter])) {
				if (empty($this->order)) {
					$this->order[] = $letter;
					return true;
				}
			}

			$rel = $this->rels[$letter];

			$poss = [];
			foreach ($rel['lt'] as $l) {
				$tmp = array_search($l,$this->order);
				if ($tmp === false) {
					$this->rel($l);
					$tmp = array_search($l,$this->order);
				}
				$poss[] = $tmp;
			}

			$poss = min($poss);

			$posl = array_search($letter,$this->order);
			if ($posl > $poss) {
				//return 'AMBIGUOUS';
				echo 'problema';
				exit;
			}
			if ($posl === false && $poss !== false) {
				array_splice($this->order,$poss,0,$letter);
			}
		}
		function comparewords($wprev = [] /*anterior*/,$wcurr = []){
			$cprev = count($wprev);
			$ccurr = count($wcurr);

			foreach ($wprev as $k=>$letter) {
				if (!isset($wcurr[$k])) {break;}
				if ($wprev[$k] == $wcurr[$k]) {continue;}

				$label = $wcurr[$k].' < '.$wprev[$k];
				if (false && $label == 'd < u') {
					var_dump($k);
					print_r($wprev);
					print_r($wcurr);
					exit;
				}
				//echo $label.PHP_EOL;

				$this->rels[$wcurr[$k]]['lt'][] = $wprev[$k];
				break;
			}
		}
	}

	(new _cha06())->start();
