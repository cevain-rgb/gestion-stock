<?php
$layoutFile = __DIR__ . '/layout_print.php';
ob_start();
?>
<?php $titreDocument = 'Facture — ' . $facture['numero']; ?>
<div class="btn-print">
    <button class="btn-p" onclick="window.print()"><span>🖨</span> Imprimer</button>
    <a class="btn-r" href="javascript:history.back()">← Retour</a>
</div>
<div class="page">
    <div class="entete">
        <div class="entete-logo">StockManager<small>Système de Gestion de Stock</small></div>
        <div class="entete-info">
            <strong>Date :</strong> <?= (new DateTime())->format('d/m/Y') ?>
        </div>
    </div>
    <div class="titre-doc">Facture Fournisseur</div>
    <div class="numero-doc"><?= htmlspecialchars($facture['numero']) ?></div>
    <div class="bloc-infos">
        <div>
            <h4>Fournisseur</h4>
            <p><strong><?= htmlspecialchars($facture['fournisseur']) ?></strong></p>
            <p>Commande n° : <?= htmlspecialchars($facture['numero_commande']) ?></p>
        </div>
        <div>
            <h4>Facture</h4>
            <p><strong>Date :</strong> <?= (new DateTime($facture['date_document']))->format('d/m/Y') ?></p>
            <p><strong>Statut :</strong> <span class="badge badge-<?= $facture['statut_paiement'] ?>"><?= ucfirst($facture['statut_paiement']) ?></span></p>
        </div>
    </div>
    <div class="total-box" style="margin-bottom:6mm">
        <div class="total-row"><span class="total-label">Montant HT</span><span class="total-value"><?= number_format((float)$facture['montant_ht'],0,',',' ') ?> FCFA</span></div>
        <div class="total-row"><span class="total-label">TVA (<?= $facture['taux_tva'] ?>%)</span><span class="total-value"><?= number_format((float)$facture['montant_tva'],0,',',' ') ?> FCFA</span></div>
        <div class="total-row total-ttc"><span class="total-label">Total TTC</span><span class="total-value"><?= number_format((float)$facture['montant_ttc'],0,',',' ') ?> FCFA</span></div>
        <div class="total-row" style="margin-top:3mm"><span class="total-label">Reste à payer</span>
            <span class="total-value" style="color:<?= (float)$facture['reste_a_payer']>0?'#991b1b':'#065f46' ?>"><?= number_format((float)$facture['reste_a_payer'],0,',',' ') ?> FCFA</span></div>
    </div>
    <?php if(!empty($facture['reglements'])): ?>
    <p style="font-size:11px;font-weight:700;margin-bottom:3mm">Règlements enregistrés :</p>
    <table class="tableau">
        <thead><tr><th>Date</th><th>Mode</th><th>Référence</th><th class="r">Montant</th></tr></thead>
        <tbody>
        <?php foreach($facture['reglements'] as $r): ?>
        <tr>
            <td><?= (new DateTime($r['date_reglement']))->format('d/m/Y') ?></td>
            <td><?= htmlspecialchars(ucfirst(str_replace('_',' ',$r['mode_paiement']))) ?></td>
            <td><?= htmlspecialchars($r['reference']?:'—') ?></td>
            <td class="r"><?= number_format((float)$r['montant'],0,',',' ') ?> FCFA</td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
    <div class="signatures">
        <div class="signature-box"><div class="ligne-sign"></div><p>Comptabilité</p></div>
        <div class="signature-box"><div class="ligne-sign"></div><p>Direction</p></div>
    </div>
    <div class="pied"><span>StockManager — <?= (new DateTime())->format('d/m/Y à H:i') ?></span><span><?= htmlspecialchars($facture['numero']) ?></span></div>
</div>

<?php
$contenu = ob_get_clean();
include $layoutFile;
