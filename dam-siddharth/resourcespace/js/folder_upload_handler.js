/**
 * Folder Upload Handler
 * Handles uploading complete folder structures with files
 * Integrates with Uppy upload system
 */

/**
 * Process folder structure for upload
 * Converts local folder structure into ResourceSpace collection hierarchy
 */
function processFolderUpload(folderData, parentCollectionId = 0) {
    // folderData.name = root folder name (e.g. "hack")
    // folderData.items = the root entries (the "hack" folder item itself)
    // We want to create "hack" as root and send its children as the structure

    const rootItem = (folderData.items && folderData.items.length === 1 && folderData.items[0].type === 'folder')
        ? folderData.items[0]
        : null;

    const rootName = rootItem ? rootItem.name : (folderData.name || 'Upload');
    const childrenToSend = rootItem ? rootItem.children : (folderData.items || []);

    const structure = flattenFolderStructure({ items: childrenToSend });
    
    return fetch(baseurl + '/pages/ajax/process_folder_upload.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'upload_folder_structure',
            parent_collection: parentCollectionId,
            name: rootName,
            structure: structure
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Folder structure created successfully');
            console.log(`Created ${data.folders_created} folders and ${data.files_created} file entries`);
            return data;
        } else {
            throw new Error(data.error || 'Failed to create folder structure');
        }
    });
}

/**
 * Flatten folder structure from the file upload
 * Convert nested folder hierarchy into structured data
 */
function flattenFolderStructure(folderData) {
    const structure = [];
    
    function traverse(items, parentArray) {
        if (!Array.isArray(items)) {
            return;
        }
        
        items.forEach(item => {
            if (item.isDir || item.type === 'folder') {
                // This is a folder
                const folderEntry = {
                    type: 'folder',
                    name: item.name || item.webkitRelativePath?.split('/')[0] || 'Folder',
                    children: []
                };
                
                // If this folder has children, process them
                if (item.children && Array.isArray(item.children)) {
                    traverse(item.children, folderEntry.children);
                }
                
                parentArray.push(folderEntry);
            } else if (item.isFile || item.type === 'file') {
                // This is a file
                parentArray.push({
                    type: 'file',
                    name: item.name || item.webkitRelativePath || 'file'
                });
            }
        });
    }
    
    traverse(folderData.items || [folderData], structure);
    return structure;
}

/**
 * Create file resource in a folder during metadata editing
 */
function createFileResourceInFolder(folderId, filename, filePath, resourceType = 1) {
    return fetch(baseurl + '/pages/ajax/process_folder_upload.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'create_file_resource',
            folder_id: folderId,
            filename: filename,
            file_path: filePath,
            resource_type: resourceType
        })
    })
    .then(response => response.json());
}

/**
 * Handle drop event for folder uploads
 */
function handleFolderDrop(event) {
    event.preventDefault();
    event.stopPropagation();
    
    const items = event.dataTransfer.items;
    
    if (!items) {
        return;
    }
    
    // Check if folders are being dropped
    let hasFolders = false;
    
    for (let i = 0; i < items.length; i++) {
        if (items[i].webkitGetAsEntry) {
            const entry = items[i].webkitGetAsEntry();
            if (entry && entry.isDirectory) {
                hasFolders = true;
                break;
            }
        }
    }
    
    if (hasFolders) {
        console.log('Folder upload detected');
        // Handle folder upload specially
        processFolderDropItems(items);
    }
}

/**
 * Process dropped folder items
 */
function processFolderDropItems(items) {
    // We'll build per-root-folder structures
    const rootEntries = [];
    
    for (let i = 0; i < items.length; i++) {
        if (items[i].webkitGetAsEntry) {
            const entry = items[i].webkitGetAsEntry();
            if (entry) {
                rootEntries.push(entry);
            }
        }
    }
    
    if (rootEntries.length === 0) return;
    
    // Process each root entry (typically just one folder)
    // We read the whole tree recursively before doing anything
    function readEntryTree(entry) {
        return new Promise((resolve) => {
            if (entry.isFile) {
                entry.file(function(file) {
                    resolve({
                        name: file.name,
                        type: 'file',
                        path: entry.fullPath,
                        file: file
                    });
                });
            } else if (entry.isDirectory) {
                const reader = entry.createReader();
                const node = {
                    name: entry.name,
                    type: 'folder',
                    children: []
                };
                // readEntries may return results in batches - keep reading until done
                function readBatch(resolve_outer) {
                    reader.readEntries(function(entries) {
                        if (entries.length === 0) {
                            resolve_outer(node);
                            return;
                        }
                        const promises = entries.map(e => readEntryTree(e));
                        Promise.all(promises).then(function(children) {
                            node.children.push(...children);
                            readBatch(resolve_outer); // read next batch
                        });
                    });
                }
                readBatch(resolve);
            }
        });
    }
    
    Promise.all(rootEntries.map(e => readEntryTree(e))).then(function(trees) {
        // trees is an array of root items (usually just one folder)
        // Build a single folderStructure whose items = the root entries
        const folderStructure = {
            // Use the first root folder name as the upload name
            name: (trees.length === 1 && trees[0].type === 'folder') ? trees[0].name : 'Upload',
            items: trees
        };
        handleProcessedFolderStructure(folderStructure);
    });
}

/**
 * Handle the processed folder structure
 */
function handleProcessedFolderStructure(folderStructure) {
    console.log('Folder structure processed:', folderStructure);
    console.log('Folder structure items:', JSON.stringify(folderStructure.items, (key, value) => {
        if (key === 'file') return '[File object]';
        return value;
    }, 2));
    
    // Determine the root folder item (typically items[0] is the dropped folder)
    const rootItem = (folderStructure.items && folderStructure.items.length === 1 && folderStructure.items[0].type === 'folder')
        ? folderStructure.items[0]
        : null;
    
    // Extract files from the root folder's children
    // Files will have relativePath relative to the root folder (not including root folder name)
    const itemsToExtract = rootItem ? rootItem.children : folderStructure.items;
    const files = extractFilesFromStructure({ items: itemsToExtract });
    
    console.log(`Total files to upload: ${files.length}`);
    console.log('Extracted files:', files.map(f => ({ name: f.name, relativePath: f.relativePath, path: f.path })));
    
    if (files.length === 0) {
        console.warn('No files found in folder structure - only folders will be created');
    }
    
    // Create the folder hierarchy in ResourceSpace
    processFolderUpload(folderStructure)
        .then(result => {
            console.log('Folder structure created');
            console.log('Structure map:', result.structure_map);
            
            // Now queue the files for upload with their target folder information
            if (files.length > 0 && window.uppyInstance) {
                console.log('Queuing files for upload...');
                
                files.forEach(file => {
                    let targetFolderId = result.root_folder_id;
                    
                    // Get the folder path from relativePath
                    // relativePath contains the folder path relative to root (not including root name or filename)
                    const folderPath = file.relativePath;
                    
                    if (folderPath && folderPath !== '') {
                        console.log(`File ${file.name} folder path:`, folderPath);
                        
                        // Look up the folder ID using the relative path
                        if (result.structure_map && result.structure_map[folderPath]) {
                            targetFolderId = result.structure_map[folderPath];
                            console.log(`Found target folder ID ${targetFolderId} for path: ${folderPath}`);
                        } else {
                            console.warn(`Could not find folder ID for path: ${folderPath}`);
                            // File will default to root folder
                        }
                    } else {
                        console.log(`File ${file.name} belongs to root folder ${result.root_folder_id}`);
                    }
                    
                    console.log(`Queuing file ${file.name} for target folder ${targetFolderId}`);
                    
                    // Add file to Uppy with folder context - this metadata will be sent to upload_batch.php
                    window.uppyInstance.addFile({
                        name: file.name,
                        type: file.file.type,
                        data: file.file,
                        meta: {
                            folder_path: file.path,
                            relative_path: file.relativePath,
                            target_folder_id: targetFolderId,
                            from_folder_upload: true
                        }
                    });
                });
                
                console.log(`Queued ${files.length} files for upload to their respective folders`);
            } else if (files.length === 0) {
                alert('No files found to upload. Folder structure created but empty.');
            }
        })
        .catch(error => {
            console.error('Failed to create folder structure:', error);
            alert('Error creating folder structure: ' + error.message);
        });
}

/**
 * Extract all files from nested folder structure
 */
function extractFilesFromStructure(structure, parentPath = '') {
    const files = [];
    
    function traverse(items, currentPath) {
        if (!Array.isArray(items)) {
            return;
        }
        
        items.forEach(item => {
            if (item.type === 'folder' && item.children) {
                const folderPath = currentPath ? currentPath + '/' + item.name : item.name;
                traverse(item.children, folderPath);
            } else if (item.type === 'file' && item.file) {
                files.push({
                    name: item.name,
                    file: item.file,
                    path: item.path || (currentPath ? currentPath + '/' + item.name : item.name),
                    // Store the folder path (not including the filename)
                    relativePath: currentPath || ''
                });
            }
        });
    }
    
    traverse(structure.items || [structure], parentPath);
    return files;
}

/**
 * Find target folder ID based on file path
 */
function findTargetFolderId(filePath, structureMap) {
    // Extract folder path from file path
    const pathParts = filePath.split('/');
    let currentId = null;
    
    for (let i = 0; i < pathParts.length - 1; i++) {
        const folderName = pathParts[i];
        if (structureMap[folderName]) {
            currentId = structureMap[folderName];
        }
    }
    
    return currentId;
}

/**
 * Get the folder context for a file being uploaded
 */
function getFileUploadContext(filename, uploadMeta) {
    return {
        filename: filename,
        target_folder: uploadMeta.target_folder_id,
        relative_path: uploadMeta.relative_path
    };
}

/**
 * Attach folder upload handlers to Uppy
 */
function attachFolderUploadHandlers(uppyInstance) {
    window.uppyInstance = uppyInstance;
    
    // Handle completed uploads with folder context
    uppyInstance.on('upload-success', (file, response) => {
        console.log('File uploaded:', file.name);
        
        // Store the upload context for later metadata editing
        if (file.meta && file.meta.target_folder_id) {
            console.log(`File ${file.name} should be placed in folder ${file.meta.target_folder_id}`);
        }
    });
    
    // Handle upload errors
    uppyInstance.on('upload-error', (file, error, response) => {
        console.error('Upload error for ' + file.name + ':', error);
    });
}

// Export for use in other scripts
window.FolderUploadHandler = {
    process: processFolderUpload,
    createFileResource: createFileResourceInFolder,
    handleDrop: handleFolderDrop,
    attachHandlers: attachFolderUploadHandlers,
    extractFiles: extractFilesFromStructure
};
