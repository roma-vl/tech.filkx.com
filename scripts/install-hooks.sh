#!/bin/bash

# Script to install Git hooks

HOOKS_DIR="$(git rev-parse --show-toplevel)/.git/hooks"
SOURCE_DIR="$(git rev-parse --show-toplevel)/scripts/git-hooks"

echo "📦 Installing Git hooks..."

if [ ! -d "$SOURCE_DIR" ]; then
    echo "❌ Source hooks directory not found: $SOURCE_DIR"
    exit 1
fi

# Copy all hooks from source to .git/hooks
cp "$SOURCE_DIR"/* "$HOOKS_DIR"/

# Make them executable
chmod +x "$HOOKS_DIR"/*

echo "✅ Git hooks installed successfully!"
