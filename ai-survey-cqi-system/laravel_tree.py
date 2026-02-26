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

# Added 'file=None' parameter to redirect output
def print_tree(start_path, prefix="", file=None):
    try:
        # List items, ignoring specified folders
        items = [i for i in os.listdir(start_path) if i not in IGNORE]
    except PermissionError:
        return

    items = sorted(items)
    for index, item in enumerate(items):
        path = os.path.join(start_path, item)

        # Skip ignored files
        if os.path.isfile(path) and item in IGNORE_FILES:
            continue

        # Root directory filtering logic
        if start_path == ".":
            # Skip non-important directories at the root
            if os.path.isdir(path) and item not in IMPORTANT_DIRS:
                continue
            # Skip non-essential files at the root
            if os.path.isfile(path) and not item.endswith(('.php', '.json', '.js', '.md')):
                continue

        connector = "├── " if index < len(items) - 1 else "└── "
        
        # Output is directed to the file object
        print(prefix + connector + item, file=file)

        if os.path.isdir(path):
            extension = "│   " if index < len(items) - 1 else "    "
            # Recursive call, passing the file object along
            print_tree(path, prefix + extension, file=file)

if __name__ == "__main__":
    root = "."
    output_filename = "project_structure.txt"

    # Open the file for writing ('w') using a context manager
    with open(output_filename, 'w', encoding='utf-8') as f:
        # Write the header to the file
        print(f"Important Laravel Project Structure for: {os.path.abspath(root)}\n", file=f)
        
        # Call the function, passing the file object 'f'
        print_tree(root, file=f)
        
    print(f"✅ Directory tree successfully created and saved to **{output_filename}**")