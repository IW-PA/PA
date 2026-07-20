<?php
/*******************************************************************************
* FPDF                                                                         *
*                                                                              *
* Version: 1.86                                                                *
* Date:    2023-06-25                                                          *
* Author:  Olivier PLATHEY                                                     *
*******************************************************************************/

define('FPDF_VERSION', '1.86');

class FPDF
{
protected $page;               // current page number
protected $n;                  // current object number
protected $offsets;            // array of object offsets
protected $buffer;             // buffer holding in-memory PDF
protected $pages;              // array containing pages
protected $state;              // current document state
protected $compress;           // compression flag
protected $k;                  // scale factor (number of points in user unit)
protected $DefOrientation;     // default orientation
protected $CurOrientation;     // current orientation
protected $StdPageSizes;       // standard page sizes
protected $DefPageSize;        // default page size
protected $CurPageSize;        // current page size
protected $CurRotation;        // current page rotation
protected $PageInfo;           // page-related data
protected $wPt, $hPt;          // dimensions of current page in points
protected $w, $h;              // dimensions of current page in user units
protected $lMargin;            // left margin
protected $tMargin;            // top margin
protected $rMargin;            // right margin
protected $bMargin;            // page break margin
protected $cMargin;            // cell margin
protected $x, $y;              // current position in user units
protected $lasth;              // height of last printed cell
protected $LineWidth;          // line width in user units
protected $fontpath;           // path containing fonts
protected $CoreFonts;          // array of core font names
protected $fonts;              // array of used fonts
protected $FontFiles;          // array of font files
protected $encodings;          // array of encodings
protected $cmaps;              // array of ToUnicode CMaps
protected $FontFamily;         // current font family
protected $FontStyle;          // current font style
protected $underline;          // underlining flag
protected $CurrentFont;        // current font info
protected $FontSizePt;         // current font size in points
protected $FontSize;           // current font size in user units
protected $DrawColor;          // commands for drawing color
protected $FillColor;          // commands for filling color
protected $TextColor;          // commands for text color
protected $ColorFlag;          // whether fill and text colors are different
protected $WithAlpha;          // whether alpha channel is used
protected $AutoPageBreak;      // automatic page breaking
protected $PageBreakTrigger;   // threshold causing page break
protected $InHeader;           // flag set when processing header
protected $InFooter;           // flag set when processing footer
protected $AliasNbPages;       // alias for total number of pages
protected $ZoomMode;           // zoom display mode
protected $LayoutMode;         // layout display mode
protected $metadata;           // document properties
protected $PDFVersion;         // PDF version number

function __construct($orientation='P', $unit='mm', $size='A4')
{
    // Some checks
    $this->_dochecks();
    // Initialization of properties
    $this->state = 0;
    $this->page = 0;
    $this->n = 2;
    $this->buffer = '';
    $this->pages = array();
    $this->PageInfo = array();
    $this->fonts = array();
    $this->FontFiles = array();
    $this->encodings = array();
    $this->cmaps = array();
    $this->CoreFonts = array('courier', 'helvetica', 'times', 'symbol', 'zapfdingbats');
    $this->LineWidth = 0.567 / 2.8346456692913; // 0.2 mm in pt -> user unit
    $this->fontpath = '';
    $this->compress = true;
    // Page format
    $this->DefOrientation = $orientation;
    $this->StdPageSizes = array('a3'=>array(841.89,1190.55), 'a4'=>array(595.28,841.89), 'a5'=>array(420.94,595.28),
        'letter'=>array(612,792), 'legal'=>array(612,1008));
    $size = $this->_getpagesize($size);
    $this->DefPageSize = $size;
    $this->CurPageSize = $size;
    // Scale factor
    if($unit=='pt') $this->k = 1;
    elseif($unit=='mm') $this->k = 72/25.4;
    elseif($unit=='cm') $this->k = 72/2.54;
    elseif($unit=='in') $this->k = 72;
    else $this->Error('Incorrect unit: '.$unit);
    // Page orientation
    $orientation = strtolower($orientation);
    if($orientation=='p' || $orientation=='portrait')
    {
        $this->DefOrientation = 'P';
        $this->w = $size[0];
        $this->h = $size[1];
    }
    elseif($orientation=='l' || $orientation=='landscape')
    {
        $this->DefOrientation = 'L';
        $this->w = $size[1];
        $this->h = $size[0];
    }
    else $this->Error('Incorrect orientation: '.$orientation);
    $this->CurOrientation = $this->DefOrientation;
    $this->wPt = $this->w*$this->k;
    $this->hPt = $this->h*$this->k;
    // Page rotation
    $this->CurRotation = 0;
    // Page margins (1 cm)
    $margin = 28.35/$this->k;
    $this->SetMargins($margin,$margin);
    // Interior cell margin (1 mm)
    $this->cMargin = $margin/10;
    // Line width (0.2 mm)
    $this->LineWidth = 0.567/$this->k;
    // Automatic page break
    $this->SetAutoPageBreak(true,2*$margin);
    // Default display mode
    $this->SetDisplayMode('default');
    // Enable compression
    $this->SetCompression(true);
    // Set default PDF version number
    $this->PDFVersion = '1.3';
}

function SetMargins($left, $top, $right=null)
{
    $this->lMargin = $left;
    $this->tMargin = $top;
    if($right===null)
        $this->rMargin = $left;
    else
        $this->rMargin = $right;
}

function SetLeftMargin($margin)
{
    $this->lMargin = $margin;
    if($this->page>0 && $this->x<$margin)
        $this->x = $margin;
}

function SetTopMargin($margin)
{
    $this->tMargin = $margin;
}

function SetRightMargin($margin)
{
    $this->rMargin = $margin;
}

function SetAutoPageBreak($auto, $margin=0)
{
    $this->AutoPageBreak = $auto;
    $this->PageBreakTrigger = $this->h-$margin;
    $this->bMargin = $margin;
}

function SetDisplayMode($zoom, $layout='default')
{
    if($zoom=='fullpage' || $zoom=='fullwidth' || $zoom=='real' || $zoom=='default' || !is_string($zoom))
        $this->ZoomMode = $zoom;
    else
        $this->Error('Incorrect zoom display mode: '.$zoom);
    if($layout=='single' || $layout=='continuous' || $layout=='two' || $layout=='default')
        $this->LayoutMode = $layout;
    else
        $this->Error('Incorrect layout display mode: '.$layout);
}

function SetCompression($compress)
{
    if(function_exists('gzcompress'))
        $this->compress = $compress;
    else
        $this->compress = false;
}

function SetTitle($title, $isUTF8=false)
{
    $this->metadata['Title'] = $isUTF8 ? $title : utf8_encode($title);
}

function SetAuthor($author, $isUTF8=false)
{
    $this->metadata['Author'] = $isUTF8 ? $author : utf8_encode($author);
}

function SetSubject($subject, $isUTF8=false)
{
    $this->metadata['Subject'] = $isUTF8 ? $subject : utf8_encode($subject);
}

function SetCreator($creator, $isUTF8=false)
{
    $this->metadata['Creator'] = $isUTF8 ? $creator : utf8_encode($creator);
}

function AliasNbPages($alias='{nb}')
{
    $this->AliasNbPages = $alias;
}

function Error($msg)
{
    throw new Exception('FPDF error: '.$msg);
}

function Open()
{
    $this->state = 1;
}

function Close()
{
    if($this->state==3)
        return;
    if($this->page==0)
        $this->AddPage();
    // Page footer
    $this->InFooter = true;
    $this->Footer();
    $this->InFooter = false;
    // Close page
    $this->_endpage();
    // Close document
    $this->_enddoc();
}

function AddPage($orientation='', $size='', $rotation=0)
{
    // Start a new page
    if($this->state==0)
        $this->Open();
    $family = $this->FontFamily;
    $style = $this->FontStyle.($this->underline ? 'U' : '');
    $fontsize = $this->FontSizePt;
    $lw = $this->LineWidth;
    $dc = $this->DrawColor;
    $fc = $this->FillColor;
    $tc = $this->TextColor;
    $cf = $this->ColorFlag;
    if($this->page>0)
    {
        // Page footer
        $this->InFooter = true;
        $this->Footer();
        $this->InFooter = false;
        // Close page
        $this->_endpage();
    }
    // Start new page
    $this->_beginpage($orientation,$size,$rotation);
    // Set line cap style to square
    $this->_out('2 J');
    // Set line width
    $this->LineWidth = $lw;
    $this->_out(sprintf('%.2F w',$lw*$this->k));
    // Set font
    if($family)
        $this->SetFont($family,$style,$fontsize);
    // Set colors
    $this->DrawColor = $dc;
    if($dc!='0 G')
        $this->_out($dc);
    $this->FillColor = $fc;
    if($fc!='0 g')
        $this->_out($fc);
    $this->TextColor = $tc;
    $this->ColorFlag = $cf;
    // Page header
    $this->InHeader = true;
    $this->Header();
    $this->InHeader = false;
    // Restore line width
    if($this->LineWidth!=$lw)
    {
        $this->LineWidth = $lw;
        $this->_out(sprintf('%.2F w',$lw*$this->k));
    }
    // Restore font
    if($family)
        $this->SetFont($family,$style,$fontsize);
    // Restore colors
    if($this->DrawColor!=$dc)
    {
        $this->DrawColor = $dc;
        $this->_out($dc);
    }
    if($this->FillColor!=$fc)
    {
        $this->FillColor = $fc;
        $this->_out($fc);
    }
    $this->TextColor = $tc;
    $this->ColorFlag = $cf;
}

function Header()
{
    // To be implemented in your own inherited class
}

function Footer()
{
    // To be implemented in your own inherited class
}

function PageNo()
{
    return $this->page;
}

function SetDrawColor($r, $g=null, $b=null)
{
    if(($r==0 && $g==0 && $b==0) || $g===null)
        $this->DrawColor = sprintf('%.3F G',$r/255);
    else
        $this->DrawColor = sprintf('%.3F %.3F %.3F RG',$r/255,$g/255,$b/255);
    if($this->page>0)
        $this->_out($this->DrawColor);
}

function SetFillColor($r, $g=null, $b=null)
{
    if(($r==0 && $g==0 && $b==0) || $g===null)
        $this->FillColor = sprintf('%.3F g',$r/255);
    else
        $this->FillColor = sprintf('%.3F %.3F %.3F rg',$r/255,$g/255,$b/255);
    $this->ColorFlag = ($this->FillColor!=$this->TextColor);
    if($this->page>0)
        $this->_out($this->FillColor);
}

function SetTextColor($r, $g=null, $b=null)
{
    if(($r==0 && $g==0 && $b==0) || $g===null)
        $this->TextColor = sprintf('%.3F g',$r/255);
    else
        $this->TextColor = sprintf('%.3F %.3F %.3F rg',$r/255,$g/255,$b/255);
    $this->ColorFlag = ($this->FillColor!=$this->TextColor);
}

function SetX($x)
{
    if($x>=0)
        $this->x = $x;
    else
        $this->x = $this->w+$x;
}

function SetY($y, $resetX=true)
{
    if($y>=0)
        $this->y = $y;
    else
        $this->y = $this->h+$y;
    if($resetX)
        $this->x = $this->lMargin;
}

function SetXY($x, $y)
{
    $this->SetY($y, false);
    $this->SetX($x);
}

function GetX()
{
    return $this->x;
}

function GetY()
{
    return $this->y;
}

function GetStringWidth($s)
{
    $s = (string)$s;
    if(!isset($this->CurrentFont['cw']))
        return 0;
    $cw = &$this->CurrentFont['cw'];
    $w = 0;
    $l = strlen($s);
    for($i=0;$i<$l;$i++)
        $w += isset($cw[$s[$i]]) ? $cw[$s[$i]] : 600;
    return $w*$this->FontSize/1000;
}

function SetLineWidth($width)
{
    $this->LineWidth = $width;
    if($this->page>0)
        $this->_out(sprintf('%.2F w',$width*$this->k));
}

function Line($x1, $y1, $x2, $y2)
{
    $this->_out(sprintf('%.2F %.2F m %.2F %.2F l S',$x1*$this->k,($this->h-$y1)*$this->k,$x2*$this->k,($this->h-$y2)*$this->k));
}

function Rect($x, $y, $w, $h, $style='')
{
    if($style=='F')
        $op = 'f';
    elseif($style=='FD' || $style=='DF')
        $op = 'B';
    else
        $op = 'S';
    $this->_out(sprintf('%.2F %.2F %.2F %.2F re %s',$x*$this->k,($this->h-$y)*$this->k,$w*$this->k,-$h*$this->k,$op));
}

function SetFont($family, $style='', $size=0)
{
    if($family=='')
        $family = $this->FontFamily;
    else
        $family = strtolower($family);
    $style = strtoupper($style);
    if(strpos($style,'U')!==false)
    {
        $this->underline = true;
        $style = str_replace('U','',$style);
    }
    else
        $this->underline = false;
    if($style=='I')
        $style = 'italic';
    elseif($style=='B')
        $style = 'bold';
    elseif($style=='BI' || $style=='IB')
        $style = 'bolditalic';
    if($size==0)
        $size = $this->FontSizePt;

    if($family=='arial')
        $family = 'helvetica';
    if(in_array($family,$this->CoreFonts))
    {
        if($family=='symbol' || $family=='zapfdingbats')
            $style = '';
        $fontkey = $family.strtolower($style);
        if(!isset($this->fonts[$fontkey]))
        {
            $this->AddFont($family,$style);
        }
        $this->FontFamily = $family;
        $this->FontStyle = $style;
        $this->FontSizePt = $size;
        $this->FontSize = $size/$this->k;
        $this->CurrentFont = &$this->fonts[$fontkey];
        if($this->page>0)
            $this->_out(sprintf('BT /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
    }
    else
        $this->Error('Undefined font: '.$family.' '.$style);
}

function Ln($h=null)
{
    $this->x = $this->lMargin;
    if($h===null)
        $this->y += $this->lasth;
    else
        $this->y += $h;
}

function AddFont($family, $style='', $file='')
{
    $family = strtolower($family);
    if($family=='arial')
        $family = 'helvetica';
    $fontkey = $family.strtolower($style);
    if(isset($this->fonts[$fontkey]))
        return;

    // Define standard font metrics
    $cw = array(
        'Courier'=>array('a'=>600,'b'=>600,'c'=>600,'d'=>600,'e'=>600,'f'=>600,'g'=>600,'h'=>600,'i'=>600,'j'=>600,'k'=>600,'l'=>600,'m'=>600,'n'=>600,'o'=>600,'p'=>600,'q'=>600,'r'=>600,'s'=>600,'t'=>600,'u'=>600,'v'=>600,'w'=>600,'x'=>600,'y'=>600,'z'=>600),
        'Helvetica'=>array(' '=>278,'!'=>278,'"'=>355,'#'=>556,'$'=>556,'%'=>889,'&'=>667,'\''=>191,'('=>333,')'=>333,'*'=>389,'+'=>584,','=>278,'-'=>333,'.'=>278,'/'=>278,'0'=>556,'1'=>556,'2'=>556,'3'=>556,'4'=>556,'5'=>556,'6'=>556,'7'=>556,'8'=>556,'9'=>556,':'=>278,';'=>278,'<'=>584,'='=>584,'>'=>584,'?'=>556,'@'=>1015,'A'=>667,'B'=>667,'C'=>722,'D'=>722,'E'=>667,'F'=>611,'G'=>778,'H'=>722,'I'=>278,'J'=>500,'K'=>667,'L'=>556,'M'=>833,'N'=>722,'O'=>778,'P'=>667,'Q'=>778,'R'=>722,'S'=>667,'T'=>611,'U'=>722,'V'=>667,'W'=>944,'X'=>667,'Y'=>667,'Z'=>667,'['=>333,'\\'=>278,']'=>333,'^'=>469,'_'=>556,'`'=>333,'a'=>556,'b'=>556,'c'=>500,'d'=>556,'e'=>500,'f'=>278,'g'=>556,'h'=>556,'i'=>222,'j'=>222,'k'=>500,'l'=>222,'m'=>833,'n'=>556,'o'=>556,'p'=>556,'q'=>556,'r'=>333,'s'=>500,'t'=>278,'u'=>556,'v'=>500,'w'=>722,'x'=>500,'y'=>500,'z'=>500,'{'=>334,'|'=>260,'}'=>334,'~'=>584)
    );
    // Build default character width table
    $cw_table = array();
    for($i=0;$i<=255;$i++)
        $cw_table[chr($i)] = 600;
    if(isset($cw[ucfirst($family)]))
    {
        foreach($cw[ucfirst($family)] as $c=>$w)
            $cw_table[$c] = $w;
    }

    $i = count($this->fonts)+1;
    $name = ucfirst($family);
    if($style!='')
        $name .= '-'.$style;
    $this->fonts[$fontkey] = array('i'=>$i, 'type'=>'core', 'name'=>$name, 'cw'=>$cw_table);
}

function SetFontSize($size)
{
    if($this->FontSizePt==$size)
        return;
    $this->FontSizePt = $size;
    $this->FontSize = $size/$this->k;
    if($this->page>0)
        $this->_out(sprintf('BT /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
}

function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
{
    $txt = (string)$txt;
    $k = $this->k;
    if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
    {
        $x = $this->x;
        $ws = $this->ws;
        if($ws>0)
        {
            $this->ws = 0;
            $this->_out('0 Tw');
        }
        $this->AddPage($this->CurOrientation,$this->CurPageSize,$this->CurRotation);
        $this->x = $x;
        if($ws>0)
        {
            $this->ws = $ws;
            $this->_out(sprintf('%.3F Tw',$ws*$k));
        }
    }
    if($w==0)
        $w = $this->w-$this->rMargin-$this->x;
    $s = '';
    if($fill || $border==1)
    {
        if($fill)
            $op = ($border==1) ? 'B' : 'f';
        else
            $op = 'S';
        $s = sprintf('%.2F %.2F %.2F %.2F re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
    }
    if(is_string($border))
    {
        $x = $this->x;
        $y = $this->y;
        if(strpos($border,'L')!==false)
            $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
        if(strpos($border,'T')!==false)
            $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
        if(strpos($border,'R')!==false)
            $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
        if(strpos($border,'B')!==false)
            $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
    }
    if($txt!=='')
    {
        if($align=='R')
            $dx = $w-$this->cMargin-$this->GetStringWidth($txt);
        elseif($align=='C')
            $dx = ($w-$this->GetStringWidth($txt))/2;
        else
            $dx = $this->cMargin;
        if($this->ColorFlag)
            $s .= 'q '.$this->TextColor.' ';
        $txt2 = str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));
        $s .= sprintf('BT %.2F %.2F Td (%s) Tj ET',($this->x+$dx)*$k,($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k,$txt2);
        if($this->underline)
            $s .= ' '.$this->_dounderline($this->x+$dx,$this->y+.5*$h+.3*$this->FontSize,$txt);
        if($this->ColorFlag)
            $s .= ' Q';
    }
    if($s)
        $this->_out($s);
    $this->lasth = $h;
    if($ln>0)
    {
        $this->y += $h;
        if($ln==1)
            $this->x = $this->lMargin;
    }
    else
        $this->x += $w;
}

function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false)
{
    $cw = &$this->CurrentFont['cw'];
    if($w==0)
        $w = $this->w-$this->rMargin-$this->x;
    $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
    $s = str_replace("\r",'',$txt);
    $nb = strlen($s);
    if($nb>0 && $s[$nb-1]=="\n")
        $nb--;
    $b = 0;
    if($border)
    {
        if($border==1)
        {
            $border = 'LRTB';
            $b = 'LRT';
            $b2 = 'LRB';
        }
        else
        {
            $b2 = '';
            if(strpos($border,'L')!==false)
                $b2 .= 'L';
            if(strpos($border,'R')!==false)
                $b2 .= 'R';
            $b = strpos($border,'T')!==false ? $b2.'T' : $b2;
        }
    }
    $sep = -1;
    $i = 0;
    $j = 0;
    $l = 0;
    $ns = 0;
    $nl = 1;
    while($i<$nb)
    {
        $c = $s[$i];
        if($c=="\n")
        {
            $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
            $i++;
            $sep = -1;
            $j = $i;
            $l = 0;
            $ns = 0;
            $nl++;
            if($border && $nl==2)
                $b = $b2;
            continue;
        }
        if($c==' ')
        {
            $sep = $i;
            $ls = $l;
            $ns++;
        }
        $l += isset($cw[$c]) ? $cw[$c] : 600;
        if($l>$wmax)
        {
            if($sep==-1)
            {
                if($i==$j)
                    $i++;
                $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
            }
            else
            {
                $this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill);
                $i = $sep+1;
            }
            $sep = -1;
            $j = $i;
            $l = 0;
            $ns = 0;
            $nl++;
            if($border && $nl==2)
                $b = $b2;
        }
        else
            $i++;
    }
    if($border && strpos($border,'B')!==false)
        $b .= 'B';
    $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
    $this->x = $this->lMargin;
}

function Output($dest='', $name='', $isUTF8=false)
{
    if($this->state<3)
        $this->Close();
    $dest = strtoupper($dest);
    if($dest=='')
    {
        if($name=='')
        {
            $name = 'doc.pdf';
            $dest = 'I';
        }
        else
            $dest = 'F';
    }
    switch($dest)
    {
        case 'I':
            $this->_checkoutput();
            if(PHP_SAPI!='cli')
            {
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="'.$name.'"');
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');
            }
            echo $this->buffer;
            break;
        case 'D':
            $this->_checkoutput();
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="'.$name.'"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            echo $this->buffer;
            break;
        case 'F':
            $f = fopen($name,'wb');
            if(!$f)
                $this->Error('Unable to create output file: '.$name);
            fwrite($f,$this->buffer,strlen($this->buffer));
            fclose($f);
            break;
        case 'S':
            return $this->buffer;
        default:
            $this->Error('Incorrect output destination: '.$dest);
    }
    return '';
}

protected function _dochecks()
{

}

protected function _getpagesize($size)
{
    if(is_string($size))
    {
        $a = strtolower($size);
        if(!isset($this->StdPageSizes[$a]))
            $this->Error('Unknown page size: '.$size);
        return $this->StdPageSizes[$a];
    }
    else
        return array($size[0]*$this->k, $size[1]*$this->k);
}

protected function _beginpage($orientation, $size, $rotation)
{
    $this->page++;
    $this->pages[$this->page] = '';
    $this->state = 2;
    $this->x = $this->lMargin;
    $this->y = $this->tMargin;
    $this->FontFamily = '';
    if($orientation=='')
        $orientation = $this->DefOrientation;
    else
        $orientation = strtoupper($orientation[0]);
    if($size=='')
        $size = $this->DefPageSize;
    else
        $size = $this->_getpagesize($size);
    if($orientation!=$this->CurOrientation || $size[0]!=$this->CurPageSize[0] || $size[1]!=$this->CurPageSize[1])
    {
        if($orientation=='P')
        {
            $this->w = $size[0];
            $this->h = $size[1];
        }
        else
        {
            $this->w = $size[1];
            $this->h = $size[0];
        }
        $this->wPt = $this->w*$this->k;
        $this->hPt = $this->h*$this->k;
        $this->PageBreakTrigger = $this->h-$this->bMargin;
        $this->CurOrientation = $orientation;
        $this->CurPageSize = $size;
    }
    $this->CurRotation = $rotation;
}

protected function _endpage()
{
    $this->state = 1;
}

protected function _loadfont($font)
{
}

protected function _out($s)
{
    if($this->state==2)
        $this->pages[$this->page] .= $s."\n";
    else
        $this->buffer .= $s."\n";
}

protected function _putpages()
{
    $nb = $this->page;
    if(!empty($this->AliasNbPages))
    {
        for($n=1;$n<=$nb;$n++)
            $this->pages[$n] = str_replace($this->AliasNbPages,$nb,$this->pages[$n]);
    }
    for($n=1;$n<=$nb;$n++)
    {
        $this->_newobj();
        $this->_out('<</Type /Page');
        $this->_out('/Parent 1 0 R');
        $this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]',$this->wPt,$this->hPt));
        $this->_out('/Resources 2 0 R');
        $this->_out('/Contents '.($this->n+1).' 0 R>>');
        $this->_out('endobj');

        $p = ($this->compress) ? gzcompress($this->pages[$n]) : $this->pages[$n];
        $this->_newobj();
        $this->_out('<</Length '.strlen($p));
        if($this->compress)
            $this->_out('/Filter /FlateDecode');
        $this->_out('>>');
        $this->_putstream($p);
        $this->_out('endobj');
    }
    $this->offsets[1] = strlen($this->buffer);
    $this->_out('1 0 R');
}

protected function _putstream($s)
{
    $this->_out('stream');
    $this->_out($s);
    $this->_out('endstream');
}

protected function _putfonts()
{
    foreach($this->fonts as $k=>$font)
    {
        $this->_newobj();
        $this->fonts[$k]['n'] = $this->n;
        $name = $font['name'];
        $this->_out('<</Type /Font');
        $this->_out('/Subtype /Type1');
        $this->_out('/BaseFont /'.$name);
        $this->_out('/Encoding /WinAnsiEncoding');
        $this->_out('>>');
        $this->_out('endobj');
    }
}

protected function _putresourcecatalog()
{
    $this->_putfonts();
    // Resource dictionary
    $this->offsets[2] = strlen($this->buffer);
    $this->_out('2 0 R');
    $this->_out('<</ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');
    $this->_out('/Font <<');
    foreach($this->fonts as $font)
        $this->_out('/F'.$font['i'].' '.$font['n'].' 0 R');
    $this->_out('>>');
    $this->_out('>>');
    $this->_out('endobj');
}

protected function _putinfo()
{
    $this->_out('/Producer (FPDF '.FPDF_VERSION.')');
    if(!empty($this->metadata['Title']))
        $this->_out('/Title ('.$this->_escape($this->metadata['Title']).')');
    if(!empty($this->metadata['Subject']))
        $this->_out('/Subject ('.$this->_escape($this->metadata['Subject']).')');
    if(!empty($this->metadata['Author']))
        $this->_out('/Author ('.$this->_escape($this->metadata['Author']).')');
    if(!empty($this->metadata['Creator']))
        $this->_out('/Creator ('.$this->_escape($this->metadata['Creator']).')');
    $this->_out('/CreationDate (D:'.date('YmdHis').')');
}

protected function _putcatalog()
{
    $this->_out('/Type /Catalog');
    $this->_out('/Pages 1 0 R');
}

protected function _enddoc()
{
    $this->buffer = '%PDF-' . $this->PDFVersion . "\n" . $this->buffer;
    $this->_putinfo();
    $this->_putpages();
    $this->_putresourcecatalog();
    // Catalog
    $this->_newobj();
    $this->_out('<</Type /Catalog');
    $this->_out('/Pages 1 0 R>>');
    $this->_out('endobj');
    // Cross-ref
    $o = strlen($this->buffer);
    $this->_out('xref');
    $this->_out('0 '.($this->n+1));
    $this->_out('0000000000 65535 f ');
    for($i=1;$i<=$this->n;$i++)
        $this->_out(sprintf('%010d 00000 n ',$this->offsets[$i]));
    // Trailer
    $this->_out('trailer');
    $this->_out('<</Size '.($this->n+1));
    $this->_out('/Root '.$this->n.' 0 R');
    $this->_out('/Info '.($this->n-1).' 0 R>>');
    $this->_out('startxref');
    $this->_out($o);
    $this->_out('%%EOF');
    $this->state = 3;
}

protected function _newobj()
{
    $this->n++;
    $this->offsets[$this->n] = strlen($this->buffer);
    $this->_out($this->n.' 0 obj');
}

protected function _dounderline($x, $y, $txt)
{
    $up = $this->CurrentFont['up'];
    $ut = $this->CurrentFont['ut'];
    $w = $this->GetStringWidth($txt)+$this->ws*substr_count($txt,' ');
    return sprintf('%.2F %.2F %.2F %.2F re f',$x*$this->k,($this->h-($y-$up/1000*$this->FontSize))*$this->k,$w*$this->k,-$ut/1000*$this->FontSizePt);
}

protected function _escape($s)
{
    return str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$s)));
}

protected function _checkoutput()
{
    if(PHP_SAPI!='cli')
    {
        if(headers_sent($file,$line))
            $this->Error("Some data has already been output, can't send PDF file (output started at $file:$line)");
    }
}
}
