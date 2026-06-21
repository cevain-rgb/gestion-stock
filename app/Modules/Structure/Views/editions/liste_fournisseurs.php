<?php
$layoutFile = BASE_PATH . '/app/Modules/Approvisionnement/Views/editions/layout_print.php';
ob_start();
?>
<?php $titreDocument = 'Liste des fournisseurs'; ?>
<div class="btn-print">
    <button class="btn-p" onclick="window.print()">🖨 Imprimer</button>
    <a class="btn-r" href="javascript:history.back()">← Retour</a>
</div>
<div class="page">
    <div class="entete">
        <div class="entete-logo">StockManager<small>Système de Gestion de Stock</small></div>
        <div class="entete-info"><strong>Date :</strong> <?= (new DateTime())->format('d/m/Y H:i') ?></div>
    </div>
    <div class="titre-doc">Liste des Fournisseurs</div>
    <p style="text-align:center;color:#64748b;margin-bottom:6mm"><?= count($fournisseurs) ?> fournisseur(s) enregistré(s)</p>
    <table class="tableau">
        <thead><tr>
            <th>Nom</th><th>Téléphone</th><th>Email</th><th>Ville</th>
            <th class="r" style="width:10%">Cmds</th>
            <th class="r" style="width:18%">Total achats</th>
        </tr></thead>
        <tbody>
        <?php foreach ($fournisseurs as $f): ?>
        <tr>
            <td style="font-weight:600"><?= htmlspecialchars($f['nom']) ?></td>
            <td><?= htmlspecialchars($f['telephone']?:'—') ?></td>
            <td style="font-size:10px"><?= htmlspecialchars($f['email']?:'—') ?></td>
            <td><?= htmlspecialchars($f['ville']?:'—') ?></td>
            <td class="r"><?= (int)$f['nb_commandes'] ?></td>
            <td class="r"><?= number_format((float)$f['total_commandes'],0,',',' ') ?> FCFA</td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr>
            <td colspan="4">Total</td>
            <td class="r"><?= array_sum(array_column($fournisseurs,'nb_commandes')) ?></td>
            <td class="r"><?= number_format(array_sum(array_column($fournisseurs,'total_commandes')),0,',',' ') ?> FCFA</td>
        </tr></tfoot>
    </table>
    <div class="pied"><span>StockManager — <?= (new DateTime())->format('d/m/Y à H:i') ?></span></div>
</div>

<?php
$contenu = ob_get_clean();
include $layoutFile;
