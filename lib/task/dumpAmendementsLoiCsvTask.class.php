<?php

class printDumpAmendementsLoiCsvTask extends sfBaseTask {
  protected function configure() {
    $this->namespace = 'dump';
    $this->name = 'AmendementsLoiCsv';
    $this->briefDescription = 'dump un csv contenant tous les amendements sur un texte de loi';
    $this->addArgument('loi_id', sfCommandArgument::REQUIRED, 'Numero de loi'); 
    $this->addArgument('format', sfCommandArgument::REQUIRED, 'Numero de loi'); 
    $this->addOption('env', null, sfCommandOption::PARAMETER_OPTIONAL, 'Changes the environment this task is run in', 'prod');
    $this->addOption('app', null, sfCommandOption::PARAMETER_OPTIONAL, 'Changes the environment this task is run in', 'frontend');
 }

  protected function execute($arguments = array(), $options = array()) {
    $this->configuration = sfProjectConfiguration::getApplicationConfiguration($options['app'], $options['env'], true);
    $manager = new sfDatabaseManager($this->configuration);
    $context = sfContext::createInstance($this->configuration);
    $this->configuration->loadHelpers(array('Url'));
    $loi = $arguments['loi_id'];
    $amendements = Doctrine::getTable('Amendement')->createQuery('a')
      ->select('a.id, a.legislature, a.texteloi_id, a.numero, CAST( a.numero AS SIGNED ) AS num, a.sujet, a.sort, a.date, a.texte, a.expose, a.signataires, a.source')
      ->from('Amendement a')
      ->where('a.sort <> ?', 'Rectifié')
      ->andWhere('a.texteloi_id = ?', $loi)
      ->orderBy('num')
      ->fetchArray();
    $champs = array();
    $res = array('amendements' => array());
    $res = array();
    foreach ($amendements as $a) {
      $parlslugs = array();
      foreach (Doctrine_Query::create()->select('p.slug')->from('Parlementaire p, ParlementaireAmendement pa')->where('p.id = pa.parlementaire_id')->andWhere('pa.amendement_id = ?', $a['id'])->orderBy('pa.numero_signataire')->fetchArray() as $s)
        $parlslugs[] = $s['slug'];
      $a['parlementaires'] = myTools::array2hash($parlslugs, 'parlementaire');
      $a['url_nosdeputes'] = url_for('@amendement?loi='.$loi.'&numero='.$a['numero'], 'absolute=true');
      unset($a['num']);
      foreach(array_keys($a) as $key)
        if (!isset($champs[$key]))
          $champs[$key] = 1;
      $res['amendements'][] = array("amendement" => $a);
    }
    $breakline = 'amendement';
    switch($arguments['format']) {
      case 'csv':
        foreach(array_keys($champs) as $key)
          echo "$key;";
        echo "\n";
        myTools::depile_csv($res, $breakline, array('parlementaire' => 1));
	break;
      case 'xml':
        myTools::depile_xml($res, $breakline);
	break;
      case 'json':
        echo json_encode($res);
	break;
      default:
        echo "Please input format csv, json or xml.";
    }
  }
  
}

