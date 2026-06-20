<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $titreDocument ?? 'Document' ?></title>
<style>
/* ── Reset ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Arial', sans-serif; font-size: 12px; color: #1e293b; background: #fff; }

/* ── Mise en page A4 ── */
.page { width: 210mm; min-height: 297mm; margin: 0 auto; padding: 15mm 18mm; }
.entete { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10mm; border-bottom: 2px solid #7c3aed; padding-bottom: 6mm; }
.entete-logo { font-size: 22px; font-weight: 900; color: #7c3aed; letter-spacing: -0.5px; }
.entete-logo small { display: block; font-size: 10px; font-weight: 400; color: #64748b; letter-spacing: 0.5px; }
.entete-info { text-align: right; font-size: 10px; color: #64748b; line-height: 1.6; }

.titre-doc { text-align: center; font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 8mm; text-transform: uppercase; letter-spacing: 1px; }
.numero-doc { text-align: center; font-size: 20px; font-weight: 800; color: #7c3aed; margin-bottom: 8mm; }

/* ── Blocs infos bicolonnes ── */
.bloc-infos { display: flex; gap: 10mm; margin-bottom: 8mm; }
.bloc-infos > div { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 4mm; }
.bloc-infos h4 { font-size: 9px; text-transform: uppercase; letter-spacing: 0.8px; color: #94a3b8; font-weight: 600; margin-bottom: 3mm; }
.bloc-infos p { font-size: 11px; line-height: 1.7; }
.bloc-infos p strong { color: #1e293b; }

/* ── Tableau ── */
.tableau { width: 100%; border-collapse: collapse; margin-bottom: 8mm; }
.tableau thead tr { background: #7c3aed; color: white; }
.tableau thead th { padding: 3mm 4mm; text-align: left; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
.tableau thead th.r { text-align: right; }
.tableau tbody tr { border-bottom: 1px solid #e2e8f0; }
.tableau tbody tr:nth-child(even) { background: #f8fafc; }
.tableau tbody td { padding: 2.5mm 4mm; font-size: 11px; }
.tableau tbody td.r { text-align: right; }
.tableau tfoot tr { border-top: 2px solid #7c3aed; }
.tableau tfoot td { padding: 3mm 4mm; font-size: 12px; font-weight: 700; }
.tableau tfoot td.r { text-align: right; color: #7c3aed; }

/* ── Total box ── */
.total-box { display: flex; flex-direction: column; align-items: flex-end; gap: 1.5mm; margin-bottom: 10mm; }
.total-row { display: flex; gap: 20mm; font-size: 11px; }
.total-row.total-ttc { font-size: 14px; font-weight: 800; color: #7c3aed; border-top: 1px solid #7c3aed; padding-top: 2mm; margin-top: 1mm; }
.total-label { text-align: right; min-width: 50mm; color: #64748b; }
.total-value { text-align: right; min-width: 40mm; font-weight: 600; }

/* ── Signatures ── */
.signatures { display: flex; justify-content: space-between; margin-top: 15mm; }
.signature-box { text-align: center; width: 60mm; }
.signature-box .ligne-sign { border-bottom: 1px solid #94a3b8; margin-bottom: 2mm; height: 15mm; }
.signature-box p { font-size: 10px; color: #64748b; }

/* ── Badge statut ── */
.badge { display: inline-block; padding: 1mm 3mm; border-radius: 3px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
.badge-en_attente { background: #fef3c7; color: #92400e; }
.badge-validee     { background: #dbeafe; color: #1e40af; }
.badge-recue       { background: #d1fae5; color: #065f46; }
.badge-impayee     { background: #fee2e2; color: #991b1b; }
.badge-partielle   { background: #fef3c7; color: #92400e; }
.badge-soldee      { background: #d1fae5; color: #065f46; }

/* ── Pied de page ── */
.pied { margin-top: auto; padding-top: 6mm; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; font-size: 9px; color: #94a3b8; }

/* ── Bouton d'impression (écran seulement) ── */
.btn-print { display: flex; gap: 8px; margin: 10px auto 0; justify-content: center; }
.btn-print button, .btn-print a { display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; }
.btn-print .btn-p { background: #7c3aed; color: white; }
.btn-print .btn-r { background: #f1f5f9; color: #475569; }

@media print {
    .btn-print { display: none; }
    body { background: white; }
    .page { padding: 10mm 15mm; margin: 0; }
    @page { size: A4; margin: 0; }
}
</style>
</head>
<body>
<?= $contenu ?? '' ?>
</body>
</html>
