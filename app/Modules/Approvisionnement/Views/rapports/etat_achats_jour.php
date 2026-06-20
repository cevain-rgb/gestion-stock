<?php
$layoutFile = __DIR__ . '/../editions/layout_print.php';
ob_start();
?>
<?php
$titreDocument = 'État des achats — ' . (new DateTime($date))->format('d/m/Y');
$moisFR = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
?>
<div class="btn-print">
    <button class="btn-p" onclick="window.print()"><span>🖨</span> Imprimer</button>
    <a class="btn-r" href="javascript:history.back()">← Retour</a>
    <form method="GET" action="" style="display:flex;align-items:center;gap:6px;margin-left:12px">
        <input type="date" name="date" value="<?= htmlspecialchars($date) ?>" style="border:1px solid #e2e8f0;border-radius:4px;padding:4px 8px;font-size:12px">
        <button type="submit" style="background:#7c3aed;color:white;border:none;border-radius:4px;padding:4px 12px;cursor:pointer;font-size:12px">Voir</button>
    </form>
</div>
<div class="page">
    <div class="entete">
        <div class="entete-logo">StockManager<small>Système de Gestion de Stock</small></div>
        <div class="entete-info">
            <strong>Date impression :</strong> <?= (new DateTime())->format('d/m/Y H:i') ?>
        </div>
    </div>
    <div class="titre-doc">État des Achats par Jour</div>
    <div class="numero-doc"><?= (new DateTime($date))->format('d/m/Y') ?></div>
    <?php if(empty($lignes)): ?>
    <p style="text-align:center;color:#94a3b8;padding:15mm;font-size:14px">Aucun achat enregistré pour cette date.</p>
    <?php else: ?>
    <table class="tableau">
        <thead><tr>
            <th style="width:22%">N° Commande</th><th>Fournisseur</th>
            <th style="width:15%">Statut</th><th class="r" style="width:22%">Montant</th>
        </tr></thead>
        <tbody>
        <?php foreach($lignes as $l): ?>
        <tr>
            <td style="font-family:monospace"><?= htmlspecialchars($l['numero']) ?></td>
            <td><?= htmlspecialchars($l['fournisseur']) ?></td>
            <td><span class="badge badge-<?= $l['statut'] ?>"><?= ucfirst(str_replace('_',' ',$l['statut'])) ?></span></td>
            <td class="r" style="font-weight:600"><?= number_format((float)$l['montant_total'],0,',',' ') ?> FCFA</td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr>
            <td colspan="2"><strong><?= (int)$totaux['nb_commandes'] ?> commande(s)</strong></td>
            <td></td>
            <td class="r"><?= number_format((float)$totaux['total_ht'],0,',',' ') ?> FCFA</td>
        </tr></tfoot>
    </table>
    <?php endif; ?>
    <div class="pied"><span>StockManager — <?= (new DateTime())->format('d/m/Y à H:i') ?></span><span>État du <?= (new DateTime($date))->format('d/m/Y') ?></span></div>
</div>

<?php
$contenu = ob_get_clean();
include $layoutFile;
