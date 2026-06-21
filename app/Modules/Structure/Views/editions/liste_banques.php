<?php
$layoutFile = BASE_PATH . '/app/Modules/Approvisionnement/Views/editions/layout_print.php';
ob_start();
?>
<?php $titreDocument = 'Liste des banques'; ?>
<div class="btn-print">
    <button class="btn-p" onclick="window.print()">🖨 Imprimer</button>
    <a class="btn-r" href="javascript:history.back()">← Retour</a>
</div>
<div class="page">
    <div class="entete">
        <div class="entete-logo">StockManager<small>Système de Gestion de Stock</small></div>
        <div class="entete-info"><strong>Date :</strong> <?= (new DateTime())->format('d/m/Y H:i') ?></div>
    </div>
    <div class="titre-doc">Liste des Banques</div>
    <table class="tableau">
        <thead><tr>
            <th>Banque</th><th>N° Compte</th><th>Ville</th><th>Pays</th>
            <th class="r">Versements</th><th class="r">Total versé</th>
        </tr></thead>
        <tbody>
        <?php foreach ($banques as $b): ?>
        <tr>
            <td style="font-weight:600"><?= htmlspecialchars($b['nom']) ?></td>
            <td style="font-family:monospace;font-size:10px"><?= htmlspecialchars($b['numero_compte']?:'—') ?></td>
            <td><?= htmlspecialchars($b['ville']?:'—') ?></td>
            <td><?= htmlspecialchars($b['pays']?:'—') ?></td>
            <td class="r"><?= (int)$b['nb_versements'] ?></td>
            <td class="r"><?= number_format((float)$b['total_verses'],0,',',' ') ?> FCFA</td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr>
            <td colspan="4">Total</td>
            <td class="r"><?= array_sum(array_column($banques,'nb_versements')) ?></td>
            <td class="r"><?= number_format(array_sum(array_column($banques,'total_verses')),0,',',' ') ?> FCFA</td>
        </tr></tfoot>
    </table>
    <div class="pied"><span>StockManager — <?= (new DateTime())->format('d/m/Y à H:i') ?></span></div>
</div>

<?php
$contenu = ob_get_clean();
include $layoutFile;
