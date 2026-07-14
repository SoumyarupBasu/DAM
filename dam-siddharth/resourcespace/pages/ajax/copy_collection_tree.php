<?php
include "../../include/boot.php";
include "../../include/authenticate.php";

// Suppress any PHP warnings/notices that would corrupt JSON output
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');

try {
    if (!checkperm('c')) {
        echo json_encode(['success' => false, 'error' => 'Permission denied']);
        exit;
    }

    $source_ref = (int) getval('source_ref', 0, true);
    $dest_ref   = (int) getval('dest_ref', 0, true);

    if ($source_ref <= 0 || $dest_ref <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid parameters source=' . $source_ref . ' dest=' . $dest_ref]);
        exit;
    }

    function rs_is_descendant(int $child, int $ancestor): bool
    {
        if ($child === 0) return false;
        $parent = (int) ps_value("SELECT parent AS value FROM collection WHERE ref = ?", ['i', $child], 0);
        if ($parent === 0) return false;
        if ($parent === $ancestor) return true;
        return rs_is_descendant($parent, $ancestor);
    }

    if ($dest_ref === $source_ref || rs_is_descendant($dest_ref, $source_ref)) {
        echo json_encode(['success' => false, 'error' => 'Cannot paste into itself or a descendant']);
        exit;
    }

    function rs_copy_tree(int $src, int $new_parent): int
    {
        global $userref;

        $row = ps_query("SELECT name, type, allow_changes, cant_delete FROM collection WHERE ref = ?", ['i', $src]);
        if (empty($row)) return 0;
        $row = $row[0];

        $base_name = trim(preg_replace('/\s*\(ID-\d+\)$/', '', $row['name']));

        $new_ref = create_collection(
            $userref,
            $base_name,
            (int) $row['allow_changes'],
            (int) $row['cant_delete'],
            0,
            false,
            ['parent' => $new_parent, 'type' => (int) $row['type']]
        );

        if (!$new_ref || $new_ref <= 0) return 0;

        $resources = ps_array("SELECT resource AS value FROM collection_resource WHERE collection = ?", ['i', $src]);
        foreach ($resources as $res_ref) {
            ps_query(
                "INSERT IGNORE INTO collection_resource (collection, resource, date_added) VALUES (?, ?, NOW())",
                ['i', $new_ref, 'i', (int) $res_ref]
            );
        }

        $children = ps_array("SELECT ref AS value FROM collection WHERE parent = ? ORDER BY ref", ['i', $src]);
        foreach ($children as $child_ref) {
            rs_copy_tree((int) $child_ref, $new_ref);
        }

        return $new_ref;
    }

    $new_ref = rs_copy_tree($source_ref, $dest_ref);
    clear_query_cache("featured_collections");

    if ($new_ref > 0) {
        echo json_encode(['success' => true, 'new_ref' => $new_ref]);
    } else {
        echo json_encode(['success' => false, 'error' => 'create_collection returned 0']);
    }

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

// Must be set after headers
header('Content-Type: application/json');
ini_set('display_errors', 0);

try {
    if (!checkperm('c')) {
        echo json_encode(['success' => false, 'error' => 'Permission denied']);
        exit;
    }

    $source_ref = (int) getval('source_ref', 0, true);
    $dest_ref   = (int) getval('dest_ref', 0, true);

    if ($source_ref <= 0 || $dest_ref <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid parameters source=' . $source_ref . ' dest=' . $dest_ref]);
        exit;
    }

    // Prevent copying into itself or its own descendants
    function rs_is_descendant(int $child, int $ancestor): bool
    {
        if ($child === 0) return false;
        $parent = (int) ps_value("SELECT parent AS value FROM collection WHERE ref = ?", ['i', $child], 0);
        if ($parent === 0) return false;
        if ($parent === $ancestor) return true;
        return rs_is_descendant($parent, $ancestor);
    }

    if ($dest_ref === $source_ref || rs_is_descendant($dest_ref, $source_ref)) {
        echo json_encode(['success' => false, 'error' => 'Cannot paste into itself or a descendant']);
        exit;
    }

    function rs_copy_tree(int $src, int $new_parent): int
    {
        global $userref;

        $row = ps_query("SELECT name, type, allow_changes, cant_delete FROM collection WHERE ref = ?", ['i', $src]);
        if (empty($row)) return 0;
        $row = $row[0];

        // Strip existing (ID-XXXX) suffix — create_collection will add a new one
        $base_name = trim(preg_replace('/\s*\(ID-\d+\)$/', '', $row['name']));

        $new_ref = create_collection(
            $userref,
            $base_name,
            (int) $row['allow_changes'],
            (int) $row['cant_delete'],
            0,
            false,
            ['parent' => $new_parent, 'type' => (int) $row['type']]
        );

        if (!$new_ref || $new_ref <= 0) return 0;

        // Copy linked resources
        $resources = ps_array("SELECT resource AS value FROM collection_resource WHERE collection = ?", ['i', $src]);
        foreach ($resources as $res_ref) {
            ps_query(
                "INSERT IGNORE INTO collection_resource (collection, resource, date_added) VALUES (?, ?, NOW())",
                ['i', $new_ref, 'i', (int) $res_ref]
            );
        }

        // Recurse into children
        $children = ps_array("SELECT ref AS value FROM collection WHERE parent = ? ORDER BY ref", ['i', $src]);
        foreach ($children as $child_ref) {
            rs_copy_tree((int) $child_ref, $new_ref);
        }

        return $new_ref;
    }

    $new_ref = rs_copy_tree($source_ref, $dest_ref);
    clear_query_cache("featured_collections");

    if ($new_ref > 0) {
        echo json_encode(['success' => true, 'new_ref' => $new_ref]);
    } else {
        echo json_encode(['success' => false, 'error' => 'create_collection returned 0 — check permissions or DB']);
    }

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
