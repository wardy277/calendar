<?php

/**
 * Created by PhpStorm.
 * User: ico3
 * Date: 29/05/15
 * Time: 15:18
 */
class Table extends Entity{

	private $rows;

	public function addRow($cells){
		$this->rows[] = $cells;
	}

	public function buildTable(){
		$html = '<table class="'.$this->getClass().'">';

		foreach($this->rows as $cells){
			$html .= "<tr>";

			foreach($cells as $cell){
				if($cell instanceof Url){
					$html .= "<td>".$cell->buildAnchor()."</td>";
				}
				else{
					$html .= "<td>$cell</td>";
				}
			}

			$html .= "</tr>";
		}
		$html .= "</table>";

		return $html;
	}
}