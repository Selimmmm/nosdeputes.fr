<h1>Recherche de députés</h1>
<?php $sf_response->setTitle('Recherche de députes "'.$search.'"'); ?>
<div class="liste_deputes">
<?php if ($search != "") : ?>
<p><?php $nResults = $pager->getNbResults(); echo $nResults; ?> résultat<?php if ($nResults > 1) echo 's'; ?> pour <em>"<?php echo $search; ?>"</em></p>
<?php else : ?>
<p>Les <?php $nResults = $pager->getNbResults(); echo $nResults; ?> députés de la législature (577 en activité)&nbsp;:</p>
<?php endif; ?>
<?php if (isset($similars) && $similars) {
   echo '<p>Peut être, cherchiez vous : </p><ul>';
   foreach($similars as $s) {
     echo '<li>'.link_to($s['nom'], 'parlementaire/show?slug='.$s['slug']).'</li>'; 
   }
   echo '</ul>';
 }?>
<ul>
<?php foreach($pager->getResults() as $parlementaire) : ?>
<li><?php echo link_to($parlementaire->nom, 'parlementaire/show?slug='.$parlementaire->slug); ?> (<?php echo $parlementaire->getStatut(1); ?>, <?php echo link_to($parlementaire->nom_circo, '@list_parlementaires_circo?search='.$parlementaire->nom_circo); ?>)</li>
<?php endforeach ; ?>
</ul>
<?php if ($pager->haveToPaginate()) : ?>
<div class="pagination">
    <?php echo link_to('<< ', '@list_parlementaires?search='.$search.'&page=1'); ?>
    <?php echo link_to('< ', '@list_parlementaires?search='.$search.'&page='.$pager->getPreviousPage()); ?>
    <?php foreach ($pager->getLinks() as $page): ?>
      <?php if ($page == $pager->getPage()): ?>
        <?php echo $page ?>
      <?php else: ?>
        <?php echo link_to($page, '@list_parlementaires?search='.$search.'&page='.$page); ?>
      <?php endif; ?>
    <?php endforeach; ?>
    <?php echo link_to('> ', '@list_parlementaires?search='.$search.'&page='.$pager->getNextPage()); ?>
    <?php echo link_to('>> ', '@list_parlementaires?search='.$search.'&page='.$pager->getLastPage()); ?>
</div>
<?php endif; ?>
	</div>
