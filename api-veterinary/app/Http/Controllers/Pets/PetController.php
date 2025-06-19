<?php

namespace App\Http\Controllers\Pets;

use App\Http\Controllers\Controller;
use App\Http\Resources\Pets\PetCollection;
use App\Http\Resources\Pets\PetResource;
use App\Models\Pets\Owner;
use App\Models\Pets\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get("search");
        $species = $request->get("species");
        // where(DB::raw("pets.name"), "ilike", "%" . $search . "%")
        //     ->
        $pets = Pet::where(function ($query) use ($search, $species) {
            if ($species) {
                $query->where("specie", $species);
            }
            if ($search) {
                $query->whereHas("owner", function ($query) use ($search) {
                    $query->where(DB::raw("pets.name || ' ' || owners.first_name || ' ' || COALESCE(owners.last_name,'')|| ' ' || owners.phone || ' ' || owners.n_document "), "ilike", "%" . $search . "%");
                });
            }
        })
            ->orderBy("id", "asc")->paginate(5);
        return response()->json([
            "total_page" => $pets->lastPage(),
            "pets" => PetCollection::make($pets),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->hasFile("imagen")) {
            $path = $request->file("imagen")->store("pets", "public");
            $request->request->add(["photo" => $path]);
        }

        if ($request->dirth_date) {
            $request->request->add(["dirth_date" => $request->dirth_date . " 00:00:00"]);
        }


        $owner = Owner::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'emergency_contact' => $request->emergency_contact,
            'type_document' => $request->type_document,
            'n_document' => $request->n_document
        ]);
        $request->request->add([
            "owner_id" => $owner->id
        ]);
        $pet = Pet::create($request->all());
        return response()->json([
            "message" => 200,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pet = Pet::findOrFail($id);
        return response()->json([
            "pet" => PetResource::make($pet),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $pet = Pet::findOrFail($id);

        if ($request->hasFile("imagen")) {
            if ($pet->photo) {
                Storage::delete($pet->photo);
            }
            $path = Storage::putFile("pets", $request->file("imagen"));
            $request->request->add(["photo" => $path]);
        }
        if ($request->dirth_date) {
            $request->request->add(["dirth_date" => $request->dirth_date . " 00:00:00"]);
        }

        $pet->update($request->all());
        $owner = $pet->owner;
        $owner->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'emergency_contact' => $request->emergency_contact,
            'type_document' => $request->type_document,
            'n_document' => $request->n_document
        ]);
        return response()->json([
            "message" => 200,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pet = Pet::findOrFail($id);
        if ($pet->photo) {
            Storage::disk('public')->delete($pet->photo);
        }
        $pet->delete();
    }
}
