<?php
$layoutFile = __DIR__ . '/layout_print.php';
ob_start();
?>
<?php $titreDocument = 'Reçu de règlement'; ?>
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
    <div class="titre-doc">Reçu de Règlement Fournisseur</div>
    <div class="numero-doc">Règlement n° <?= $regl['id_reglement_f'] ?></div>
    <div class="bloc-infos">
        <div>
            <h4>Fournisseur</h4>
            <p><strong><?= htmlspecialchars($facture['fournisseur']) ?></strong></p>
            <p>Facture n° : <?= htmlspecialchars($facture['numero']) ?></p>
        </div>
        <div>
            <h4>Paiement</h4>
            <p><strong>Date :</strong> <?= (new DateTime($regl['date_reglement']))->format('d/m/Y') ?></p>
            <p><strong>Mode :</strong> <?= htmlspecialchars(ucfirst(str_replace('_',' ',$regl['mode_paiement']))) ?></p>
            <?php if($banque): ?><p><strong>Banque :</strong> <?= htmlspecialchars($banque['nom']) ?></p><?php endif; ?>
            <?php if(!empty($regl['reference'])): ?><p><strong>Réf :</strong> <?= htmlspecialchars($regl['reference']) ?></p><?php endif; ?>
        </div>
    </div>
    <div style="text-align:center;margin:10mm 0;padding:8mm;border:2px solid #7c3aed;border-radius:8px;">
        <p style="font-size:12px;color:#64748b;margin-bottom:4mm;">Montant réglé</p>
        <p style="font-size:30px;font-weight:900;color:#7c3aed;"><?= number_format((float)$regl['montant'],0,',',' ') ?> FCFA</p>
    </div>
    <div class="total-box" style="margin-top:6mm">
        <div class="total-row"><span class="total-label">Montant TTC facture</span><span class="total-value"><?= number_format((float)$facture['montant_ttc'],0,',',' ') ?> FCFA</span></div>
        <div class="total-row"><span class="total-label">Ce règlement</span><span class="total-value"><?= number_format((float)$regl['montant'],0,',',' ') ?> FCFA</span></div>
        <div class="total-row total-ttc"><span class="total-label">Reste à payer</span>
            <span class="total-value" style="color:<?= (float)$facture['reste_a_payer']>0?'#991b1b':'#065f46' ?>"><?= number_format((float)$facture['reste_a_payer'],0,',',' ') ?> FCFA</span></div>
    </div>
    <div class="signatures" style="margin-top:12mm">
        <div class="signature-box"><div class="ligne-sign"></div><p>Caissier / Comptable</p></div>
        <div class="signature-box"><div class="ligne-sign"></div><p>Fournisseur</p></div>
    </div>
    <div class="pied"><span>StockManager — <?= (new DateTime())->format('d/m/Y à H:i') ?></span></div>
</div>

<?php
$contenu = ob_get_clean();
include $layoutFile;
