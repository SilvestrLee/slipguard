#!/usr/bin/env bash

set -Eeuo pipefail

# ============================================================
# SlipGuard Transparent Brand Asset Upload Script
# ============================================================
#
# Purpose:
#   Extract the approved transparent SlipGuard brand package,
#   validate every required file, copy the files into
#   public/brand, verify integrity and stage only the uploaded
#   brand assets.
#
# Usage:
#
#   ./scripts/maintenance/upload-slipguard-brand-assets.sh \
#     "/Users/silvestr/Downloads/slipguard-site-assets-transparent.zip"
#
# Optional explicit repository path:
#
#   ./scripts/maintenance/upload-slipguard-brand-assets.sh \
#     "/Users/silvestr/Downloads/slipguard-site-assets-transparent.zip" \
#     "/Users/silvestr/slipguard"
#
# ============================================================

ZIP_PATH="${1:-}"
REPO_PATH="${2:-$(pwd)}"
TARGET_DIR="${REPO_PATH}/public/brand"

REQUIRED_FILES=(
    "slipguard-logo-light-transparent.svg"
    "slipguard-logo-light-transparent.png"
    "slipguard-logo-dark-transparent.svg"
    "slipguard-logo-dark-transparent.png"
    "slipguard-icon-light-transparent.svg"
    "slipguard-icon-light-transparent.png"
    "slipguard-icon-dark-transparent.svg"
    "slipguard-icon-dark-transparent.png"
    "README.md"
)

print_heading() {
    echo ""
    echo "============================================================"
    echo "$1"
    echo "============================================================"
    echo ""
}

fail() {
    echo ""
    echo "ERROR: $1"
    echo ""
    exit 1
}

cleanup() {
    if [[ -n "${TEMP_DIR:-}" && -d "${TEMP_DIR}" ]]; then
        rm -rf "${TEMP_DIR}"
    fi
}

trap cleanup EXIT

print_heading "SlipGuard Transparent Brand Asset Upload"

if [[ -z "${ZIP_PATH}" ]]; then
    fail "No ZIP package path was supplied.

Usage:
  ./scripts/maintenance/upload-slipguard-brand-assets.sh \"/path/to/slipguard-site-assets-transparent.zip\""
fi

if [[ ! -f "${ZIP_PATH}" ]]; then
    fail "The brand package was not found:

  ${ZIP_PATH}"
fi

if [[ ! -d "${REPO_PATH}" ]]; then
    fail "The repository path does not exist:

  ${REPO_PATH}"
fi

if [[ ! -f "${REPO_PATH}/artisan" ]]; then
    fail "The target path does not appear to be a Laravel repository.

Missing:
  ${REPO_PATH}/artisan"
fi

if [[ ! -d "${REPO_PATH}/.git" ]]; then
    fail "The target path is not a Git repository:

  ${REPO_PATH}"
fi

if ! command -v unzip >/dev/null 2>&1; then
    fail "The unzip command is not available."
fi

if ! command -v shasum >/dev/null 2>&1; then
    fail "The shasum command is not available."
fi

TEMP_DIR="$(mktemp -d)"

echo "Package:"
echo "  ${ZIP_PATH}"
echo ""
echo "Repository:"
echo "  ${REPO_PATH}"
echo ""
echo "Destination:"
echo "  ${TARGET_DIR}"
echo ""

print_heading "Extracting Package"

unzip -q "${ZIP_PATH}" -d "${TEMP_DIR}"

echo "Package extracted successfully."

print_heading "Validating Required Files"

# Bash 3.2 (macOS system default, no associative arrays) — look up each
# file's extracted path on demand via find() instead of a hash map.
find_source_path() {
    find "${TEMP_DIR}" -type f -name "$1" -print -quit
}

for FILE in "${REQUIRED_FILES[@]}"; do
    FOUND_PATH="$(find_source_path "${FILE}")"

    if [[ -z "${FOUND_PATH}" ]]; then
        fail "Required file missing from package:

  ${FILE}"
    fi

    if [[ ! -s "${FOUND_PATH}" ]]; then
        fail "Required file is empty:

  ${FILE}"
    fi

    echo "FOUND: ${FILE}"
done

echo ""
echo "All approved files are present."

print_heading "Checking SVG Transparency References"

SVG_FILES=(
    "slipguard-logo-light-transparent.svg"
    "slipguard-logo-dark-transparent.svg"
    "slipguard-icon-light-transparent.svg"
    "slipguard-icon-dark-transparent.svg"
)

for FILE in "${SVG_FILES[@]}"; do
    SVG_PATH="$(find_source_path "${FILE}")"

    if ! grep -q "<svg" "${SVG_PATH}"; then
        fail "File does not contain valid SVG markup:

  ${FILE}"
    fi

    if ! grep -Eq "image|path|use" "${SVG_PATH}"; then
        fail "SVG does not appear to contain renderable artwork:

  ${FILE}"
    fi

    echo "VALID SVG: ${FILE}"
done

print_heading "Preparing Destination"

mkdir -p "${TARGET_DIR}"

BACKUP_DIR=""

for FILE in "${REQUIRED_FILES[@]}"; do
    EXISTING_FILE="${TARGET_DIR}/${FILE}"

    if [[ -f "${EXISTING_FILE}" ]]; then
        if [[ -z "${BACKUP_DIR}" ]]; then
            BACKUP_DIR="${REPO_PATH}/storage/app/brand-backups/$(date +%Y%m%d-%H%M%S)"
            mkdir -p "${BACKUP_DIR}"
        fi

        cp "${EXISTING_FILE}" "${BACKUP_DIR}/${FILE}"
    fi
done

if [[ -n "${BACKUP_DIR}" ]]; then
    echo "Existing matching assets were backed up to:"
    echo "  ${BACKUP_DIR}"
else
    echo "No matching existing assets required backup."
fi

print_heading "Copying Approved Assets"

for FILE in "${REQUIRED_FILES[@]}"; do
    SOURCE_PATH="$(find_source_path "${FILE}")"
    DESTINATION_PATH="${TARGET_DIR}/${FILE}"

    cp "${SOURCE_PATH}" "${DESTINATION_PATH}"

    if [[ ! -s "${DESTINATION_PATH}" ]]; then
        fail "Copied file is missing or empty:

  ${DESTINATION_PATH}"
    fi

    echo "COPIED: public/brand/${FILE}"
done

print_heading "Verifying Copied Files"

for FILE in "${REQUIRED_FILES[@]}"; do
    DESTINATION_PATH="${TARGET_DIR}/${FILE}"

    SOURCE_HASH="$(shasum -a 256 "$(find_source_path "${FILE}")" | awk '{print $1}')"
    DESTINATION_HASH="$(shasum -a 256 "${DESTINATION_PATH}" | awk '{print $1}')"

    if [[ "${SOURCE_HASH}" != "${DESTINATION_HASH}" ]]; then
        fail "Integrity verification failed for:

  ${FILE}"
    fi

    echo "VERIFIED: ${FILE}"
done

print_heading "Installed File Sizes"

for FILE in "${REQUIRED_FILES[@]}"; do
    FILE_SIZE="$(stat -f%z "${TARGET_DIR}/${FILE}")"
    printf "%-48s %12s bytes\n" "${FILE}" "${FILE_SIZE}"
done

print_heading "SHA-256 Checksums"

for FILE in "${REQUIRED_FILES[@]}"; do
    shasum -a 256 "${TARGET_DIR}/${FILE}"
done

print_heading "Checking Repository Diff"

cd "${REPO_PATH}"

git diff --check -- public/brand

echo "No whitespace errors found in public/brand."

print_heading "Staging Brand Assets"

git add public/brand

echo "Staged brand files:"
echo ""

git diff --cached --name-status -- public/brand

print_heading "Upload Complete"

echo "Approved transparent SlipGuard assets are now installed in:"
echo ""
echo "  public/brand"
echo ""
echo "The brand assets have been staged."
echo ""
echo "No commit has been created."
echo ""
echo "Next steps:"
echo ""
echo "  1. Review the staged files."
echo "  2. Integrate the assets into existing layouts."
echo "  3. Validate light and dark theme placement."
echo "  4. Commit brand assets separately from UI integration."
echo ""
