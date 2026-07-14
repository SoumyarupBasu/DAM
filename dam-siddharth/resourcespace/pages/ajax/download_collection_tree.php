<?php
/**
 * Download all resources from a collection tree as a ZIP file.
 * Recursively collects all resources from the folder and its subfolders.
 */
ini_set('zlib.output_compression', 'off');
include "../../include/boot.php";
include "../../include/authenticate.php";

ini_set('display_errors', 0);

$collection_ref = getval('collection_ref', 0, true);

if ($collection_ref <= 0) {
    exit('Invalid collection reference.');
}

// Get the folder name for the ZIP filename
$col_data = get_collection($collection_ref);
if (!$col_data) {
    exit('Collection not found.');
}

$folder_name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strip_prefix_chars($col_data['name'], '*'));
$folder_name = trim($folder_name, '_') ?: 'download';

/**
 * Recursively gather all resource file paths from a collection tree.
 * Returns array of ['path' => '/full/path/to/file', 'name' => 'filename_in_zip.ext']
 */
function gather_files_recursive(int $col_ref, string $zip_path = ''): array
{
    $files = [];

    // Get resources in this collection
    $resources = ps_query(
        "SELECT r.ref, r.file_extension, r.file_path
         FROM collection_resource cr
         INNER JOIN resource r ON r.ref = cr.resource
         WHERE cr.collection = ? AND r.archive != 3 AND r.file_extension != ''",
        ['i', $col_ref]
    );

    foreach ($resources as $res) {
        $filepath = get_resource_path((int)$res['ref'], true, '', false, $res['file_extension']);
        if ($filepath && file_exists($filepath)) {
            // Get resource title for filename
            global $view_title_field;
            $title = get_data_by_field((int)$res['ref'], $view_title_field);
            $title = trim(preg_replace('/[^a-zA-Z0-9_\-\. ]/', '_', $title));
            if ($title == '') {
                $title = 'resource_' . $res['ref'];
            }
            $zip_filename = $zip_path . $title . '.' . $res['file_extension'];
            $files[] = ['path' => $filepath, 'name' => $zip_filename];
        }
    }

    // Recurse into child collections
    $children = ps_query(
        "SELECT ref, name FROM collection WHERE parent = ?",
        ['i', $col_ref]
    );

    foreach ($children as $child) {
        $child_name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strip_prefix_chars($child['name'], '*'));
        $child_name = trim($child_name, '_') ?: 'folder_' . $child['ref'];
        $child_files = gather_files_recursive((int)$child['ref'], $zip_path . $child_name . '/');
        $files = array_merge($files, $child_files);
    }

    return $files;
}

$files = gather_files_recursive($collection_ref, $folder_name . '/');

if (empty($files)) {
    exit('No downloadable files found in this folder.');
}

// Create ZIP in memory
$zip_temp = tempnam(sys_get_temp_dir(), 'rs_dl_') . '.zip';
$zip = new ZipArchive();

if ($zip->open($zip_temp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    exit('Failed to create ZIP file.');
}

foreach ($files as $file) {
    $zip->addFile($file['path'], $file['name']);
}

$zip->close();

// Stream the ZIP to the browser
$zip_size = filesize($zip_temp);
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $folder_name . '.zip"');
header('Content-Length: ' . $zip_size);
header('Pragma: no-cache');
header('Expires: 0');

ob_end_clean();
readfile($zip_temp);
unlink($zip_temp);
exit;
