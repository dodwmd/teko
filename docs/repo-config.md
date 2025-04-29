# Repository Configuration as Code

Teko uses a "configuration as code" approach to manage GitHub repository settings. This ensures that repository settings are version-controlled, documented, and automatically applied.

## Overview

The repository configuration system consists of:

1. **Configuration File**: `.github/config.json` defines all repository settings
2. **Automation Workflow**: `.github/workflows/repo-config.yml` applies the settings

## Configuration Options

The `config.json` file includes:

### Repository Settings
- Basic repository metadata (name, description, topics)
- Feature toggles (issues, wiki, projects)
- Merge strategies (squash, merge, rebase)
- Default branch settings

### Branch Protection
- Required status checks
- Required reviews
- Conversation resolution requirements
- Linear history requirements

### Environments
- Production and staging environments
- Deployment protection rules
- Review requirements

### Team Access
- Team permissions
- Collaborator access levels

## How to Update Settings

1. Edit the `.github/config.json` file
2. Commit and push changes to the main branch
3. The workflow will automatically apply your changes

## Manual Trigger

You can also manually trigger the workflow:

1. Go to the "Actions" tab in your GitHub repository
2. Select the "Repository Configuration" workflow
3. Click "Run workflow"
4. Optionally select "Force update all settings"

## Required Permissions

For this workflow to function properly, you need:

### Token Setup

1. Create a Personal Access Token (PAT) with these permissions:
   - `repo` (Full control of repositories)
   - `admin:org` (If using team settings)

2. Add this token as a repository secret named `REPO_CONFIG_TOKEN`

### Authentication Configuration

In your repository:
1. Go to Settings → Secrets and variables → Actions
2. Add a new repository secret
3. Name: `REPO_CONFIG_TOKEN`
4. Value: Your PAT created above

## Security Considerations

- The access token has elevated permissions
- Only administrators should have permission to edit the config file
- Consider using branch protection on `.github/config.json` itself

## Troubleshooting

If the workflow fails:

1. Check the workflow logs for specific error messages
2. Verify your PAT has the required permissions
3. Ensure the JSON syntax in the config file is valid
4. Confirm that branch names in protection rules match existing branches
