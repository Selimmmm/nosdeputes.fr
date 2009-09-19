<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class PersonnaliteTable extends Doctrine_Table
{
  protected $changed = 0;
  protected $all = null;
  public function similarTo($str, $sexe = null, $return_array = 0)
  {
    if (preg_match('/^\s*$/', $str))
      return null;
    $str = preg_replace('/\(.*\)/', '', $str);
    $word = preg_replace('/^.*\s(\S+)\s*$/i', '\\1', $str);
    $q = $this->createQuery('p')->where('nom LIKE ?', '% '.$word.'%');
    $res = $q->Execute();
    if ($res->count() == 1) {
      if ($return_array)
	return array($res[0]);
      return $res[0];
    }
    $q->free();
    $res->free();

    //load parlementaires only once
    if (!$this->all) {
      $this->all = $this->createQuery('p')
	->select('id, nom, nom_de_famille, sexe, slug')
	->fetchArray();
      $this->changed = 0;
    }

    $closest = null;
    $closest_res = -1;
    $best_res = -1;
    $champ = 'nom';
    if (!preg_match('/ /', $str))
      $champ = 'nom_de_famille';
    $similar = array();
    //Compare each parlementaire with the string and keep the best
    for ($i = 0 ; $i < count($this->all) ; $i++) {
      $parl = $this->all[$i];
      if ($sexe && $sexe != $parl['sexe'])
        continue;
      $res = similar_text(preg_replace('/[^a-z]+/i', ' ', $parl[$champ]), preg_replace('/[^a-z]+/i', ' ', $str), $pc);
      if ($res > 0 && $pc > 65)
	$similar[$i] = $pc;
    }

    arsort($similar);
    $keys = array_keys($similar);
    if (count($keys)) {
      $closest_res = $similar[$keys[0]];
      $closest = $this->all[$keys[0]];
    }

    if ($return_array) {
      $res = array();
      foreach(array_keys($similar) as $i) {
	array_push($res, $this->all[$i]);
      }
      return $res;
    }

#    echo "$str "; echo $closest['nom'];    echo " $closest_res\n";
    if (strlen($str) < 8) $seuil = 65;
    else $seuil = 85;
    //If more than 85% similarities, it is the best
    if ($closest_res > $seuil)
      return $this->find($closest['id']);
    //If str is the end of the best parlementaire, it is OK (remove non alpha car to avoid preg pb)
    if (preg_match('/'.preg_replace('/[^a-z]/i', '', $str).'$/', preg_replace('/[^a-z]/i', '', $closest['nom'])))
      return $this->find($closest['id']);


    return null;
  }

  public function hasChanged() {
    $this->changed = 1;
  }

}