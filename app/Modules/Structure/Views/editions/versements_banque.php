<?php
$layoutFile = BASE_PATH . '/app/Modules/Approvisionnement/Views/editions/layout_print.php';
ob_start();
?>
<?php $titreDocument = 'État des versements en banque'; ?>
<div class="btn-print">
    <button class="btn-p" onclick="window.print()">🖨 Imprimer</button>
    <a class="btn-r" href="javascript:history.back()">← Retour</a>
    <form method="GET" action="" style="display:flex;align-items:center;gap:6px;margin-left:12px">
        <label style="font-size:11px;color:#475569">Du</label>
        <input type="date" name="debut" value="<?= htmlspecialchars($debut) ?>" style="border:1px solid #e2e8f0;border-radius:4px;padding:3px 8px;font-size:11px">
        <label style="font-size:11px;color:#475569">Au</label>
        <input type="date" name="fin" value="<?= htmlspecialchars($fin) ?>" style="border:1px solid #e2e8f0;border-radius:4px;padding:3px 8px;font-size:11px">
        <select name="id_banque" style="border:1px solid #e2e8f0;border-radius:4px;padding:3px 8px;font-size:11px">
            <option value="0">Toutes les banques</option>
            <?php foreach ($banques as $b): ?>
            <option value="<?= $b['id_banque'] ?>" <?= $b['id_banque']==$idBanque?'selected':'' ?>><?= htmlspecialchars($b['nom']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" style="background:#7c3aed;color:white;border:none;border-radius:4px;padding:3px 10px;cursor:pointer;font-size:11px">Voir</button>
    </form>
</div>
<div class="page">
    <div class="entete">
        <div class="entete-logo">StockManager<small>Système de Gestion de Stock</small></div>
        <div class="entete-info"><strong>Date :</strong> <?= (new DateTime())->format('d/m/Y H:i') ?></div>
    </div>
    <div class="titre-doc">État des Versements en Banque</div>
    <div class="numero-doc"><?= (new DateTime($debut))->format('d/m/Y') ?> → <?= (new DateTime($fin))->format('d/m/Y') ?></div>
    <div style="display:flex;gap:8mm;margin-bottom:8mm">
        <div style="flex:1;background:#f8fafc;border:1px solid #e2e8f0;border-radius:4px;padding:4mm;text-align:center">
            <div style="font-size:22px;font-weight:900;color:#7c3aed"><?= number_format((float)$totaux['total'],0,',',' ') ?> FCFA</div>
            <div style="font-size:10px;color:#64748b">Total versé</div>
        </div>
        <div style="flex:1;background:#f8fafc;border:1px solid #e2e8f0;border-radius:4px;padding:4mm;text-align:center">
            <div style="font-size:22px;font-weight:900;color:#1e40af"><?= (int)$totaux['nb'] ?></div>
            <div style="font-size:10px;color:#64748b">Versements</div>
        </div>
    </div>
    <?php if (empty($versements)): ?>
    <p style="text-align:center;color:#94a3b8;padding:15mm">Aucun versement sur cette période.</p>
    <?php else: ?>
    <table class="tableau">
        <thead><tr>
            <th style="width:14%">Date</th><th>Banque</th>
            <th class="r" style="width:22%">Montant</th>
            <th style="width:30%">Référence</th>
        </tr></thead>
        <tbody>
        <?php foreach ($versements as $v): ?>
        <tr>
            <td><?= (new DateTime($v['date_versement']))->format('d/m/Y') ?></td>
            <td style="font-weight:600"><?= htmlspecialchars($v['banque']) ?></td>
            <td class="r"><?= number_format((float)$v['montant'],0,',',' ') ?> FCFA</td>
            <td style="font-size:10px;color:#64748b"><?= htmlspecialchars($v['reference']?:'—') ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr>
            <td colspan="2"><?= (int)$totaux['nb'] ?> versement(s)</td>
            <td class="r"><?= number_format((float)$totaux['total'],0,',',' ') ?> FCFA</td>
            <td></td>
        </tr></tfoot>
    </table>
    <?php endif; ?>
    <div class="pied"><span>StockManager — <?= (new DateTime())->format('d/m/Y à H:i') ?></span></div>
</div>

<?php
$contenu = ob_get_clean();
include $layoutFile;
