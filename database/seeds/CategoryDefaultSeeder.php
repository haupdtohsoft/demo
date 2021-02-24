<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryDefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $type = config('category.type.menu');
        $categories = [
            ['id' => 1, 'owner_id' => 1, 'name' => 'Đề Thi Tuyển Dụng', 'type' => $type],
            ['id' => 2, 'owner_id' => 1, 'name' => 'Tiểu học - THCS - THPT', 'type' => $type],
            ['id' => 3, 'owner_id' => 1, 'name' => 'Đại học - Cao Đẳng', 'type' => $type],
            ['id' => 4, 'owner_id' => 1, 'name' => 'Ngoại Ngữ', 'type' => $type],
        ];
        \App\Entities\Category::insert($categories);
        $categories = [
            ['parent_id' => 1, 'owner_id' => 1, 'name' => 'Tuyển dụng Ngân hàng', 'type' => $type],
            ['parent_id' => 1, 'owner_id' => 1, 'name' => 'Tuyển dụng Công chức', 'type' => $type],
            ['parent_id' => 2, 'owner_id' => 1, 'name' => 'Lớp 1', 'type' => $type],
            ['parent_id' => 2, 'owner_id' => 1, 'name' => 'Lớp 2', 'type' => $type],
            ['parent_id' => 2, 'owner_id' => 1, 'name' => 'Lớp 3', 'type' => $type],
            ['parent_id' => 2, 'owner_id' => 1, 'name' => 'Lớp 4', 'type' => $type],
            ['parent_id' => 2, 'owner_id' => 1, 'name' => 'Lớp 5', 'type' => $type],
            ['parent_id' => 2, 'owner_id' => 1, 'name' => 'Lớp 6', 'type' => $type],
            ['parent_id' => 2, 'owner_id' => 1, 'name' => 'Lớp 7', 'type' => $type],
            ['parent_id' => 2, 'owner_id' => 1, 'name' => 'Lớp 8', 'type' => $type],
            ['parent_id' => 2, 'owner_id' => 1, 'name' => 'Lớp 9', 'type' => $type],
            ['parent_id' => 2, 'owner_id' => 1, 'name' => 'Lớp 10', 'type' => $type],
            ['parent_id' => 2, 'owner_id' => 1, 'name' => 'Lớp 11', 'type' => $type],
            ['parent_id' => 2, 'owner_id' => 1, 'name' => 'Lớp 12', 'type' => $type],
            ['parent_id' => 2, 'owner_id' => 1, 'name' => 'Thi THPT Quốc Gia', 'type' => $type],
            ['parent_id' => 3, 'owner_id' => 1, 'name' => 'Đại Học Công Nghệ Đồng Nai', 'type' => $type],
            ['parent_id' => 3, 'owner_id' => 1, 'name' => 'ĐH Kinh Tế Quốc Dân - NEU', 'type' => $type],
            ['parent_id' => 4, 'owner_id' => 1, 'name' => 'Tiếng Trung', 'type' => $type],
            ['parent_id' => 4, 'owner_id' => 1, 'name' => 'Tiếng Hàn Quốc', 'type' => $type],
            ['parent_id' => 4, 'owner_id' => 1, 'name' => 'Tiếng Nhật Bản', 'type' => $type],
            ['parent_id' => 4, 'owner_id' => 1, 'name' => 'Tiếng Anh', 'type' => $type],
        ];
        \App\Entities\Category::insert($categories);
    }
}
