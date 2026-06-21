<?php
$layoutFile = BASE_PATH . '/app/Modules/Approvisionnement/Views/editions/layout_print.php';
ob_start();
?>
<?php $titreDocument = 'État des ventes — ' . (new DateTime($date))->format('d/m/Y'); ?>
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
        <div class="entete-info"><strong>Date impression :</strong> <?= (new DateTime())->format('d/m/Y H:i') ?></div>
    </div>
    <div class="titre-doc">État des Ventes par Jour</div>
    <div class="numero-doc"><?= (new DateTime($date))->format('d/m/Y') ?></div>

    <!-- KPI du jour -->
    <div style="display:flex;gap:8mm;margin-bottom:8mm">
        <div style="flex:1;background:#f8fafc;border:1px solid #e2e8f0;border-radius:4px;padding:4mm;text-align:center">
            <div style="font-size:20px;font-weight:900;color:#7c3aed"><?= number_format((float)$totaux['total'],0,',',' ') ?> FCFA</div>
            <div style="font-size:10px;color:#64748b">CA total</div>
        </div>
        <div style="flex:1;background:#f8fafc;border:1px solid #e2e8f0;border-radius:4px;padding:4mm;text-align:center">
            <div style="font-size:20px;font-weight:900;color:#065f46"><?= number_format((float)$totaux['total_comptant'],0,',',' ') ?> FCFA</div>
            <div style="font-size:10px;color:#64748b">Comptant</div>
        </div>
        <div style="flex:1;background:#f8fafc;border:1px solid #e2e8f0;border-radius:4px;padding:4mm;text-align:center">
            <div style="font-size:20px;font-weight:900;color:#1e40af"><?= number_format((float)$totaux['total_credit'],0,',',' ') ?> FCFA</div>
            <div style="font-size:10px;color:#64748b">Crédit</div>
        </div>
        <div style="flex:1;background:#f8fafc;border:1px solid #e2e8f0;border-radius:4px;padding:4mm;text-align:center">
            <div style="font-size:20px;font-weight:900;color:#92400e"><?= (int)$totaux['nb_commandes'] ?></div>
            <div style="font-size:10px;color:#64748b">Commandes</div>
        </div>
    </div>

    <?php if (empty($lignes)): ?>
    <p style="text-align:center;color:#94a3b8;padding:15mm;font-size:14px">Aucune vente enregistrée pour cette date.</p>
    <?php else: ?>
    <table class="tableau">
        <thead><tr>
            <th style="width:20%">N° Commande</th><th>Client</th>
            <th style="width:10%">Type</th>
            <th style="width:15%">Statut</th>
            <th class="r" style="width:20%">Montant</th>
        </tr></thead>
        <tbody>
        <?php foreach ($lignes as $l): ?>
        <tr>
            <td style="font-family:monospace"><?= htmlspecialchars($l['numero']) ?></td>
            <td><?= htmlspecialchars($l['client']) ?></td>
            <td><span style="font-size:9px;background:<?= $l['est_comptant']?'#d1fae5':'#dbeafe' ?>;color:<?= $l['est_comptant']?'#065f46':'#1e40af' ?>;padding:1mm 2mm;border-radius:2px;font-weight:700"><?= $l['est_comptant']?'Comptant':'Crédit' ?></span></td>
            <td><span class="badge badge-<?= $l['statut'] ?>"><?= ucfirst(str_replace('_',' ',$l['statut'])) ?></span></td>
            <td class="r" style="font-weight:600"><?= number_format((float)$l['montant_total'],0,',',' ') ?> FCFA</td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr>
            <td colspan="3"><strong><?= (int)$totaux['nb_commandes'] ?> commande(s)</strong></td>
            <td></td>
            <td class="r"><?= number_format((float)$totaux['total'],0,',',' ') ?> FCFA</td>
        </tr></tfoot>
    </table>
    <?php endif; ?>
    <div class="pied">
        <span>StockManager — <?= (new DateTime())->format('d/m/Y à H:i') ?></span>
        <span>État du <?= (new DateTime($date))->format('d/m/Y') ?></span>
    </div>
</div>

<?php
$contenu = ob_get_clean();
include $layoutFile;
