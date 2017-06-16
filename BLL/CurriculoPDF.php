<?php
/**
 * Created by PhpStorm.
 * User: rodri
 * Date: 15/06/2017
 * Time: 12:18
 */

namespace Emagine\BLL;

require_once dirname(__DIR__) . '/fpdf/fpdf.php';

use Emagine\Model\CargoInfo;
use Emagine\Model\ConhecimentoInfo;
use Emagine\Model\ProjetoInfo;
use FPDF;
use Emagine\Model\CurriculoInfo;

class CurriculoPDF extends FPDF
{
    const PRETO = "preto";
    const CINZA = "cinza";
    const AZUL = "azul";

    private $curriculo = null;

    /**
     * @return CurriculoInfo
     */
    public function getCurriculo() {
        return $this->curriculo;
    }

    /**
     * @param CurriculoInfo $value
     */
    public function setCurriculo($value) {
        $this->curriculo = $value;
    }

    public function Header() {
        /*
        $this->SetFont('Arial','B',15);
        // Move to the right
        $this->Cell(80);
        // Title
        $this->Cell(30,10,'Title',1,0,'C');
        // Line break
        $this->Ln(20);
        */
    }

    public function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial','I',8);
        // Page number
        $this->Cell(0,10,$this->PageNo(),0,0,'R');
    }

    /**
     * @param string $cor
     */
    private function definirCor($cor) {
        switch ($cor) {
            case CurriculoPDF::CINZA:
                $this->SetTextColor(120,120,120);
                break;
            case CurriculoPDF::AZUL:
                $this->SetTextColor(0,0,139);
                break;
            default:
                $this->SetTextColor(0,0,0);
                break;
        }
    }

    /**
     * @param string $text
     * @param int $size
     * @param string $cor
     * @param int $h
     * @param string $align
     * @param int|null $w
     * @param string $style
     * @param bool $ln
     */
    private function escrever($text, $size, $cor, $h, $align = "", $w = 0, $style = "", $ln = false) {
        $largura = is_null($w) ? $this->GetStringWidth($text) : $w;
        $this->definirCor($cor);
        $this->SetFont('Arial', $style, $size);
        $this->Cell($largura, $h, utf8_decode($text), 0, (($ln === true) ? 1 : 0), $align);
    }

    /**
     * @param string $text
     * @param int $size
     * @param int $h
     * @param int $w
     */
    private function escreverLabel($text, $size, $h, $w) {
        $this->escrever($text, $size,CurriculoPDF::CINZA,$h,"R", $w,"",false);
    }

    /**
     * @param string $text
     * @param int $size
     * @param string $cor
     * @param int $h
     * @param string $align
     * @param int $w
     * @param string $style
     */
    private function escreverLn($text, $size, $cor, $h, $align = "", $w = 0, $style = "") {
        $this->escrever($text, $size, $cor, $h, $align, $w, $style, true);
    }

    /**
     * @param string $text
     * @param int $size
     * @param string $cor
     * @param int $h
     * @param string $align
     * @param int $w
     * @param bool $ln
     */
    private function escreverNegrito($text, $size, $cor, $h, $align = "", $w = 0, $ln = false) {
        $this->escrever($text, $size, $cor, $h, $align, $w, "B", $ln);
    }

    /**
     * @param string $text
     * @param int $size
     * @param string $cor
     * @param int $h
     * @param string $align
     * @param int $w
     */
    private function escreverNegritoLn($text, $size, $cor, $h, $align = "", $w = 0) {
        $this->escrever($text, $size, $cor, $h, $align, $w, "B", true);
    }

    /**
     * @param string $text
     * @param int $size
     * @param int $h
     * @param string $align
     * @param int $w
     * @param bool $ln
     */
    private function escreverLink($text, $size, $h, $align = "", $w = 0, $ln = false) {
        $this->escrever($text, $size, CurriculoPDF::AZUL, $h, $align, $w, "U", $ln);
    }

    /**
     * @param string $text
     * @param int $size
     * @param int $h
     * @param string $align
     * @param int $w
     */
    private function escreverLinkLn($text, $size, $h, $align = "", $w = 0) {
        $this->escrever($text, $size, CurriculoPDF::AZUL, $h, $align, $w, "U", true);
    }

    /**
     * @param string $style
     * @param int $size
     */
    private function textoCinza($size = 9, $style = "") {
        $this->SetTextColor(120,120,120);
        $this->SetFont('Arial', $style, $size);
    }

    /**
     * @param string $style
     * @param int $size
     */
    private function textoAzul($size = 9, $style = "") {
        $this->SetTextColor(0,0,139);
        $this->SetFont('Arial', $style, $size);
    }

    /**
     * @param string $style
     * @param int $size
     */
    private function textoPreto($size = 9, $style = "") {
        $this->SetTextColor(0,0,0);
        $this->SetFont('Arial', $style, $size);
    }

    /**
     * @param ConhecimentoInfo[] $conhecimentos
     * @return string
     */
    private function consolidarConhecimento($conhecimentos) {
        $vetor = array();
        foreach ($conhecimentos as $conhecimento) {
            $vetor[] = $conhecimento->getNome();
        }
        $str = implode(", ", $vetor);
        return str_lreplace(", ", " e ", $str);
    }

    private function desenharLinha() {
        $this->SetDrawColor(120, 120, 120);
        $this->Line(10, $this->GetY() + 2, $this->GetPageWidth() - 10, $this->GetY() + 2);
        $this->SetY($this->GetY() + 5);
    }

    /**
     * @param string $texto
     */
    private function escreverTitulo($texto) {
        $this->SetTextColor(120,120,120);
        $this->SetFont('Arial', "B", 12);
        $this->Cell(0,7, utf8_decode($texto), 0, 1);
    }

    /**
     * @param CurriculoInfo $curriculo
     */
    private function gerarDados($curriculo) {
        $this->escreverNegritoLn($curriculo->getNome(),16,CurriculoPDF::PRETO,6);
        $this->escreverNegritoLn($curriculo->getCargoAtual(),12,CurriculoPDF::CINZA,6);
        $this->desenharLinha();

        $y = $this->GetY();

        //$this->escrever(_("Phone") . ":",9,CurriculoPDF::CINZA,5,"R",20, "",false);
        $this->escreverLabel(_("Phone") . ":",9,5,20);
        $this->escreverNegritoLn($curriculo->getTelefone1(),9,CurriculoPDF::PRETO,5);

        $this->escreverLabel(_("Email") . ":",9,5,20);
        $this->escreverNegritoLn($curriculo->getEmail1(),9,CurriculoPDF::PRETO,5);

        $colx = ($this->GetPageWidth() / 2) - 10;

        $this->SetXY($colx, $y);

        $this->escreverLabel(_("LinkedIn") . ":",9,5,20);
        $this->escreverLinkLn($curriculo->getLinkedinUrl(),9,5);

        $this->SetX($colx);
        $this->escreverLabel(_("GitHub") . ":",9,5,20);
        $this->escreverLinkLn($curriculo->getGithubUrl(),9,5);

        $this->SetX($colx);
        $this->escreverLabel(_("Twitter") . ":",9,5,20);
        $this->escreverLinkLn($curriculo->getTwitterUrl(),9,5);

        $this->desenharLinha();

        $this->escreverTitulo(_("Career Profile"));
        $this->SetFont('Arial','',9);
        $this->textoPreto();
        $this->MultiCell(0, 4, utf8_decode($curriculo->getResumo()));

    }

    /**
     * @param CargoInfo $cargo
     */
    private function escreverCargo($cargo) {
        $this->SetFont('Arial','B',9);
        $this->textoPreto();
        $this->Cell($this->GetStringWidth($cargo->getNome()),4, utf8_decode($cargo->getNome()));
        $this->SetFont('Arial','',9);
        $em = " " . _("at") . " ";
        $this->Cell($this->GetStringWidth($em),4, $em);
        $this->SetFont('Arial','B',9);
        $this->Cell($this->GetStringWidth($cargo->getEmpresa()),4, utf8_decode($cargo->getEmpresa()), 0, 1);

        $this->SetFont('Arial','',9);
        $this->textoCinza();
        $this->Cell(0,4, utf8_decode($cargo->getDataInicioStr() . " - " . $cargo->getDataTerminoStr()), 0, 1);
        $this->SetFont('Arial','',9);
        $this->textoPreto();

        $this->SetX($this->GetX() + 5);
        $this->SetFont('Arial','',9);
        $descricao = $cargo->getDescricao() . " " . _("Related skills") . ": " . $this->consolidarConhecimento($cargo->listarConhecimento()) . ".";
        $this->MultiCell(0, 4, utf8_decode($descricao));

        $this->SetY($this->GetY() + 2);
    }

    /**
     * @param CurriculoInfo $curriculo
     */
    private function gerarCargo($curriculo) {
        $this->desenharLinha();
        $this->escreverTitulo(_("Experiences"));
        foreach ($curriculo->listarCargo() as $cargo) {
            $this->escreverCargo($cargo);
        }
    }

    /**
     * @param ProjetoInfo $projeto
     */
    private function escreverProjeto($projeto) {
        $this->SetFont('Arial','B',9);
        $this->textoPreto();
        $this->Cell($this->GetStringWidth($projeto->getNome()),6, utf8_decode($projeto->getNome()), 0 , 1);

        $this->SetX($this->GetX() + 3);
        $this->SetFont('Arial','',9);
        $this->textoPreto();
        $descricao = $projeto->getDescricao() . " " . _("Related skills") . ": " . $this->consolidarConhecimento($projeto->listarConhecimento()) . ".";
        $this->MultiCell(0, 4, utf8_decode($descricao));

        foreach ($projeto->listarLinks() as $link) {
            $this->textoPreto();
            $this->SetFont('Arial','',8);
            $this->Cell(40,4, utf8_decode($link->getNome() . ": "), 0,0,"R");

            $this->textoAzul();
            $this->SetFont('Arial','U',8);
            $this->Cell(0,4, utf8_decode($link->getUrl()), 0, 1);
        }

        $this->SetY($this->GetY() + 2);
    }

    /**
     * @param CurriculoInfo $curriculo
     */
    private function gerarProjeto($curriculo) {
        $this->desenharLinha();
        $this->escreverTitulo(_("Projects"));
        foreach ($curriculo->listarProjeto() as $projeto) {
            $this->escreverProjeto($projeto);
        }
    }

    /**
     * @param CurriculoInfo $curriculo
     */
    private function gerarConhecimento($curriculo) {
        $this->desenharLinha();
        $this->escreverTitulo(_("Skills"));
        $this->SetFont('Arial','',10);
        $this->textoPreto();
        $this->MultiCell(0, 5, utf8_decode($this->consolidarConhecimento($curriculo->listarConhecimento())));
    }

    public function gerar() {
        $curriculo = $this->getCurriculo();
        $this->AliasNbPages();
        $this->AddPage();
        $this->gerarDados($curriculo);
        $this->gerarCargo($curriculo);
        $this->gerarProjeto($curriculo);
        $this->gerarConhecimento($curriculo);
        /*
        $this->SetFont('Arial','',12);
        for($i=1;$i<=40;$i++)
            $this->Cell(0,10,'Printing line number '.$i,0,1);
        */
    }

}