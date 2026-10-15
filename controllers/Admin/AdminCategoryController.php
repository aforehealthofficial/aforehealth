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
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $status = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';

        if (empty($name)) {
            set_flash('danger', 'Category name is required.');
            redirect('/admin/categories');
        }

        // Handle category image upload
        $image = 'cat_period_panties.svg';
        if (!empty($_FILES['image']['tmp_name'])) {
            $uploaded = upload_file($_FILES['image'], 'products');
            if ($uploaded) {
                $image = $uploaded;
            }
        } elseif (!empty($_POST['image_preset'])) {
            $image = trim($_POST['image_preset']);
        }

        $categoryModel = new Category();
        $categoryModel->create([
            'name' => $name,
            'slug' => slugify($name),
            'description' => $desc,
            'image' => $image,
            'sort_order' => $sortOrder,
            'status' => $status
        ]);

        set_flash('success', "Category '{$name}' created successfully with image.");
        redirect('/admin/categories');
    }

    public function update(): void {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $status = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';

        if (!$id || empty($name)) {
            set_flash('danger', 'Category ID and name are required.');
            redirect('/admin/categories');
        }

        $categoryModel = new Category();
        $existing = $categoryModel->find($id);
        if (!$existing) {
            set_flash('danger', 'Category not found.');
            redirect('/admin/categories');
        }

        $updateData = [
            'name' => $name,
            'slug' => slugify($name),
            'description' => $desc,
            'sort_order' => $sortOrder,
            'status' => $status
        ];

        // If new image uploaded, save and replace
        if (!empty($_FILES['image']['tmp_name'])) {
            $uploaded = upload_file($_FILES['image'], 'products');
            if ($uploaded) {
                $updateData['image'] = $uploaded;
            }
        }

        $categoryModel->update($id, $updateData);

        set_flash('success', "Category '{$name}' updated successfully.");
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
