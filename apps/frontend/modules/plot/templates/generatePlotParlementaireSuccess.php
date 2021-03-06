<?php

$data = get_component('plot', 'getParlData', array('parlementaire' => $parlementaire, 'session' => $time));

if ($format == 'json') {
  print_r($data);
  exit;
}
$data = json_decode($data, true);

$n = count($data['labels']);
$presences = array_fill(1, $n, 0);
$participations = array_fill(1, $n, 0);
$presences_medi = array_fill(1, $n, 0);
if ($type === 'total') for ($i = 1; $i <= $n; $i++) {
  $presences[$i] = $data['n_presences']['hemicycle'][$i] + $data['n_presences']['commission'][$i];
  $participations[$i] = $data['n_participations']['hemicycle'][$i] + $data['n_participations']['commission'][$i];
} else if ($type === 'hemicycle' || $type === 'commission') {
  $presences = $data['n_presences']["$type"];
  $participations = $data['n_participations']["$type"];
}
$presences_medi = $data['presences_medi']["$type"];

if ($link === 'true') {
  $font = 9;
  $ticks = 2;
  $scale = 14;
  $size = 150;
} else {
  $font = 12;
  $ticks = 1;
  if ($type === 'total') {
    $scale = 14;
    $size = 300;
  } else if ($type === 'commission') {
    $scale = 7;
    $size = 185;  // 70+$scale/$scaletotal*($sizetotal-70)
  } else if ($type === 'hemicycle') {
    $scale = 12;
    $size = 267;
  }
}

$DataSet = new xsPData();
$DataSet->AddPoint($data['labels'], "Serie1");
$DataSet->AddPoint($presences, "Serie2");
$DataSet->AddPoint($participations, "Serie3");
$DataSet->AddSerie("Serie2");
$DataSet->AddSerie("Serie3");
$DataSet->SetAbsciseLabelSerie("Serie1");
$DataSet->SetYAxisName("Séances par semaine");

$DataSetLegend = new xsPData();
$DataSetLegend->AddPoint($data['labels'], "Serie1");
$DataSetLegend->AddPoint($presences, "Serie2");
$DataSetLegend->AddPoint($participations, "Serie3");
$DataSetLegend->AddSerie("Serie2");
$DataSetLegend->AddSerie("Serie3");
$DataSetLegend->SetSerieName(utf8_decode('Présences'),"Serie2");
$DataSetLegend->SetSerieName("Participations","Serie3");
$DataSetLegend->SetYAxisUnit(utf8_decode(" séances cette semaine"));
$DataSetLegend->SetAbsciseLabelSerie("Serie1");

$DataSetBordure = new xsPData();
$DataSetBordure->AddPoint($data['labels'], "Serie1");
$DataSetBordure->AddPoint($presences, "Serie2");
$DataSetBordure->AddPoint(array_fill(1, count($data['labels']), 0), "Serie5");
$DataSetBordure->AddSerie("Serie2");
$DataSetBordure->AddSerie("Serie5");
$DataSetBordure->SetAbsciseLabelSerie("Serie1");

$DataSet2 = new xsPData();
$DataSet2->AddPoint($data['labels'], "Serie1");
$DataSet2->AddPoint($data['vacances'], "Serie6");
$DataSet2->AddSerie("Serie6");
$DataSet2->SetAbsciseLabelSerie("Serie1");

if (!$data['fin'] && $questions === 'true' && $type != 'commission') {
  $DataSet3 = new xsPData();
  $DataSet3->AddPoint($data['labels'], "Serie1");
  $DataSet3->AddPoint($data['n_questions'], "Serie7");
  $DataSet3->AddSerie("Serie7");
  $DataSet3->SetAbsciseLabelSerie("Serie1");
  $Data3 = $DataSet3->GetData();
  $DataDescr3 = $DataSet3->GetDataDescription();
}

$DataSet4 = new xsPData();
$DataSet4->AddPoint($data['labels'], "Serie1");
$DataSet4->AddPoint($presences_medi, "Serie8");
$DataSet4->AddPoint(array_fill(1, count($data['labels']), 0), "Serie5");
$DataSet4->AddSerie("Serie8");
$DataSet4->AddSerie("Serie5");
$DataSet4->SetAbsciseLabelSerie("Serie1");
$Data4 = $DataSet4->GetData();
$DataDescr4 = $DataSet4->GetDataDescription();

$Data = $DataSet->GetData();
$DataDescr = $DataSet->GetDataDescription();
$DataBordure = $DataSetBordure->GetData();
$DataDescrBordure = $DataSetBordure->GetDataDescription();
$Data2 = $DataSet2->GetData();
$DataDescr2 = $DataSet2->GetDataDescription();
$DataLegend = $DataSetLegend->GetData();
$DataDescrLegend = $DataSetLegend->GetDataDescription();

$Test = new xsPChart(790,$size);
$Test->setGraphArea(25+3*$font,3*$font,780,$size-10-2*$font);
$Test->drawFilledRoundedRectangle(7,3,785,$size-7,5,240,240,240);
$Test->drawRoundedRectangle(5,1,787,$size - 5,5,230,230,230);
$Test->drawGraphArea(230,230,230,FALSE);
$Test->setFixedScale(0,$scale,$scale/$ticks);
$Test->xsSetFontProperties("tahoma.ttf",$font);
$ticks = TRUE;
if ($data['fin']) $ticks = FALSE;
$Test->drawScale($Data,$DataDescr,SCALE_NORMAL,50,50,50,$ticks,0,0,FALSE,1,FALSE);
if ($link === 'true') {
  $Test->setColorPalette(0,255,255,255);
  $Test->setColorPalette(1,255,255,255);
  $Test->drawOverlayBarGraph($DataLegend,$DataDescrLegend,30,100);
}
$Test->drawGrid(0,TRUE,0,0,0,100);

// Vacances
$Test->setColorPalette(0,50,50,50);
$Test->drawOverlayBarGraph($Data2,$DataDescr2,30,100);

// Médiane
$Test->setColorPalette(0,160,160,160);
if ($histogram)
  $Test->drawOverlayBarGraph($Data4,$DataDescr4,5,95);
else $Test->drawLineGraph($Data4,$DataDescr4);

// Présences et participations
$Test->setColorPalette(0,255,0,0);
$Test->setColorPalette(1,255,255,0);
$Test->setColorPalette(2,0,255,0);
if ($histogram)
  $Test->drawOverlayBarGraph($Data,$DataDescr,78,95);
else $Test->drawFilledLineGraph($Data,$DataDescr,78);

// Questions
if (!$data["fin"] && $questions === 'true' && $type != 'commission') {
  $Test->setColorPalette(0,100,100,255);
  $Test->drawOverlayBarGraph($Data3,$DataDescr3,100,25);
}

// Bordure présences sur participation
$Test->setColorPalette(0,255,0,0);
$Test->setColorPalette(1,0,0,0);
if (!$histogram)
  $Test->drawLineGraph($DataBordure,$DataDescrBordure);

$Test->xsSetFontProperties("tahoma.ttf",$font + 2);
$pos_titre = 240;
if (preg_match("/^l/", $time)) {
  if (isset($data['mandat_clos'])) {
    $pos_titre = 210;
    $duree = 'de sa dernière année de mandat';
  } else if ($data['fin']) {
    $pos_titre = 235;
    $duree = 'de toute la législature';
  } else {
    $mois = min(12, $parlementaire->getNbMois(array(), true));
    $duree = ($mois < 2 ? "du premier" : "des $mois ".($mois < 12 ? "prem" : "dern")."iers")." mois";
  }
  $shortduree = 'annee';
} else {
  $duree = "de la session ".preg_replace('/^(\d{4})/', '\\1-', $time);
  $shortduree = $time;
}
if ($type === 'total') {
  $Test->drawTitle($pos_titre,3 + 2*$font,"Présences en commissions et participation en hémicycle au cours ".$duree,50,50,50,585);
} else {
  if ($type === 'commission') {
    $titre = "Présences et participation en commissions";
  } else {
    $titre = "Participation en hémicycle";
  }
  $Test->drawTitle($pos_titre+30,3 + 2*$font, $titre." au cours ".$duree,50,50,50,585);
}

$Test->xsStroke();

?>
