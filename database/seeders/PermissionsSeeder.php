<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      // Reset cached roles and permissions
      app()['cache']->forget('spatie.permission.cache');
      $permissions = [
          [
            "name" => "view member",
            "description" => "View a Member or All Members",
            "guard_name" => "web"
          ],
          [
            "name" => "edit member",
            "description" => "Edit a Members",
            "guard_name" => "web"
          ],
          [
            "name" => "add member",
            "description" => "Add a Members",
            "guard_name" => "web"
          ],
          [
            "name" => "delete member",
            "description" => "Delete a Members",
            "guard_name" => "web"
          ],
          [
            "name" => "view program",
            "description" => "View a Program or All Programs",
            "guard_name" => "web"
          ],
          [
            "name" => "edit program",
            "description" => "Edit a Program",
            "guard_name" => "web"
          ],
          [
            "name" => "add program",
            "description" => "Add a Program",
            "guard_name" => "web"
          ],
          [
            "name" => "delete program",
            "description" => "Delete a Program",
            "guard_name" => "web"
          ],
          [
            "name" => "edit business_profile",
            "description" => "Edit Business Profile",
            "guard_name" => "web"
          ],
          [
            "name" => "add member_card",
            "description" => "Add, Edit or Remove a Card",
            "guard_name" => "web"
          ],
          [
            "name" => "view achievement",
            "description" => "View a Achievement or All Achievements",
            "guard_name" => "web"
          ],
          [
            "name" => "edit achievement",
            "description" => "Edit a Achievement",
            "guard_name" => "web"
          ],
          [
            "name" => "add achievement",
            "description" => "Add a Achievement",
            "guard_name" => "web"
          ],
          [
            "name" => "delete achievement",
            "description" => "Delete a Achievement",
            "guard_name" => "web"
          ],
          [
            "name" => "view contract",
            "description" => "View a Contract or All Contract",
            "guard_name" => "web"
          ],
          [
            "name" => "edit contract",
            "description" => "Edit a Contract",
            "guard_name" => "web"
          ],
          [
            "name" => "add contract",
            "description" => "Add a Contract",
            "guard_name" => "web"
          ],
          [
            "name" => "delete contract",
            "description" => "Delete a Contract",
            "guard_name" => "web"
          ],
          [
            "name" => "view role",
            "description" => "View a Role or All Roles",
            "guard_name" => "web"
          ],
          [
            "name" => "edit role",
            "description" => "Edit a Role",
            "guard_name" => "web"
          ],
          [
            "name" => "add role",
            "description" => "Add a Role",
            "guard_name" => "web"
          ],
          [
            "name" => "delete role",
            "description" => "Delete a Role",
            "guard_name" => "web"
          ]
        ];
        DB::beginTransaction();
        foreach($permissions as $permission){
          Permission::insert($permission);
        }
        DB::commit();  
      }
}
