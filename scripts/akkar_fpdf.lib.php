<?php
/*
###########################################################################
#                                  AKKAR                                  #
#                      http://akkar.sourceforge.net/                      #
#                                                                         #
#                            -------------------                          #
#                            akkar_fpdf.lib.php                           #
#                            -------------------                          #
#                                                                         #
#   copyright (C) 2004 Roy W. Andersen                                    #
#   email: ensnared@gmail.com                                             #
#                                                                         #
###########################################################################

###########################################################################
#                                                                         #
#  This program is free software; you can redistribute it and/or modify   #
#  it under the terms of the GNU General Public License as published by   #
#  the Free Software Foundation; either version 2 of the License, or      #
#  (at your option) any later version.                                    #
#                                                                         #
###########################################################################
*/
if (!defined("IN_AKKAR")) {
	exit("Access violation.");
}

/*

Based on the following scripts, all licensed with FPDF's Freeware - http://www.fpdf.org/

PDF_PageGroup
Author: Larry Stanbery

PDF_Label
Author Laurent PASSEBECQ <lpasseb@numericable.fr>

FPDF Watermark
Author: Ivan

PDF_Rotate
Author: Olivier <olivier@fpdf.org>

*/

require_once('fpdf.lib.php');

class PDF extends FPDF {

	var $internal_print;

	function GetPageWidth() {
		global $pdf_left_margin, $pdf_right_margin;
		return $this->fw - $pdf_left_margin - $pdf_right_margin;
	}

	function Header() {
		global $LANG, $pdf_header_left, $pdf_header_right, $pdf_header_center, $pdf_left_margin, $pdf_dimvars;

		$this->SetFont('Arial', 'B', 10);
		$hdleft = $this->GetStringWidth($pdf_header_left);
		$hdright = $this->GetStringWidth($pdf_header_right);
		$this->SetFont('Arial', 'B', 12);
		$hdcenter = $this->GetStringWidth($pdf_header_center);

		if ($this->internal_print) {
			//Put watermark
			$this->SetFont('Arial','B',50);
			$this->SetTextColor(255,192,203);
			$this->RotatedText($pdf_dimvars['wmarkx'],$pdf_dimvars['wmarky'],$LANG['MISC']['internal_document'],45);
			$this->SetTextColor(0,0,0);
			//Done watermark
		}
		$this->SetLeftMargin(10);
		$this->SetY(10);
		$this->SetFont('Arial','B',12);
		$this->Cell(0,10,$pdf_header_center,0,0,'C');
		$this->SetFont('Arial','B',10);
		if($hdleft + $hdcenter/2 > $pdf_dimvars['sep_len']/2 || $hdright + $hdcenter/2 > $pdf_dimvars['sep_len']/2) {
			$this->Ln(5);
		}
		$this->SetX(10);
		$this->Cell(0,10,$pdf_header_left,0,0,'L');
		$this->SetFont('Arial','B',10);
		$this->SetX(10);
		$this->Cell(0,10,$pdf_header_right,0,0,'R');
		$this->Ln(7.5);
		$this->Rect($this->GetX(), $this->GetY(), $pdf_dimvars['sep_len'], 0.3);
		$this->SetY(25);
		if (!$pdf_left_margin) {
			$this->SetLeftMargin(10);
		} else {
			$this->SetLeftMargin($pdf_left_margin);
		}
	}
	function Footer() {
		global $LANG, $config, $pdf_left_margin, $pdf_dimvars;
		$this->SetLeftMargin(10);
		$this->SetRightMargin(10);
		$this->SetY(-15);
		$this->SetFont('Arial','B',8);
		$this->Cell(0,10,'AKKAR-'.$config['version'],0,0,'L');
		$this->SetX(10);
		$this->SetFont('Arial','B',10);
		$this->Cell(0,10,$LANG['MISC']['page']." ".$this->GroupPageNo().' '.$LANG['MISC']['of'].' '.$this->PageGroupAlias(),0,0,'C');
		$this->SetX(10);
		$this->Cell(0,10,$config['arrgruppenavn'],0,0,'R');
		$this->SetY(-13);
		$this->Rect($this->GetX(), $this->GetY(), $pdf_dimvars['sep_len'], 0.3);
		if (!$pdf_left_margin) {
			$this->SetLeftMargin(10);
		} else {
			$this->SetLeftMargin($pdf_left_margin);
		}
	}

	var $NewPageGroup;   // variable indicating whether a new group was requested
	var $PageGroups;     // variable containing the number of pages of the groups
	var $CurrPageGroup;  // variable containing the alias of the current page group

	// create a new page group; call this before calling AddPage()
	function StartPageGroup()
	{
		$this->NewPageGroup=true;
	}

	// current page in the group
	function GroupPageNo()
	{
		return $this->PageGroups[$this->CurrPageGroup];
	}

	// alias of the current page group -- will be replaced by the total number of pages in this group
	function PageGroupAlias()
	{
		return $this->CurrPageGroup;
	}

	function _beginpage($orientation)
	{
		parent::_beginpage($orientation);
		if($this->NewPageGroup)
		{
			// start a new group
			$n = sizeof($this->PageGroups)+1;
			$alias = "{nb$n}";
			$this->PageGroups[$alias] = 1;
			$this->CurrPageGroup = $alias;
			$this->NewPageGroup=false;
		}
		elseif($this->CurrPageGroup)
		$this->PageGroups[$this->CurrPageGroup]++;
	}

	function _putpages()
	{
		$nb = $this->page;
		if (!empty($this->PageGroups))
		{
			// do page number replacement
			foreach ($this->PageGroups as $k => $v)
			{
				for ($n = 1; $n <= $nb; $n++)
				{
					$this->pages[$n]=str_replace($k, $v, $this->pages[$n]);
				}
			}
		}
		parent::_putpages();
	}

	function RotatedText($x,$y,$txt,$angle) {
		//Text rotated around its origin
		$this->Rotate($angle,$x,$y);
		$this->Text($x,$y,$txt);
		$this->Rotate(0);
	}

	var $angle=0;

	function Rotate($angle,$x=-1,$y=-1) {
		if($x==-1)
		$x=$this->x;
		if($y==-1)
		$y=$this->y;
		if($this->angle!=0)
		$this->_out('Q');
		$this->angle=$angle;
		if($angle!=0) {
			$angle*=M_PI/180;
			$c=cos($angle);
			$s=sin($angle);
			$cx=$x*$this->k;
			$cy=($this->h-$y)*$this->k;
			$this->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
		}
	}

	function _endpage() {
		if($this->angle!=0) {
			$this->angle=0;
			$this->_out('Q');
		}
		parent::_endpage();
	}
}


class PDF_Label extends FPDF {

	// Private properties
	var $_Avery_Name    = '';                // Name of format
	var $_Margin_Left    = 0;                // Left margin of labels
	var $_Margin_Top    = 0;                // Top margin of labels
	var $_X_Space         = 0;                // Horizontal space between 2 labels
	var $_Y_Space         = 0;                // Vertical space between 2 labels
	var $_X_Number         = 0;                // Number of labels horizontally
	var $_Y_Number         = 0;                // Number of labels vertically
	var $_Width         = 0;                // Width of label
	var $_Height         = 0;                // Height of label
	var $_Char_Size        = 10;                // Character size
	var $_Line_Height    = 10;                // Default line height
	var $_Metric         = 'mm';                // Type of metric for labels.. Will help to calculate good values
	var $_Metric_Doc     = 'mm';                // Type of metric for the document
	var $_Font_Name        = 'Arial';            // Name of the font

	var $_COUNTX = 1;
	var $_COUNTY = 1;


	// Listing of labels size
	var $_Avery_Labels = array (
	'5160'	=>array('name'=>'5160',		'paper-size'=>'letter',	'metric'=>'mm',	'marginLeft'=>1.762,	'marginTop'=>10.7,		'NX'=>3,	'NY'=>10,	'SpaceX'=>3.175,	'SpaceY'=>0,	'width'=>66.675,	'height'=>25.4,		'font-size'=>8),
	'5161'	=>array('name'=>'5161',		'paper-size'=>'letter',	'metric'=>'mm',	'marginLeft'=>0.967,	'marginTop'=>10.7,		'NX'=>2,	'NY'=>10,	'SpaceX'=>3.967,	'SpaceY'=>0,	'width'=>101.6,		'height'=>25.4,		'font-size'=>8),
	'5162'	=>array('name'=>'5162',		'paper-size'=>'letter',	'metric'=>'mm',	'marginLeft'=>0.97,		'marginTop'=>20.224,	'NX'=>2,	'NY'=>7,	'SpaceX'=>4.762,	'SpaceY'=>0,	'width'=>100.807,	'height'=>35.72,	'font-size'=>8),
	'5163'	=>array('name'=>'5163',		'paper-size'=>'letter',	'metric'=>'mm',	'marginLeft'=>1.762,	'marginTop'=>10.7,		'NX'=>2,	'NY'=>5,	'SpaceX'=>3.175,	'SpaceY'=>0,	'width'=>101.6,		'height'=>50.8,		'font-size'=>8),
	'5164'	=>array('name'=>'5164',		'paper-size'=>'letter',	'metric'=>'in',	'marginLeft'=>0.148,	'marginTop'=>0.5,		'NX'=>2,	'NY'=>3,	'SpaceX'=>0.2031,	'SpaceY'=>0,	'width'=>4.0,		'height'=>3.33,		'font-size'=>12),
	'8600'	=>array('name'=>'8600',		'paper-size'=>'letter',	'metric'=>'mm',	'marginLeft'=>7.1,		'marginTop'=>19,		'NX'=>3,	'NY'=>10,	'SpaceX'=>9.5,		'SpaceY'=>3.1,	'width'=>66.6,		'height'=>25.4,		'font-size'=>8),
	'L7163'	=>array('name'=>'L7163',	'paper-size'=>'A4',		'metric'=>'mm',	'marginLeft'=>5,		'marginTop'=>15,		'NX'=>2,	'NY'=>7,	'SpaceX'=>25,		'SpaceY'=>0,	'width'=>99.1,		'height'=>38.1,		'font-size'=>9),
	'L7160'	=>array('name'=>'L7163',	'paper-size'=>'A4',		'metric'=>'mm',	'marginLeft'=>7.2,		'marginTop'=>15.9,		'NX'=>3,	'NY'=>7,	'SpaceX'=>0.25,		'SpaceY'=>0,	'width'=>63.5,		'height'=>38.1,		'font-size'=>9),
	'C2160'	=>array('name'=>'C2160',	'paper-size'=>'A4',		'metric'=>'mm',	'marginLeft'=>7.2,		'marginTop'=>7.2,		'NX'=>3,	'NY'=>7,	'SpaceX'=>0.25,		'SpaceY'=>0,	'width'=>63.5,		'height'=>38.1,		'font-size'=>9)
	);

	// convert units (in to mm, mm to in)
	// $src and $dest must be 'in' or 'mm'
	function _Convert_Metric ($value, $src, $dest) {
		if ($src != $dest) {
			$tab['in'] = 39.37008;
			$tab['mm'] = 1000;
			return $value * $tab[$dest] / $tab[$src];
		} else {
			return $value;
		}
	}

	// Give the height for a char size given.
	function _Get_Height_Chars($pt) {
		// Array matching character sizes and line heights
		$_Table_Hauteur_Chars = array(6=>2, 7=>2.5, 8=>3, 9=>4, 10=>5, 11=>6, 12=>7, 13=>8, 14=>9, 15=>10);
		if (in_array($pt, array_keys($_Table_Hauteur_Chars))) {
			return $_Table_Hauteur_Chars[$pt];
		} else {
			return 100; // There is a prob..
		}
	}

	function _Set_Format($format) {
		$this->_Metric         = $format['metric'];
		$this->_Avery_Name     = $format['name'];
		$this->_Margin_Left    = $this->_Convert_Metric ($format['marginLeft'], $this->_Metric, $this->_Metric_Doc);
		$this->_Margin_Top    = $this->_Convert_Metric ($format['marginTop'], $this->_Metric, $this->_Metric_Doc);
		$this->_X_Space     = $this->_Convert_Metric ($format['SpaceX'], $this->_Metric, $this->_Metric_Doc);
		$this->_Y_Space     = $this->_Convert_Metric ($format['SpaceY'], $this->_Metric, $this->_Metric_Doc);
		$this->_X_Number     = $format['NX'];
		$this->_Y_Number     = $format['NY'];
		$this->_Width         = $this->_Convert_Metric ($format['width'], $this->_Metric, $this->_Metric_Doc);
		$this->_Height         = $this->_Convert_Metric ($format['height'], $this->_Metric, $this->_Metric_Doc);
		$this->Set_Font_Size($format['font-size']);
	}

	// Constructor
	function PDF_Label ($format, $unit='mm', $posX=1, $posY=1) {
		if (is_array($format)) {
			// Custom format
			$Tformat = $format;
		} else {
			// Avery format
			$Tformat = $this->_Avery_Labels[$format];
		}

		parent::FPDF('P', $Tformat['metric'], $Tformat['paper-size']);
		$this->_Set_Format($Tformat);
		$this->Set_Font_Name('Arial');
		$this->SetMargins(0,0);
		$this->SetAutoPageBreak(false);

		$this->_Metric_Doc = $unit;
		// Start at the given label position
		if ($posX > 1) $posX--; else $posX=0;
		if ($posY > 1) $posY--; else $posY=0;
		if ($posX >=  $this->_X_Number) $posX =  $this->_X_Number-1;
		if ($posY >=  $this->_Y_Number) $posY =  $this->_Y_Number-1;
		$this->_COUNTX = $posX;
		$this->_COUNTY = $posY;
	}

	// Sets the character size
	// This changes the line height too
	function Set_Font_Size($pt) {
		if ($pt > 3) {
			$this->_Char_Size = $pt;
			$this->_Line_Height = $this->_Get_Height_Chars($pt);
			$this->SetFontSize($this->_Char_Size);
		}
	}

	// Method to change font name
	function Set_Font_Name($fontname) {
		if ($fontname != '') {
			$this->_Font_Name = $fontname;
			$this->SetFont($this->_Font_Name);
		}
	}

	// Print a label
	function Add_PDF_Label($texte) {
		// We are in a new page, then we must add a page
		if (($this->_COUNTX ==0) and ($this->_COUNTY==0)) {
			$this->AddPage();
		}

		$_PosX = $this->_Margin_Left+($this->_COUNTX*($this->_Width+$this->_X_Space));
		$_PosY = $this->_Margin_Top+($this->_COUNTY*($this->_Height+$this->_Y_Space));
		$this->SetXY($_PosX+3, $_PosY+3);
		$this->MultiCell($this->_Width, $this->_Line_Height, $texte);
		$this->_COUNTY++;

		if ($this->_COUNTY == $this->_Y_Number) {
			// End of column reached, we start a new one
			$this->_COUNTX++;
			$this->_COUNTY=0;
		}

		if ($this->_COUNTX == $this->_X_Number) {
			// Page full, we start a new one
			$this->_COUNTX=0;
			$this->_COUNTY=0;
		}
	}

}

?>
