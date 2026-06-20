<?php
$layoutFile = __DIR__ . '/layout_print.php';
ob_start();
?>
<?php $titreDocument = 'Bon de Réception — ' . $reception['numero']; ?>
<div class="btn-print">
    <button class="btn-p" onclick="window.print()"><span>🖨</span> Imprimer</button>
    <a class="btn-r" href="javascript:history.back()">← Retour</a>
</div>
<div class="page">
    <div class="entete">
        <div class="entete-logo">StockManager<small>Système de Gestion de Stock</small></div>
        <div class="entete-info">
            <strong>Date :</strong> <?= (new DateTime())->format('d/m/Y') ?><br>
            <strong>Imprimé par :</strong> <?= htmlspecialchars($_SESSION['user_login']??'') ?>
        </div>
    </div>
    <div class="titre-doc">Bon de Réception</div>
    <div class="numero-doc"><?= htmlspecialchars($reception['numero']) ?></div>
    <div class="bloc-infos">
        <div>
            <h4>Fournisseur</h4>
            <p><strong><?= htmlspecialchars($reception['fournisseur']) ?></strong></p>
            <p>Commande n° : <?= htmlspecialchars($reception['numero_commande']) ?></p>
        </div>
        <div>
            <h4>Réception</h4>
            <p><strong>Date :</strong> <?= (new DateTime($reception['date_document']))->format('d/m/Y') ?></p>
            <p><strong>Réceptionné par :</strong> <?= htmlspecialchars($reception['cree_par']) ?></p>
        </div>
    </div>
    <table class="tableau">
        <thead><tr>
            <th style="width:15%">Code</th><th>Désignation</th>
            <th class="r" style="width:14%">Qté reçue</th><th class="r" style="width:10%">Unité</th>
            <th class="r" style="width:18%">Prix unit.</th><th class="r" style="width:18%">Montant</th>
        </tr></thead>
        <tbody>
        <?php foreach($reception['lignes'] as $l): ?>
        <tr>
            <td><?= htmlspecialchars($l['code']) ?></td><td><?= htmlspecialchars($l['designation']) ?></td>
            <td class="r" style="font-weight:700;color:#065f46"><?= number_format((float)$l['quantite_recue'],2,',',' ') ?></td>
            <td class="r"><?= htmlspecialchars($l['unite']) ?></td>
            <td class="r"><?= number_format((float)$l['prix_unitaire'],0,',',' ') ?> FCFA</td>
            <td class="r"><?= number_format((float)$l['montant_ligne'],0,',',' ') ?> FCFA</td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr><td colspan="5">Total</td>
            <td class="r"><?= number_format(array_sum(array_column($reception['lignes'],'montant_ligne')),0,',',' ') ?> FCFA</td>
        </tr></tfoot>
    </table>
    <?php if(!empty($reception['observations'])): ?><p style="font-size:11px;margin-bottom:8mm;padding:3mm;background:#f8fafc;border-left:3px solid #7c3aed;"><strong>Observations :</strong> <?= htmlspecialchars($reception['observations']) ?></p><?php endif; ?>
    <div class="signatures">
        <div class="signature-box"><div class="ligne-sign"></div><p>Magasinier</p></div>
        <div class="signature-box"><div class="ligne-sign"></div><p>Responsable</p></div>
        <div class="signature-box"><div class="ligne-sign"></div><p>Fournisseur / Livreur</p></div>
    </div>
    <div class="pied"><span>StockManager — <?= (new DateTime())->format('d/m/Y à H:i') ?></span><span><?= htmlspecialchars($reception['numero']) ?></span></div>
</div>

<?php
$contenu = ob_get_clean();
include $layoutFile;
