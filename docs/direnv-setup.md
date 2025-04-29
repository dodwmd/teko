# Direnv Setup for Teko

This project uses [direnv](https://direnv.net/) to automatically set up your development environment when you enter the project directory.

## What is direnv?

Direnv is a shell extension that loads environment variables from a `.envrc` file when you enter a directory. It automatically:

- Activates the Python virtual environment
- Sets up environment variables for the project
- Adds project scripts to your PATH
- Sets up database connection variables

## Installation

### macOS
```bash
brew install direnv
```

### Ubuntu/Debian
```bash
sudo apt-get install direnv
```

### Arch Linux
```bash
sudo pacman -S direnv
```

## Shell Configuration

Add the following to your shell configuration file (`.bashrc`, `.zshrc`, etc.):

```bash
# For bash
eval "$(direnv hook bash)"

# For zsh
eval "$(direnv hook zsh)"

# For fish
direnv hook fish | source
```

Then restart your shell or source your configuration file.

## Usage

1. Navigate to the Teko project directory:
   ```bash
   cd /path/to/teko
   ```

2. The first time you enter the directory, you'll need to allow direnv to load the `.envrc` file:
   ```bash
   direnv allow
   ```

3. Now whenever you navigate to the project directory, direnv will automatically:
   - Activate the Python virtual environment
   - Add project scripts to your PATH
   - Set up environment variables
   - Configure database connections

## What's Included

The `.envrc` file:
- Activates the Python virtual environment in `agents/.venv`
- Creates the virtual environment if it doesn't exist
- Sets up environment variables for database connections
- Adds `scripts/` and `vendor/bin/` to your PATH
- Loads variables from `.env` (if it exists) for override values
