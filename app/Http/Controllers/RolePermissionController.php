<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get("search");
        $roles= Role::where("name","like","%".$search."%")->orderBy("id","desc")->paginate(25);
    
        return response()->json([
            "total"=> $roles->total(),
            "roles" => $roles->map(function($rol){
                $rol ->permission_pluck = $rol-> permission -> pluck("name");
                $rol -> create_at = $rol->create_at->format("Y-m-d h:i A");


                return $rol;
            }),
        ]);
    }


    public function store(Request $request)
    {
        $IS_ROLE = Role::where("name", $request->name)->first();

        if($IS_ROLE){
           return response()->json([
               "message" => 403,
               "message_text"=> "El rol ya existe"
           ]);
        }

        $role = Role::create([
            'guard_name' => 'api',
            'name' => $request-> name
        ]);

        foreach ($request -> permissions as $key => $permission) {
            $role -> givePermissionTo($permission);
        }

        return response()->json([
           "message" => 200,
           "role" => [
               "id"=> $role->id,
               "permission" => $role-> permissions,
                "permission_pluck" => $role-> permission -> pluck("name"),
                "create_at" => $role->create_at->format("Y-m-d h:i A"),
                "name" => $role->name,
           ] 
           ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $IS_ROLE = Role::where("name", $request->name)->where("id","<>", $id)->first();

        if($IS_ROLE){
           return response()->json([
               "message" => 403,
               "message_text" => "Ya existe un rol con este nombre"
            ]);
        }

        $role = Role::findOrFail($id);

        $role -> update($request->all());

        $role -> syncPermission($request-> permissions);

        return response()->json([
           "message" => 200,
           "role" => [
               "id"=> $role->id,
               "permission" => $role-> permissions,
                "permission_pluck" => $role-> permission -> pluck("name"),
                "create_at" => $role->create_at->format("Y-m-d h:i A"),
                "name" => $role->name,
           ] 
           ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);
        $role -> delete();

        return response()->json([
            "message" => 200,
        ]);
    }
}
