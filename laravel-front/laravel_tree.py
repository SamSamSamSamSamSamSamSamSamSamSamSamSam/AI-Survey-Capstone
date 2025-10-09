import os

# Folders to ignore (auto-generated or dependencies)
IGNORE = {
    'vendor', 'node_modules', 'storage', 'bootstrap', '.git', '__pycache__',
    '.idea', '.vscode', 'public/build', 'public/storage'
}

# Files to ignore
IGNORE_FILES = {
    '.env', '.env.example', 'package-lock.json', 'yarn.lock', 'composer.lock',
    'phpunit.xml', '.gitignore'
}

# Important Laravel directories (will be included even if empty)
IMPORTANT_DIRS = {
    'app', 'routes', 'resources', 'config', 'database', 'public', 'tests'
}

def print_tree(start_path, prefix=""):
    try:
        items = [i for i in os.listdir(start_path) if i not in IGNORE]
    except PermissionError:
        return

    items = sorted(items)
    for index, item in enumerate(items):
        path = os.path.join(start_path, item)

        # Skip ignored files
        if os.path.isfile(path) and item in IGNORE_FILES:
            continue

        # Only show important directories + files at root
        if start_path == ".":
            if os.path.isdir(path) and item not in IMPORTANT_DIRS:
                continue
            if os.path.isfile(path) and not item.endswith(('.php', '.json', '.js', '.md')):
                continue

        connector = "├── " if index < len(items) - 1 else "└── "
        print(prefix + connector + item)

        if os.path.isdir(path):
            extension = "│   " if index < len(items) - 1 else "    "
            print_tree(path, prefix + extension)

if __name__ == "__main__":
    root = "."
    print(f"Important Laravel Project Structure for: {os.path.abspath(root)}\n")
    print_tree(root)
