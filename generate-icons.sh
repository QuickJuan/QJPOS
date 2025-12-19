#!/bin/bash

# PWA Icon Generator Script
# This script helps generate PWA icons from a source image

echo "🎨 QuickJuan POS - PWA Icon Generator"
echo "======================================"
echo ""

# Check if source image is provided
if [ -z "$1" ]; then
    echo "❌ Error: No source image provided"
    echo ""
    echo "Usage: ./generate-icons.sh <path-to-your-logo.png>"
    echo "Example: ./generate-icons.sh logo.png"
    echo ""
    echo "Note: The source image should be at least 512x512 pixels"
    exit 1
fi

SOURCE_IMAGE="$1"

# Check if source image exists
if [ ! -f "$SOURCE_IMAGE" ]; then
    echo "❌ Error: Source image not found: $SOURCE_IMAGE"
    exit 1
fi

# Check if ImageMagick is installed
if ! command -v convert &> /dev/null; then
    echo "❌ Error: ImageMagick is not installed"
    echo ""
    echo "Install ImageMagick:"
    echo "  macOS: brew install imagemagick"
    echo "  Ubuntu: sudo apt-get install imagemagick"
    echo "  Windows: Download from https://imagemagick.org/script/download.php"
    echo ""
    echo "Or use an online tool:"
    echo "  - https://www.pwabuilder.com/imageGenerator"
    echo "  - https://realfavicongenerator.net/"
    exit 1
fi

# Create icons directory if it doesn't exist
ICONS_DIR="public/images/icons"
mkdir -p "$ICONS_DIR"

echo "📦 Creating icons directory: $ICONS_DIR"
echo ""

# Icon sizes
SIZES=(72 96 128 144 152 192 384 512)

echo "🔄 Generating icons..."
echo ""

# Generate each size
for size in "${SIZES[@]}"; do
    OUTPUT_FILE="$ICONS_DIR/icon-${size}x${size}.png"
    echo "  Creating ${size}x${size}..."

    convert "$SOURCE_IMAGE" \
        -resize "${size}x${size}" \
        -background none \
        -gravity center \
        -extent "${size}x${size}" \
        "$OUTPUT_FILE"

    if [ $? -eq 0 ]; then
        echo "  ✅ Created: $OUTPUT_FILE"
    else
        echo "  ❌ Failed to create: $OUTPUT_FILE"
    fi
done

echo ""
echo "🎉 Done! Icons generated in $ICONS_DIR"
echo ""
echo "📋 Next steps:"
echo "  1. Check the icons in $ICONS_DIR"
echo "  2. Test your PWA installation"
echo "  3. Deploy to production with HTTPS"
echo ""
