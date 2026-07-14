#!/bin/bash
# Script to install missing ResourceSpace libraries
# Run this script from the ResourceSpace root directory

echo "Installing missing ResourceSpace libraries..."
echo "=============================================="

cd lib || exit 1

# Install FontAwesome
echo ""
echo "1. Installing FontAwesome 6.4.0..."
if [ ! -d "fontawesome" ]; then
    wget -q https://use.fontawesome.com/releases/v6.4.0/fontawesome-free-6.4.0-web.zip -O /tmp/fontawesome.zip
    if [ $? -eq 0 ]; then
        unzip -q /tmp/fontawesome.zip -d /tmp/
        mv /tmp/fontawesome-free-6.4.0-web fontawesome
        rm /tmp/fontawesome.zip
        echo "   ✓ FontAwesome installed successfully"
    else
        echo "   ✗ Failed to download FontAwesome"
    fi
else
    echo "   ✓ FontAwesome already exists"
fi

# Install TinyMCE
echo ""
echo "2. Installing TinyMCE 6.7.0..."
if [ ! -d "tinymce" ]; then
    wget -q https://download.tiny.cloud/tinymce/community/tinymce_6.7.0.zip -O /tmp/tinymce.zip
    if [ $? -eq 0 ]; then
        unzip -q /tmp/tinymce.zip -d /tmp/
        mv /tmp/tinymce/js/tinymce tinymce
        rm -rf /tmp/tinymce /tmp/tinymce.zip
        echo "   ✓ TinyMCE installed successfully"
    else
        echo "   ✗ Failed to download TinyMCE"
    fi
else
    echo "   ✓ TinyMCE already exists"
fi

# Create leaflet_plugins directory
echo ""
echo "3. Creating leaflet_plugins directory structure..."
mkdir -p leaflet_plugins

echo ""
echo "=============================================="
echo "Installation Summary:"
echo "=============================================="
echo ""
echo "✓ FontAwesome: $([ -d 'fontawesome' ] && echo 'Installed' || echo 'Missing')"
echo "✓ TinyMCE: $([ -d 'tinymce' ] && echo 'Installed' || echo 'Missing')"
echo "✓ Leaflet Plugins directory: $([ -d 'leaflet_plugins' ] && echo 'Created' || echo 'Missing')"
echo ""
echo "NOTE: Leaflet plugins need to be downloaded from the complete"
echo "      ResourceSpace package as they are customized versions."
echo ""
echo "Next steps:"
echo "1. Download complete ResourceSpace from: https://www.resourcespace.com/get"
echo "2. Extract and copy the 'lib/leaflet_plugins' directory"
echo "3. Clear your browser cache (Ctrl+Shift+Delete)"
echo "4. Refresh the ResourceSpace page"
echo ""
