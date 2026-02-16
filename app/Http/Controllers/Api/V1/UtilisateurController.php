<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UtilisateurResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UtilisateurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with(['role', 'groupe', 'tribunal'])->get();

        return UtilisateurResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        Log::debug('Requête reçue **** users *** :', $request->all());


        // 1. Validation des données
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users',
            'username'  => 'required|string|max:255|unique:users',
            //'password'  => 'required|string|min:8',
            'role_id'   => 'required|exists:roles,id',
            'groupe_id' => 'required|exists:groupes,id',
            // Validation conditionnelle : obligatoire si le rôle est Tribunal (ID 2)
            'tribunal_id' => 'nullable|exists:tribunaux,id',
            'partenaire_id' => 'nullable|exists:partenaires,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2. Insertion avec valeurs forcées
        $user = User::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'username'       => $request->username,
            'role_id'        => $request->role_id,
            'groupe_id'      => $request->groupe_id,
            'tribunal_id'    => $request->tribunal_id,
            'partenaire_id'  => $request->partenaire_id,

            // --- VOS CONDITIONS SPÉCIFIQUES ---
            'password'             => Hash::make('password'), // Valeur fixe hachée
            'must_change_password' => true,                  // Forcé à true (bit 1 en SQL)
        ]);

        return response()->json([
            'message' => 'Utilisateur créé avec succès',
            'user'    => $user->load(['role', 'groupe']) // On renvoie l'user avec ses relations
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //

        $user = User::findOrFail($id);

        // 1. Validation
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            // On ignore l'ID actuel pour la règle unique
            'email'     => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'username'  => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role_id'   => 'required|exists:roles,id',
            'groupe_id' => 'required|exists:groupes,id',
            'tribunal_id' => 'nullable|exists:tribunaux,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2. Préparation des données
        $data = [
            'name'          => $request->name,
            'email'         => $request->email,
            'username'      => $request->username,
            'role_id'       => $request->role_id,
            'groupe_id'     => $request->groupe_id,
            'tribunal_id'   => $request->tribunal_id,
            'partenaire_id' => $request->partenaire_id,
        ];

        // 3. Gestion optionnelle du mot de passe
        // Si vous décidez d'envoyer un nouveau mot de passe depuis Angular
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
            $data['must_change_password'] = true;
        }

        // 4. Mise à jour dans SQL Server
        $user->update($data);

        return response()->json([
            'message' => 'Utilisateur mis à jour avec succès',
            'user'    => $user->load(['role', 'groupe', 'tribunal'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
