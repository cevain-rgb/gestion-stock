<?php
$layoutFile = BASE_PATH . '/app/Modules/Approvisionnement/Views/editions/layout_print.php';
ob_start();
?>
<?php $titreDocument = 'Liste des clients'; ?>
<div class="btn-print">
    <button class="btn-p" onclick="window.print()">🖨 Imprimer</button>
    <a class="btn-r" href="javascript:history.back()">← Retour</a>
</div>
<div class="page">
    <div class="entete">
        <div class="entete-logo">StockManager<small>Système de Gestion de Stock</small></div>
        <div class="entete-info"><strong>Date :</strong> <?= (new DateTime())->format('d/m/Y H:i') ?></div>
    </div>
    <div class="titre-doc">Liste des Clients</div>
    <p style="text-align:center;color:#64748b;margin-bottom:6mm"><?= count($clients) ?> client(s) enregistré(s)</p>
    <table class="tableau">
        <thead><tr>
            <th>Nom</th><th>Téléphone</th><th>Ville</th>
            <th>Catégorie</th><th class="r">Remise</th>
            <th class="r" style="width:8%">Cmds</th>
            <th class="r" style="width:18%">Total achats</th>
        </tr></thead>
        <tbody>
        <?php foreach ($clients as $c): ?>
        <tr>
            <td style="font-weight:600"><?= htmlspecialchars($c['nom']) ?></td>
            <td><?= htmlspecialchars($c['telephone']?:'—') ?></td>
            <td><?= htmlspecialchars($c['ville']?:'—') ?></td>
            <td style="font-size:10px"><?= htmlspecialchars($c['categorie']) ?></td>
            <td class="r"><?= $c['remise_pct'] > 0 ? $c['remise_pct'].'%' : '—' ?></td>
            <td class="r"><?= (int)$c['nb_commandes'] ?></td>
            <td class="r"><?= number_format((float)$c['total_achats'],0,',',' ') ?> FCFA</td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr>
            <td colspan="5">Total</td>
            <td class="r"><?= array_sum(array_column($clients,'nb_commandes')) ?></td>
            <td class="r"><?= number_format(array_sum(array_column($clients,'total_achats')),0,',',' ') ?> FCFA</td>
        </tr></tfoot>
    </table>
    <div class="pied"><span>StockManager — <?= (new DateTime())->format('d/m/Y à H:i') ?></span></div>
</div>

<?php
$contenu = ob_get_clean();
include $layoutFile;
