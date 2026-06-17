<?php
declare(strict_types=1);
namespace App\Modules\Structure\Services;

use App\Modules\Structure\Models\{FamilleModel, ProduitModel, FournisseurModel,
    ClientModel, CategorieClientModel, BanqueModel};
use App\Shared\Traits\AuditableTrait;

class StructureService
{
    use AuditableTrait;

    private FamilleModel        $familles;
    private ProduitModel        $produits;
    private FournisseurModel    $fournisseurs;
    private ClientModel         $clients;
    private CategorieClientModel $categories;
    private BanqueModel         $banques;

    public function __construct()
    {
        $this->familles     = new FamilleModel();
        $this->produits     = new ProduitModel();
        $this->fournisseurs = new FournisseurModel();
        $this->clients      = new ClientModel();
        $this->categories   = new CategorieClientModel();
        $this->banques      = new BanqueModel();
    }

    // ═══ FAMILLES ════════════════════════════════════════════════════════════

    public function listerFamilles(int $page, string $rech = ''): array
    {
        $total = $this->familles->compter($rech);
        return ['lignes' => $this->familles->tous($page, 20, $rech), 'total' => $total,
                'page' => $page, 'perPage' => 20, 'totalPages' => max(1,(int)ceil($total/20)),
                'recherche' => $rech];
    }

    public function creerFamille(array $d): array
    {
        if (empty(trim($d['libelle'] ?? ''))) return ['ok'=>false,'errors'=>['libelle'=>'Libellé obligatoire.']];
        if ($this->familles->libelleExiste($d['libelle'])) return ['ok'=>false,'errors'=>['libelle'=>'Libellé déjà utilisé.']];
        $id = $this->familles->creer(trim($d['libelle']), trim($d['description'] ?? ''));
        $this->journaliser('INSERT', 'famille_produit', $id, null, $d);
        return ['ok'=>true,'id'=>$id];
    }

    public function modifierFamille(int $id, array $d): array
    {
        $ancien = $this->familles->trouverParId($id);
        if (!$ancien) return ['ok'=>false,'errors'=>['global'=>'Famille introuvable.']];
        if (empty(trim($d['libelle'] ?? ''))) return ['ok'=>false,'errors'=>['libelle'=>'Libellé obligatoire.']];
        if ($this->familles->libelleExiste($d['libelle'], $id)) return ['ok'=>false,'errors'=>['libelle'=>'Libellé déjà utilisé.']];
        $this->familles->modifier($id, trim($d['libelle']), trim($d['description'] ?? ''));
        $this->journaliser('UPDATE', 'famille_produit', $id, $ancien, $d);
        return ['ok'=>true];
    }

    public function supprimerFamille(int $id): array
    {
        if ($this->familles->aProduits($id)) return ['ok'=>false,'message'=>'Cette famille contient des produits actifs.'];
        $ancien = $this->familles->trouverParId($id);
        $this->archivageXml('famille_produit', $id, $ancien ?: []);
        $this->familles->supprimer($id);
        $this->journaliser('DELETE', 'famille_produit', $id, $ancien, null);
        return ['ok'=>true];
    }

    // ═══ PRODUITS ════════════════════════════════════════════════════════════

    public function listerProduits(int $page, array $filtres): array
    {
        $total = $this->produits->compter($filtres);
        return ['lignes'   => $this->produits->tous($page, 20, $filtres),
                'total'    => $total, 'page' => $page, 'perPage' => 20,
                'totalPages' => max(1,(int)ceil($total/20)),
                'filtres'  => $filtres,
                'familles' => $this->familles->toutesOptions(),
                'stats'    => $this->produits->statsStock()];
    }

    public function creerProduit(array $d): array
    {
        $errors = $this->validerProduit($d);
        if ($errors) return ['ok'=>false,'errors'=>$errors];
        if ($this->produits->codeExiste($d['code'])) return ['ok'=>false,'errors'=>['code'=>'Ce code existe déjà.']];
        $id = $this->produits->creer($d);
        $this->journaliser('INSERT', 'produit', $id, null, $d);
        return ['ok'=>true,'id'=>$id];
    }

    public function modifierProduit(int $id, array $d): array
    {
        $ancien = $this->produits->trouverParId($id);
        if (!$ancien) return ['ok'=>false,'errors'=>['global'=>'Produit introuvable.']];
        $errors = $this->validerProduit($d);
        if ($errors) return ['ok'=>false,'errors'=>$errors];
        if ($this->produits->codeExiste($d['code'], $id)) return ['ok'=>false,'errors'=>['code'=>'Ce code existe déjà.']];
        $this->produits->modifier($id, $d);
        $this->journaliser('UPDATE', 'produit', $id, $ancien, $d);
        return ['ok'=>true];
    }

    public function supprimerProduit(int $id): array
    {
        $ancien = $this->produits->trouverParId($id);
        if (!$ancien) return ['ok'=>false,'message'=>'Produit introuvable.'];
        $this->archivageXml('produit', $id, $ancien);
        $this->produits->supprimer($id);
        $this->journaliser('DELETE', 'produit', $id, $ancien, null);
        return ['ok'=>true];
    }

    private function validerProduit(array $d): array
    {
        $e = [];
        if (empty(trim($d['code'] ?? '')))        $e['code']        = 'Code obligatoire.';
        if (empty(trim($d['designation'] ?? '')))  $e['designation'] = 'Désignation obligatoire.';
        if (empty($d['id_famille']))               $e['id_famille']  = 'Famille obligatoire.';
        if (isset($d['prix_achat'])  && (float)$d['prix_achat']  < 0) $e['prix_achat']  = 'Prix négatif.';
        if (isset($d['prix_vente'])  && (float)$d['prix_vente']  < 0) $e['prix_vente']  = 'Prix négatif.';
        if (isset($d['stock_alerte'])&& (float)$d['stock_alerte']< 0) $e['stock_alerte']= 'Seuil négatif.';
        return $e;
    }

    // ═══ FOURNISSEURS ════════════════════════════════════════════════════════

    public function listerFournisseurs(int $page, string $rech = ''): array
    {
        $total = $this->fournisseurs->compter($rech);
        return ['lignes' => $this->fournisseurs->tous($page, 20, $rech), 'total' => $total,
                'page' => $page, 'perPage' => 20, 'totalPages' => max(1,(int)ceil($total/20)),
                'recherche' => $rech];
    }

    public function creerFournisseur(array $d): array
    {
        if (empty(trim($d['nom'] ?? ''))) return ['ok'=>false,'errors'=>['nom'=>'Nom obligatoire.']];
        $id = $this->fournisseurs->creer($d);
        $this->journaliser('INSERT', 'fournisseur', $id, null, $d);
        return ['ok'=>true,'id'=>$id];
    }

    public function modifierFournisseur(int $id, array $d): array
    {
        $ancien = $this->fournisseurs->trouverParId($id);
        if (!$ancien) return ['ok'=>false,'errors'=>['global'=>'Fournisseur introuvable.']];
        if (empty(trim($d['nom'] ?? ''))) return ['ok'=>false,'errors'=>['nom'=>'Nom obligatoire.']];
        $this->fournisseurs->modifier($id, $d);
        $this->journaliser('UPDATE', 'fournisseur', $id, $ancien, $d);
        return ['ok'=>true];
    }

    public function supprimerFournisseur(int $id): array
    {
        $ancien = $this->fournisseurs->trouverParId($id);
        $this->archivageXml('fournisseur', $id, $ancien ?: []);
        $this->fournisseurs->supprimer($id);
        $this->journaliser('DELETE', 'fournisseur', $id, $ancien, null);
        return ['ok'=>true];
    }

    // ═══ CLIENTS ═════════════════════════════════════════════════════════════

    public function listerClients(int $page, array $filtres): array
    {
        $total = $this->clients->compter($filtres);
        return ['lignes'     => $this->clients->tous($page, 20, $filtres),
                'total'      => $total, 'page' => $page, 'perPage' => 20,
                'totalPages' => max(1,(int)ceil($total/20)),
                'filtres'    => $filtres,
                'categories' => $this->categories->toutesOptions()];
    }

    public function creerClient(array $d): array
    {
        if (empty(trim($d['nom'] ?? '')))    return ['ok'=>false,'errors'=>['nom'=>'Nom obligatoire.']];
        if (empty($d['id_categorie']))        return ['ok'=>false,'errors'=>['id_categorie'=>'Catégorie obligatoire.']];
        $id = $this->clients->creer($d);
        $this->journaliser('INSERT', 'client', $id, null, $d);
        return ['ok'=>true,'id'=>$id];
    }

    public function modifierClient(int $id, array $d): array
    {
        $ancien = $this->clients->trouverParId($id);
        if (!$ancien) return ['ok'=>false,'errors'=>['global'=>'Client introuvable.']];
        if (empty(trim($d['nom'] ?? ''))) return ['ok'=>false,'errors'=>['nom'=>'Nom obligatoire.']];
        $this->clients->modifier($id, $d);
        $this->journaliser('UPDATE', 'client', $id, $ancien, $d);
        return ['ok'=>true];
    }

    public function supprimerClient(int $id): array
    {
        $ancien = $this->clients->trouverParId($id);
        $this->archivageXml('client', $id, $ancien ?: []);
        $this->clients->supprimer($id);
        $this->journaliser('DELETE', 'client', $id, $ancien, null);
        return ['ok'=>true];
    }

    // ═══ CATÉGORIES CLIENT ════════════════════════════════════════════════════

    public function listerCategories(): array   { return $this->categories->toutes(); }

    public function creerCategorie(array $d): array
    {
        if (empty(trim($d['libelle'] ?? ''))) return ['ok'=>false,'errors'=>['libelle'=>'Libellé obligatoire.']];
        $remise = max(0, min(100, (float)($d['remise_pct'] ?? 0)));
        $id = $this->categories->creer(trim($d['libelle']), $remise);
        $this->journaliser('INSERT', 'categorie_client', $id, null, $d);
        return ['ok'=>true,'id'=>$id];
    }

    public function modifierCategorie(int $id, array $d): array
    {
        if (empty(trim($d['libelle'] ?? ''))) return ['ok'=>false,'errors'=>['libelle'=>'Libellé obligatoire.']];
        $ancien = $this->categories->trouverParId($id);
        $this->categories->modifier($id, trim($d['libelle']), max(0,min(100,(float)($d['remise_pct']??0))));
        $this->journaliser('UPDATE', 'categorie_client', $id, $ancien, $d);
        return ['ok'=>true];
    }

    public function supprimerCategorie(int $id): array
    {
        if ($this->categories->aClients($id)) return ['ok'=>false,'message'=>'Catégorie avec clients actifs.'];
        $this->categories->supprimer($id);
        $this->journaliser('DELETE', 'categorie_client', $id, null, null);
        return ['ok'=>true];
    }

    // ═══ BANQUES ══════════════════════════════════════════════════════════════

    public function listerBanques(): array  { return $this->banques->toutes(); }

    public function creerBanque(array $d): array
    {
        if (empty(trim($d['nom'] ?? ''))) return ['ok'=>false,'errors'=>['nom'=>'Nom obligatoire.']];
        $id = $this->banques->creer($d);
        $this->journaliser('INSERT', 'banque', $id, null, $d);
        return ['ok'=>true,'id'=>$id];
    }

    public function modifierBanque(int $id, array $d): array
    {
        $ancien = $this->banques->trouverParId($id);
        if (!$ancien) return ['ok'=>false,'errors'=>['global'=>'Banque introuvable.']];
        $this->banques->modifier($id, $d);
        $this->journaliser('UPDATE', 'banque', $id, $ancien, $d);
        return ['ok'=>true];
    }

    public function supprimerBanque(int $id): array
    {
        $this->banques->supprimer($id);
        $this->journaliser('DELETE', 'banque', $id, null, null);
        return ['ok'=>true];
    }

    public function ajouterVersement(int $id, array $d): array
    {
        if (empty($d['montant']) || (float)$d['montant'] <= 0)
            return ['ok'=>false,'errors'=>['montant'=>'Montant invalide.']];
        $this->banques->ajouterVersement($id, (float)$d['montant'],
            $d['date_versement'] ?? date('Y-m-d'), trim($d['reference'] ?? ''));
        return ['ok'=>true];
    }

    // ═══ Accesseurs modèles (pour les contrôleurs) ═══════════════════════════

    public function getFamilleModel():     FamilleModel      { return $this->familles; }
    public function getProduitModel():     ProduitModel      { return $this->produits; }
    public function getFournisseurModel(): FournisseurModel  { return $this->fournisseurs; }
    public function getClientModel():      ClientModel       { return $this->clients; }
    public function getCategorieModel():   CategorieClientModel { return $this->categories; }
    public function getBanqueModel():      BanqueModel       { return $this->banques; }
}
