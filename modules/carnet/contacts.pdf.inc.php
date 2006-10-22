<?php
/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************/

define ('FPDF_FONTPATH', dirname(__FILE__).'/fonts/');
require_once '/usr/share/fpdf/fpdf.php';
require_once dirname(__FILE__).'/../../classes/varstream.php';

class ContactsPDF extends FPDF
{
    var $col = 0;
    var $y0;

    var $title  = "Mes contacts sur Polytechnique.org";
    var $broken = false;
    var $error  = false;

    var $report = 0;

    function ContactsPDF()
    {
        $this->report = error_reporting(0);
        parent::FPDF();
        error_reporting($this->report);

        $this->AddFont('Vera Sans', '',  'Vera.php');
        $this->AddFont('Vera Sans', 'I', 'VeraIt.php');
        $this->AddFont('Vera Sans', 'B', 'VeraBd.php');

        $this->AddFont('Vera Mono', '',  'VeraMono.php');

        $this->SetTitle($this->title);
        $this->SetCreator('Site Polytechnique.org');
        $this->AddPage();
    }

    function Output($name='mescontacts.pdf', $dest='I')
    {
        Header('Pragma: public');
        error_reporting(0);
        parent::Output($name, $dest);
        error_reporting($this->report);
    }

    function Rotate($angle, $x=-1, $y=-1)
    {
        if ($x==-1) {
            $x = $this->x;
        }
        if ($y==-1) {
            $y=$this->y;
        }
        if (!empty($this->angle)) {
            $this->_out('Q');
        }
        $this->angle = $angle;
        if ($angle != 0) {
            $angle*=M_PI/180;
            $c  = cos($angle);
            $s  = sin($angle);
            $cx = $x*$this->k;
            $cy = ($this->h-$y)*$this->k;
            $this->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm',
                                $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }

    function Header()
    {

        $this->SetFont('Vera Sans', 'B', 20);
        $this->SetTextColor(230);
        $this->Rotate(45, 55, 190);
        $this->Text(55, 190, "informations limitées à un usage");
        $this->Text(40, 210, "strictement personnel et non commercial");
        $this->Rotate(0);

        $this->setLeftMargin(5);
        $this->setRightMargin(5);
        $this->SetFont('Vera Sans', 'B', 16);
        $this->SetY(5);
        $this->SetTextColor(51, 102, 153);
        $this->SetDrawColor(102, 153, 204);
        $this->SetLineWidth(0.2);
        $this->SetFillColor(245, 248, 252);
        $this->Cell(200, 10, $this->title, 1, 1, 'C', 1);
        $this->Image(dirname(__FILE__).'/../../htdocs/images/logo.png',
                     5, 5, 10, 10, 'png', 'https://www.polytechnique.org/');

        $this->Ln(10);
        $this->y0 = $this->GetY();
        $this->ColSetup(false);
    }

    function Footer()
    {
        $this->setLeftMargin(5);
        $this->setRightMargin(5);
        $this->SetY(-15);
        $this->SetFont('Vera Sans', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, 'Page '.$this->PageNo(), 0, 0, 'C');
        $this->Cell(0, 10, '(en date du '.strftime('%d %B %Y').')', 0, 0, 'R');
    }

    function ColSetup($col)
    {
        $this->col = $col;
        $x = 10 + $this->col * 100;
        $this->SetLeftMargin($x);
        $this->SetRightMargin(120 - $x);
        $this->SetX($x);
        $this->SetY($this->y0);
    }

    function NextCol()
    {
        $this->ColSetup(1 - $this->col);
        if ($this->col == 0) {
            $this->AddPage();
        }
    }

    function AcceptPageBreak()
    {
        $this->broken = true;
    }

    function Space($w=0.1, $h=0.5)
    {
        $x = $this->getX();
        $y = $this->getY();
        $this->SetLineWidth($w);
        $this->Line($x, $y, $x+90, $y);
        $this->Ln($h);
    }

    function TableRow($l, $r, $font = 'Sans')
    {
        $this->SetFont('Vera Sans', 'B', 8);
        $y = $this->getY();
        $x = $this->getX();
        $this->MultiCell(25, 4, $l, '', 1);
        $y1 = $this->getY();

        $this->SetFont('Vera '.$font, '', 8);
        $this->setY($y);
        $first = 1;

        $this->setX($x+25);
        $this->MultiCell(65, 4, $r, '', 1);

        $this->setY(max($y1, $this->getY())+0.5);
        $this->setX($x);
    }

    function Address($a)
    {
        $l = "adresse\n";
        if ($a['active']) {
            $l .= 'actuelle';
        } elseif ($a['secondaire']) {
            $l .= 'secondaire';
        } else {
            $l .= 'principale';
        }

        $r = '';
        $r = trim("$r\n".$a['adr1']);
        $r = trim("$r\n".$a['adr2']);
        $r = trim("$r\n".$a['adr3']);
        $r = trim("$r\n".trim($a['postcode'].' '.$a['city']));

        $this->TableRow($l, $r);

        if (!empty($a['tels'])) {
            foreach ($a['tels'] as $tel) {
                if (!empty($tel['tel'])) {
                    $this->TableRow($tel['tel_type'], $tel['tel'], 'Mono');
                }
            }
        }
    }

    function AddressPro($a)
    {
        if ($a['entreprise']) {
            $this->TableRow('Entreprise', $a['entreprise']);
        }

        if ($a['adr1'] || $a['adr2'] || $a['adr3'] || $a['postcode'] || $a['city']) {
            $r = '';
            $r = trim("$r\n".$a['adr1']);
            $r = trim("$r\n".$a['adr2']);
            $r = trim("$r\n".$a['adr3']);
            $r = trim("$r\n".trim($a['postcode'].' '.$a['city']));
            $this->TableRow('adresse pro', $r);
        }

        if ($a['tel']) {
            $this->TableRow('Téléphone', $a['tel'], 'Mono');
        }
        if ($a['fax']) {
            $this->TableRow('Fax', $a['fax'], 'Mono');
        }
    }

    function Error($msg)
    {
        $this->error = true;
    }

    function wordwrap($text, $maxwidth = 90) {
        $text = trim($text);
        if ($text==='') { return 0; }
        $space = $this->GetStringWidth(' ');
        $lines = explode("\n", $text);
        $text = '';
        $count = 0;

        foreach ($lines as $line) {
            $words = preg_split('/ +/', $line);
            $width = 0;

            foreach ($words as $word) {
                $wordwidth = $this->GetStringWidth($word);
                if ($width + $wordwidth <= $maxwidth) {
                    $width += $wordwidth + $space;
                    $text .= $word.' ';
                } else {
                    $width = $wordwidth + $space;
                    $text  = rtrim($text)."\n".$word.' ';
                    $count++;
                }
            }
            $text = rtrim($text)."\n";
            $count++;
        }
        $text = rtrim($text);
        return $count;
    }

    static function AddContact($self, $x, $wp = true)
    {
        /* infamous hack :
           1- we store the current state.
           2- at the end, we find out if we triggered the page break,
              -> no ? ok
              -> yes ? then we have to create a col, and add the contact again.
        */
        $old = clone $self;

        $self->SetFont('Vera Sans', '', 10);
        $self->SetDrawColor(0);
        $self->SetFillColor(245, 248, 252);
        $self->SetLineWidth(0.4);

        $nom = $x['prenom'].' '
              .($x['nom_usage'] ? "{$x['nom_usage']} ({$x['nom']})" : $x['nom'])
              ." ({$x['promo']})";
        $ok  = false;

        if ($wp) {
            $res = XDB::query("SELECT * FROM photo WHERE attachmime IN ('jpeg', 'png') AND uid={?}",
                              $x['user_id']);
            if ($i = $res->numRows()) {
                $old2  = clone $self;
                $photo = $res->fetchOneAssoc();
                $width = $photo['x'] * 20/$photo['y'];
                $GLOBALS["p{$x['user_id']}"] = $photo['attach'];

                $_x = $self->getX();
                $_y = $self->getY();
                $self->Cell(0, 20, '', '', 0, '', 1);
                error_reporting(0);
                $self->Image("var://p{$x['user_id']}", $_x, $_y, $width, 20, $photo['attachmime']);
                error_reporting($self->report);

                if ($self->error) {
                    $self = clone $old2;
                } else {
                    $self->setX($_x);
                    $self->Cell($width, 20, '', "T");
                    $h = 20 / $self->wordwrap($nom, 90-$width);
                    $self->MultiCell(0, $h, $nom, 'T', 'C');
                    $ok = true;
                }
            }
        }
        if (!$ok) {
            $self->MultiCell(0, 6, $nom, "T", 'C', 1);
        }

        if ($x['mobile']) {
            $self->Space();
            $self->TableRow('mobile', $x['mobile'], 'Mono');
        }

        foreach ($x['adr'] as $a) {
            $self->Space();
            $self->Address($a);
        }

        if (!empty($x['adr_pro'])) {
            foreach ($x['adr_pro'] as $a) {
                if ( ! ($a['entreprise'] || $a['tel'] || $a['fax']
                        || $a['adr1'] || $a['adr2'] || $a['adr3'] || $a['postcode'] || $a['city']) )
                {
                    continue;
                }
                $self->Space();
                $self->AddressPro($a);
            }
        }

        $self->Space(0.4, 5);

        if ($self->broken) {
            $old->NextCol();
            $self = ContactsPDF::AddContact($old, $x, $wp);
        }

        return $self;
    }

}

?>
