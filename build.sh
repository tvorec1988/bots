#!/bin/bash
#
# Build script for Premium Companies Plugin
# Creates a distributable ZIP package
#

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Plugin information
PLUGIN_NAME="plg_content_premiumcompanies"
VERSION="1.0.0"
SOURCE_DIR="plg_djclassifieds_premiumcompanies"
DIST_DIR="dist"
PACKAGE_NAME="${PLUGIN_NAME}_v${VERSION}.zip"

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}  Premium Companies Plugin Build Script${NC}"
echo -e "${GREEN}============================================${NC}"
echo ""

# Check if source directory exists
if [ ! -d "$SOURCE_DIR" ]; then
    echo -e "${RED}Error: Source directory '$SOURCE_DIR' not found!${NC}"
    exit 1
fi

# Create dist directory if it doesn't exist
if [ ! -d "$DIST_DIR" ]; then
    echo -e "${YELLOW}Creating dist directory...${NC}"
    mkdir -p "$DIST_DIR"
fi

# Remove old package if exists
if [ -f "$DIST_DIR/$PACKAGE_NAME" ]; then
    echo -e "${YELLOW}Removing old package...${NC}"
    rm "$DIST_DIR/$PACKAGE_NAME"
fi

# Create the package
echo -e "${YELLOW}Creating package: ${PACKAGE_NAME}${NC}"
cd "$SOURCE_DIR" || exit 1

zip -r "../$DIST_DIR/$PACKAGE_NAME" . \
    -x "*.git*" \
    -x "*.DS_Store" \
    -x "*.idea*" \
    -x "*node_modules*" \
    -x "*.vscode*" \
    -x "*__MACOSX*" \
    -x "*.log" \
    -x "*~" \
    > /dev/null 2>&1

cd ..

# Check if package was created successfully
if [ -f "$DIST_DIR/$PACKAGE_NAME" ]; then
    SIZE=$(du -h "$DIST_DIR/$PACKAGE_NAME" | cut -f1)
    echo -e "${GREEN}✓ Package created successfully!${NC}"
    echo -e "  File: $DIST_DIR/$PACKAGE_NAME"
    echo -e "  Size: $SIZE"
    echo ""
    echo -e "${GREEN}Package contents:${NC}"
    unzip -l "$DIST_DIR/$PACKAGE_NAME" | head -20
    echo ""
    echo -e "${GREEN}Build completed successfully!${NC}"
    echo -e "${YELLOW}Upload ${DIST_DIR}/${PACKAGE_NAME} to your Joomla site.${NC}"
else
    echo -e "${RED}✗ Error: Package creation failed!${NC}"
    exit 1
fi
