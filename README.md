# Environment Middleware Addon for REDAXO

This addon provides a way to manage environment variables and make them accessible in the frontend through JavaScript.

## Features

- Configure environment variables for different environments
- Access environment variables in JavaScript via `window.ENV` (configurable name)
- Integrate with OAuth services to automatically inject access tokens

## Installation

1. Install the addon via the REDAXO installer or manually upload it
2. Activate the addon in the REDAXO backend

## Configuration

### Settings

- **JavaScript Variable Name**: The name of the global JavaScript variable (default: `ENV`)
- **Debug Mode**: Enable detailed logging for troubleshooting
- **Active Environment**: Select which environment should be active and available in the frontend

### Environments

Create sets of environment variables that can be switched between. Each environment is a collection of key-value pairs that will be accessible in your frontend JavaScript.

To use OAuth tokens in your environment variables, create a variable that starts with `OAUTH_` followed by the Entry ID from the OAuth configuration. For example:

```
API_URL: https://api.example.com
OAUTH_my_api: true  # This will be replaced with the token data from the OAuth entry with ID "my_api"
```

### OAuth Configuration

Configure OAuth endpoints and credentials to automatically retrieve tokens for your frontend applications:

1. Create an OAuth entry with a unique Entry ID
2. Configure the OAuth URL and grant type
3. For Client Credentials grant type, provide the Client ID and Secret
4. Use the Entry ID in your environment variables with the `OAUTH_` prefix

## Usage in Frontend

Once configured, your environment variables will be available in the frontend as a JavaScript object:

```javascript
// If using the default variable name "ENV"
console.log(window.ENV);

// Access a specific variable
const apiUrl = window.ENV.API_URL;

// Access an OAuth token
const token = window.ENV.OAUTH_my_api.access_token;
```

## OAuth Token Structure

When using OAuth integration, the token data will typically contain:

```javascript
{
  "access_token": "ey...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "scope": "read write"
}
```

You can access these fields directly:

```javascript
// Get the access token
const accessToken = window.ENV.OAUTH_my_api.access_token;

// Use in fetch requests
fetch('https://api.example.com/data', {
  headers: {
    'Authorization': `Bearer ${accessToken}`
  }
});
```

## Troubleshooting

If you encounter issues with the addon:

1. Enable Debug Mode in the settings
2. Check the REDAXO system log for detailed information
3. Verify that your OAuth credentials are correct
4. Check the browser console for JavaScript errors

## Support

For support, please create an issue on the [GitHub repository](https://github.com/oldjazjef/redaxo-addon-env-middleware).
