# Repository Configuration as Code

Teko uses a "configuration as code" approach to manage GitHub repository settings. This ensures that repository settings are version-controlled, documented, and automatically applied.

## Overview

The repository configuration system consists of:

1. **Configuration File**: `.github/config.json` defines repository settings
2. **Automation Workflow**: `.github/workflows/repo-config.yml` applies the settings

## Configuration Options

The `config.json` file includes:

### Repository Settings
- Basic repository metadata (name, description, topics)
- Feature toggles (issues, wiki, projects)
- Merge strategies (squash, merge, rebase)
- Default branch settings

### Branch Protection
- Required status checks for the master branch
- Linear history requirements
- Conversation resolution requirements

## How to Update Settings

1. Edit the `.github/config.json` file
2. Commit and push changes to the master branch
3. The workflow will automatically apply your changes

## Manual Trigger

You can also manually trigger the workflow:

1. Go to the "Actions" tab in your GitHub repository
2. Select the "Repository Configuration" workflow
3. Click "Run workflow"

## Permissions

The workflow uses the standard `GITHUB_TOKEN` that is automatically provided by GitHub Actions, so no additional setup is required.

## Troubleshooting

If the workflow fails:

1. Check the workflow logs for specific error messages
2. Verify the JSON syntax in the config file is valid
3. Confirm that branch names in protection rules match existing branches
