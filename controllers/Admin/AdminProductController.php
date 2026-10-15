<?php
namespace Controllers\Admin;

use Models\Product;
use Models\Category;
use PDO;

class AdminProductController {
    public function index(): void {
        $pdo = getDbConnection();
        $catFilter = $_GET['category'] ?? 'all';
        $search = trim($_GET['q'] ?? '');

        $where = ["1=1"];
        $params = [];

        if ($catFilter !== 'all' && is_numeric($catFilter)) {
            $where[] = "p.category_id = ?";
            $params[] = (int)$catFilter;
        }

        if (!empty($search)) {
            $where[] = "(p.name LIKE ? OR p.sku LIKE ?)";
            $s = "%{$search}%";
            $params[] = $s;
            $params[] = $s;
        }

        $whereClause = implode(' AND ', $where);

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = in_array((int)($_GET['per_page'] ?? 20), [10, 20, 30, 50, 100]) ? (int)($_GET['per_page'] ?? 20) : 20;

        // Total count
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p WHERE {$whereClause}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name,
                   (SELECT COUNT(*) FROM product_variants WHERE product_id = p.id) as variant_count,
                   (SELECT COALESCE(SUM(quantity), 0) FROM order_items WHERE product_id = p.id) as units_sold,
                   (SELECT COALESCE(SUM(total), 0) FROM order_items WHERE product_id = p.id) as total_revenue,
                   (SELECT COUNT(*) FROM wishlists WHERE product_id = p.id) as wishlist_count,
                   (SELECT COUNT(*) FROM reviews WHERE product_id = p.id AND status = 'approved') as approved_reviews_count
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE {$whereClause}
            ORDER BY p.id DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        $categoryModel = new Category();
        $categories = $categoryModel->allActive();

        view('admin.products.index', [
            'pageTitle' => 'Catalog Products - AFOREHEALTH Admin',
            'products' => $products,
            'categories' => $categories,
            'currentCategory' => $catFilter,
            'search' => $search,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'total' => $total
        ]);
    }

    public function create(): void {
        $categoryModel = new Category();
        $categories = $categoryModel->allActive();

        view('admin.products.create', [
            'pageTitle' => 'Add New Product - AFOREHEALTH Admin',
            'categories' => $categories
        ]);
    }

    public function store(): void {
        $name = trim($_POST['name'] ?? '');
        $sku = strtoupper(trim($_POST['sku'] ?? ''));
        $categoryId = (int)($_POST['category_id'] ?? 1);
        $price = (float)($_POST['price'] ?? 0);
        $mrp = (float)($_POST['mrp'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $shortDesc = trim($_POST['short_description'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $material = trim($_POST['material'] ?? '');
        $care = trim($_POST['care_instructions'] ?? '');
        $hygiene = trim($_POST['hygiene_notice'] ?? '');

        if (empty($name) || empty($sku) || $price <= 0) {
            set_flash('danger', 'Please provide product name, unique SKU, and price.');
            redirect('/admin/products/create');
        }

        $slug = slugify($name);
        $discountPct = $mrp > $price ? round((($mrp - $price) / $mrp) * 100) : 0;

        // Primary Image upload
        $primaryImage = 'cat_period_panties.svg';
        if (!empty($_FILES['primary_image']['tmp_name'])) {
            $uploadedPrimary = upload_file($_FILES['primary_image'], 'products');
            if ($uploadedPrimary) {
                $primaryImage = $uploadedPrimary;
            }
        }

        $pdo = getDbConnection();

        // Check if SKU exists
        $checkSku = $pdo->prepare("SELECT id FROM products WHERE sku = ?");
        $checkSku->execute([$sku]);
        if ($checkSku->fetch()) {
            set_flash('danger', "SKU '{$sku}' already exists. Please choose a unique SKU.");
            redirect('/admin/products/create');
        }

        $stmt = $pdo->prepare("
            INSERT INTO products (
                name, slug, sku, category_id, price, mrp, discount_percent, stock,
                short_description, description, material, care_instructions, hygiene_notice,
                primary_image, status, is_featured, is_bestseller, is_new_arrival
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, 'published', ?, ?, ?
            )
        ");
        $stmt->execute([
            $name, $slug, $sku, $categoryId, $price, $mrp, $discountPct, $stock,
            $shortDesc, $desc, $material, $care, $hygiene,
            $primaryImage,
            !empty($_POST['is_featured']) ? 1 : 0,
            !empty($_POST['is_bestseller']) ? 1 : 0,
            !empty($_POST['is_new_arrival']) ? 1 : 0
        ]);

        $prodId = (int)$pdo->lastInsertId();

        // Additional Gallery Images
        if (!empty($_FILES['gallery_images']['name']) && is_array($_FILES['gallery_images']['name'])) {
            $galleryFiles = upload_multiple_files($_FILES['gallery_images'], 'products');
            $imgStmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, alt_text, sort_order) VALUES (?, ?, ?, ?)");
            foreach ($galleryFiles as $idx => $gImg) {
                $imgStmt->execute([$prodId, $gImg, $name, $idx + 1]);
            }
        }

        // Process Variants (Color & Size)
        $totalVariantStock = 0;
        $variantCount = 0;
        $stmtVar = $pdo->prepare("
            INSERT INTO product_variants (product_id, sku, color, color_code, size, price, mrp, stock, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
        ");

        // Case A: Structured variants submitted from the interactive variant matrix
        if (!empty($_POST['variants']) && is_array($_POST['variants'])) {
            foreach ($_POST['variants'] as $v) {
                $vColor = trim($v['color'] ?? 'Classic Black');
                $vColorCode = trim($v['color_code'] ?? '#1F1E24');
                $vSize = strtoupper(trim($v['size'] ?? 'M'));
                $vPrice = !empty($v['price']) ? (float)$v['price'] : $price;
                $vMrp = !empty($v['mrp']) ? (float)$v['mrp'] : $mrp;
                $vStock = isset($v['stock']) ? max(0, (int)$v['stock']) : 20;
                $vSku = !empty($v['sku']) ? strtoupper(trim($v['sku'])) : ($sku . '-' . strtoupper(substr(slugify($vColor), 0, 3)) . '-' . $vSize);

                if (!empty($vSize) || !empty($vColor)) {
                    try {
                        $stmtVar->execute([$prodId, $vSku, $vColor, $vColorCode, $vSize, $vPrice, $vMrp, $vStock]);
                        $totalVariantStock += $vStock;
                        $variantCount++;
                    } catch (\Throwable $e) {
                        // In case of duplicate variant sku, append unique id
                        $stmtVar->execute([$prodId, $vSku . '-' . mt_rand(10, 99), $vColor, $vColorCode, $vSize, $vPrice, $vMrp, $vStock]);
                        $totalVariantStock += $vStock;
                        $variantCount++;
                    }
                }
            }
        }

        // Case B: Fallback - if no explicit variants were submitted, check selected sizes and colors checkboxes
        if ($variantCount === 0) {
            $selectedSizes = !empty($_POST['selected_sizes']) && is_array($_POST['selected_sizes']) 
                ? $_POST['selected_sizes'] 
                : ['S', 'M', 'L', 'XL', 'XXL'];

            $selectedColors = [];
            if (!empty($_POST['color_names']) && is_array($_POST['color_names'])) {
                foreach ($_POST['color_names'] as $idx => $cName) {
                    $cName = trim($cName);
                    if (!empty($cName)) {
                        $selectedColors[] = [
                            'name' => $cName,
                            'code' => $_POST['color_codes'][$idx] ?? '#1F1E24'
                        ];
                    }
                }
            }
            if (empty($selectedColors)) {
                $selectedColors = [['name' => 'Classic Black', 'code' => '#1F1E24']];
            }

            $perVarStock = max(5, (int)round($stock / (count($selectedSizes) * count($selectedColors))));
            foreach ($selectedColors as $c) {
                $colSlug = strtoupper(substr(slugify($c['name']), 0, 3));
                foreach ($selectedSizes as $sz) {
                    $sz = strtoupper(trim($sz));
                    $varSku = $sku . '-' . $colSlug . '-' . $sz;
                    try {
                        $stmtVar->execute([$prodId, $varSku, $c['name'], $c['code'], $sz, $price, $mrp, $perVarStock]);
                    } catch (\Throwable $e) {
                        $stmtVar->execute([$prodId, $varSku . '-' . mt_rand(10, 99), $c['name'], $c['code'], $sz, $price, $mrp, $perVarStock]);
                    }
                    $totalVariantStock += $perVarStock;
                }
            }
        }

        // Update product master stock to match total variant stock if variants were created
        if ($totalVariantStock > 0) {
            $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?")->execute([$totalVariantStock, $prodId]);
        }

        set_flash('success', "Product '{$name}' created with primary image, gallery and variants.");
        redirect('/admin/products');
    }

    public function edit(int $id): void {
        $productModel = new Product();
        $product = $productModel->find($id);

        if (!$product) {
            set_flash('danger', 'Product not found.');
            redirect('/admin/products');
        }

        $categoryModel = new Category();
        $categories = $categoryModel->allActive();
        $variants = $productModel->getVariants($id);
        $images = $productModel->getImages($id);

        view('admin.products.edit', [
            'pageTitle' => 'Edit Product #' . $id . ' - AFOREHEALTH Admin',
            'product' => $product,
            'categories' => $categories,
            'variants' => $variants,
            'images' => $images
        ]);
    }

    public function update(int $id): void {
        $name = trim($_POST['name'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $mrp = (float)($_POST['mrp'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $discountPct = $mrp > $price ? round((($mrp - $price) / $mrp) * 100) : 0;

        $pdo = getDbConnection();

        // 1. Primary image replacement if uploaded
        $primaryImgSql = "";
        $primaryImgParams = [];
        if (!empty($_FILES['primary_image']['tmp_name'])) {
            $uploadedPrimary = upload_file($_FILES['primary_image'], 'products');
            if ($uploadedPrimary) {
                $primaryImgSql = ", primary_image = ?";
                $primaryImgParams[] = $uploadedPrimary;
            }
        }

        // 2. Update basic product information
        $updateSql = "
            UPDATE products SET
                name = ?, category_id = ?, price = ?, mrp = ?, discount_percent = ?, stock = ?,
                short_description = ?, description = ?, material = ?, care_instructions = ?, hygiene_notice = ?,
                status = ?, is_featured = ?, is_bestseller = ?, is_new_arrival = ?
                {$primaryImgSql}
            WHERE id = ?
        ";
        $updateParams = array_merge([
            $name,
            (int)$_POST['category_id'],
            $price,
            $mrp,
            $discountPct,
            $stock,
            trim($_POST['short_description'] ?? ''),
            trim($_POST['description'] ?? ''),
            trim($_POST['material'] ?? ''),
            trim($_POST['care_instructions'] ?? ''),
            trim($_POST['hygiene_notice'] ?? ''),
            $_POST['status'] ?? 'published',
            !empty($_POST['is_featured']) ? 1 : 0,
            !empty($_POST['is_bestseller']) ? 1 : 0,
            !empty($_POST['is_new_arrival']) ? 1 : 0,
        ], $primaryImgParams, [$id]);

        $stmt = $pdo->prepare($updateSql);
        $stmt->execute($updateParams);

        // 3. Additional Gallery Images Upload
        if (!empty($_FILES['gallery_images']['name']) && is_array($_FILES['gallery_images']['name'])) {
            $newGalleryFiles = upload_multiple_files($_FILES['gallery_images'], 'products');
            $imgStmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, alt_text, sort_order) VALUES (?, ?, ?, 0)");
            foreach ($newGalleryFiles as $gImg) {
                $imgStmt->execute([$id, $gImg, $name]);
            }
        }

        // 4. Delete removed gallery images
        if (!empty($_POST['delete_images']) && is_array($_POST['delete_images'])) {
            $delStmt = $pdo->prepare("DELETE FROM product_images WHERE id = ? AND product_id = ?");
            foreach ($_POST['delete_images'] as $delImgId) {
                $delStmt->execute([(int)$delImgId, $id]);
            }
        }

        // 5. Update Existing Variants
        if (!empty($_POST['existing_variants']) && is_array($_POST['existing_variants'])) {
            $updVarStmt = $pdo->prepare("
                UPDATE product_variants SET
                    sku = ?, color = ?, color_code = ?, size = ?, price = ?, mrp = ?, stock = ?, status = ?
                WHERE id = ? AND product_id = ?
            ");
            foreach ($_POST['existing_variants'] as $vId => $vData) {
                $updVarStmt->execute([
                    strtoupper(trim($vData['sku'] ?? '')),
                    trim($vData['color'] ?? ''),
                    trim($vData['color_code'] ?? '#1F1E24'),
                    strtoupper(trim($vData['size'] ?? '')),
                    (float)($vData['price'] ?? $price),
                    (float)($vData['mrp'] ?? $mrp),
                    max(0, (int)($vData['stock'] ?? 0)),
                    $vData['status'] ?? 'active',
                    (int)$vId,
                    $id
                ]);
            }
        }

        // 6. Delete variants marked for deletion
        if (!empty($_POST['delete_variants']) && is_array($_POST['delete_variants'])) {
            $delVarStmt = $pdo->prepare("DELETE FROM product_variants WHERE id = ? AND product_id = ?");
            foreach ($_POST['delete_variants'] as $delVId) {
                $delVarStmt->execute([(int)$delVId, $id]);
            }
        }

        // 7. Add newly added variants in edit mode
        if (!empty($_POST['new_variants']) && is_array($_POST['new_variants'])) {
            $insVarStmt = $pdo->prepare("
                INSERT INTO product_variants (product_id, sku, color, color_code, size, price, mrp, stock, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
            ");
            foreach ($_POST['new_variants'] as $newV) {
                $nSize = strtoupper(trim($newV['size'] ?? ''));
                $nColor = trim($newV['color'] ?? 'Classic Black');
                if (!empty($nSize) || !empty($nColor)) {
                    $nSku = !empty($newV['sku']) ? strtoupper(trim($newV['sku'])) : ('AFH-' . $id . '-' . strtoupper(substr(slugify($nColor), 0, 3)) . '-' . $nSize . '-' . mt_rand(10, 99));
                    $nPrice = !empty($newV['price']) ? (float)$newV['price'] : $price;
                    $nMrp = !empty($newV['mrp']) ? (float)$newV['mrp'] : $mrp;
                    $nStock = isset($newV['stock']) ? max(0, (int)$newV['stock']) : 20;
                    $nColorCode = trim($newV['color_code'] ?? '#1F1E24');
                    try {
                        $insVarStmt->execute([$id, $nSku, $nColor, $nColorCode, $nSize, $nPrice, $nMrp, $nStock]);
                    } catch (\Throwable $e) {
                        $insVarStmt->execute([$id, $nSku . '-' . mt_rand(10, 99), $nColor, $nColorCode, $nSize, $nPrice, $nMrp, $nStock]);
                    }
                }
            }
        }

        // 8. Re-sync master stock with active variants sum
        $totStock = (int)$pdo->query("SELECT COALESCE(SUM(stock), 0) FROM product_variants WHERE product_id = {$id} AND status = 'active'")->fetchColumn();
        if ($totStock > 0) {
            $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?")->execute([$totStock, $id]);
        }

        set_flash('success', 'Product, images and variants updated successfully.');
        redirect('/admin/products');
    }

    public function duplicate(int $id): void {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $orig = $stmt->fetch();

        if ($orig) {
            $newName = $orig['name'] . ' (Copy)';
            $newSku = $orig['sku'] . '-COPY-' . mt_rand(10, 99);
            $newSlug = slugify($newName) . '-' . mt_rand(100, 999);

            $insStmt = $pdo->prepare("
                INSERT INTO products (
                    name, slug, sku, category_id, price, mrp, discount_percent, stock,
                    short_description, description, material, care_instructions, hygiene_notice,
                    primary_image, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft')
            ");
            $insStmt->execute([
                $newName, $newSlug, $newSku, $orig['category_id'], $orig['price'], $orig['mrp'], $orig['discount_percent'], $orig['stock'],
                $orig['short_description'], $orig['description'], $orig['material'], $orig['care_instructions'], $orig['hygiene_notice'],
                $orig['primary_image']
            ]);

            $newProdId = (int)$pdo->lastInsertId();

            // Duplicate variants
            $varStmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ?");
            $varStmt->execute([$id]);
            $origVars = $varStmt->fetchAll();
            $insVar = $pdo->prepare("
                INSERT INTO product_variants (product_id, sku, color, color_code, size, price, mrp, stock, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            foreach ($origVars as $ov) {
                $insVar->execute([
                    $newProdId,
                    $ov['sku'] . '-C-' . mt_rand(10, 99),
                    $ov['color'],
                    $ov['color_code'],
                    $ov['size'],
                    $ov['price'],
                    $ov['mrp'],
                    $ov['stock'],
                    $ov['status']
                ]);
            }

            set_flash('success', 'Product duplicated as draft with variants.');
        }

        redirect('/admin/products');
    }

    public function delete(): void {
        $id = (int)($_POST['id'] ?? 0);
        $productModel = new Product();
        $productModel->delete($id);
        set_flash('success', 'Product deleted.');
        redirect('/admin/products');
    }
}
