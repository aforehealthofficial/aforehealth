<?php
namespace Controllers\Admin;

use Models\Category;
use PDO;

class AdminCategoryController {
    public function index(): void {
        $categoryModel = new Category();
        $categories = $categoryModel->allActive();

        view('admin.categories.index', [
            'pageTitle' => 'Category Management - AFOREHEALTH Admin',
            'categories' => $categories
        ]);
    }

    public function store(): void {
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');

        if (empty($name)) {
            set_flash('danger', 'Category name is required.');
            redirect('/admin/categories');
        }

        $categoryModel = new Category();
        $categoryModel->create([
            'name' => $name,
            'slug' => slugify($name),
            'description' => $desc,
            'image' => 'cat_period_panties.svg',
            'status' => 'active'
        ]);

        set_flash('success', "Category '{$name}' created.");
        redirect('/admin/categories');
    }

    public function delete(): void {
        $id = (int)($_POST['id'] ?? 0);
        $categoryModel = new Category();
        $categoryModel->delete($id);
        set_flash('success', 'Category deleted.');
        redirect('/admin/categories');
    }
}
