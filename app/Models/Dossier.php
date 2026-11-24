<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Dossier extends Model
{
    use HasFactory;

    protected $casts = [
        'date_sortie' => 'date:Y-m-d',
    ];
    /*protected $fillable = [
        'numero',
        'date_enregistrement',
        'avis_mp',
        'avis_dgapr',
        'avis_gouverneur',
        'typedossier_id',
        'detenu_id',
        'typemotifdossiers_id',
        'categoriedossiers_id',
        'naturedossiers_id',
        'prison_id',
        'user_id',
        'numero_detention',
        'numero_dapg',
        'date_sortie',
        'tr_tribunal',
        'date_tr_tribunal',
        'tr_dapg',
        'date_tr_dapg',
        'has_antecedent',
        'antecedant_id',
        'date_etat_greffe',
        'date_envoi_greffe',
        'etat_greffe',
        'user_tribunal',
        'user_greffe',
        'user_parquet',
        'nbr_redirection',
        'originedossier',
        'observation_redirection',
        'has_file_mp',
        'date_etat_ok',
        'etat_parquet',
        'date_envoi_parquet',
        'date_etat_parquet',

    ];*/

    protected $fillable = [
        'numero_dapg',
        'numero',
        'numeromp',
        'etat',
        'tr_tribunal',
        'date_tr_tribunal',
        'tr_dapg',
        'date_tr_dapg',
        'date_enregistrement',
        'date_sortie',
        'observation',
        'has_antecedent',
        'antecedant_id',
        'avis_mp',
        'avis_dgapr',
        'avis_gouverneur',
        'user_tribunal_id',
        'user_tribunal_libelle',
        'numero_detention',
        'comportement_id',
        'objetdemande_id',
        'sourcedemande_id',
        'autre_source',
        'prison_id',
        'user_id',
        'typedossier_id',
        'detenu_id',
        'categoriedossiers_id',
        'naturedossiers_id',
        'typemotifdossiers_id',
        'date_etat_tribunal',
        'date_etat_greffe',
        'date_envoi_greffe',
        'etat_greffe',
        'user_tribunal',
        'user_greffe',
        'user_parquet',
        'categorie',
        'nbr_redirection',
        'originedossier',
        'date_envoi_parquet',
        'date_etat_parquet',
        'etat_parquet',
        'observations_parquet',
        'avis_id',
        'observation_redirection',
        'has_file_mp',
        'date_etat_ok',
        'etat_retour_dapg_tr',
        'redirecton_greffe_sglb_tr',
        'date_redirecton_greffe_sglb_tr',
        'nbr_redirecton_greffe_sglb_tr',
        'observation_redirecton_greffe_sglb_tr',

    ];
    const ANNEE_DEBUT_NUMEROMP = 2027;

    protected static function booted()
    {
        static::saving(function (Dossier $dossier) {
            if (!empty($dossier->numeromp)) {
                return;
            }

            if (empty($dossier->user_tribunal_id) || !in_array((int) $dossier->typedossier_id, [1, 2])) {
                return;
            }

            $annee = (int) now()->year;
            if ($annee < self::ANNEE_DEBUT_NUMEROMP) {
                return;
            }

            $tribunal = Tribunal::find($dossier->user_tribunal_id);
            if (!$tribunal || $tribunal->type_tribunal !== 'C') {
                return;
            }

            $numero = self::genererNumeroMp(
                $tribunal->id,
                (int) $dossier->typedossier_id,
                $annee
            );

            $libelle = TypeDossier::where('id', $dossier->typedossier_id)->value('libelle_mp')
                ?? $dossier->typedossier_id;

            $dossier->numeromp = $numero . ' ' . $libelle . ' ' . $annee;
            $dossier->annee_numeromp = $annee;
        });
    }

    public static function genererNumeroMp(int $tribunalId, int $typeDossierId, int $annee): int
    {
        return DB::transaction(function () use ($tribunalId, $typeDossierId, $annee) {
            $compteur = DB::table('compteurs_numeromp')
                ->where('tribunal_id', $tribunalId)
                ->where('typedossier_id', $typeDossierId)
                ->where('annee', $annee)
                ->lockForUpdate()
                ->first();

            if (!$compteur) {
                DB::table('compteurs_numeromp')->insert([
                    'tribunal_id'    => $tribunalId,
                    'typedossier_id' => $typeDossierId,
                    'annee'          => $annee,
                    'dernier_numero' => 1,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
                return 1;
            }

            $nouveau = $compteur->dernier_numero + 1;

            DB::table('compteurs_numeromp')
                ->where('id', $compteur->id)
                ->update([
                    'dernier_numero' => $nouveau,
                    'updated_at'     => now(),
                ]);

            return $nouveau;
        });
    }
    public function affaires()
    {

        return $this->belongsToMany(Affaire::class, 'dossier_affaire')->withTimestamps();
    }

    public function typedossier()
    {

        return $this->belongsTo(TypeDossier::class);
    }


    public function user()
    {

        return $this->belongsTo(User::class);
    }


    public function detenu()
    {

        return $this->belongsTo(Detenu::class);
    }
    public function garants()
    {

        return $this->belongsToMany(Garant::class, 'dossier_garant')->withTimestamps();
    }

    public function requettes()
    {

        return $this->hasMany(Requette::class);
    }
    public function categoriedossier()
    {

        return $this->belongsTo(CategorieDossier::class, 'categoriedossiers_id');
    }

    public function avis()
    {

        return $this->belongsTo(Avis::class, 'avis_id');
    }
    public function naturedossier()
    {

        return $this->belongsTo(NatureDossier::class, 'naturedossiers_id');
    }
    public function typemotifdossier()
    {

        return $this->belongsTo(TypeMotifDossier::class, 'typemotifdossiers_id');
    }

    public function objetdemande()
    {

        return $this->belongsTo(ObjetDemande::class, 'objetdemande_id');
    }


    public function sourcedemande()
    {

        return $this->belongsTo(SourceDemande::class, 'sourcedemande_id');
    }
    public function prison()
    {

        return $this->belongsTo(Prison::class, 'prison_id');
    }

    public function comportement()
    {

        return $this->belongsTo(Comportement::class, 'comportement_id');
    }
    public function pjs()
    {

        return $this->hasMany(Pj::class);
    }

    public function userParquetObjet() // Nommez-la ainsi pour correspondre à votre souhait Angular
    {
        return $this->belongsTo(User::class, 'user_parquet');
    }

    public function LibelleTribunalUtilisateur() // Nommez-la ainsi pour correspondre à votre souhait Angular
    {
        return $this->belongsTo(Tribunal::class, 'user_tribunal_id');
    }

    public function occasions()
    {
        return $this->belongsToMany(Occasion::class, 'dossier_occasion');
    }
    public function motifGreffe()
    {
        return $this->belongsTo(\App\Models\MotifGreffe::class, 'motif_greffe_id');
    }
}
