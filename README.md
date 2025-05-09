# Environment Middleware Addon for REDAXO

This addon provides an integrated solution for managing environments, OAuth authentication, and API proxying in REDAXO. It allows you to securely expose environment variables to the frontend while handling sensitive authentication details on the server.

## Features

- Configure and manage multiple environments with different settings
- Make environment variables accessible in the frontend via JavaScript
- Configure OAuth endpoints with authentication information
- Secure API communication with proxy endpoints that automatically inject authentication
- SSL certificate verification controls for development environments

## Installation

1. Install the addon via the REDAXO installer or manually upload it
2. Activate the addon in the REDAXO backend

## Configuration

### Environments

The Environments section allows you to:

1. **Define Multiple Environments**: Create sets of environment variables that can be used in different contexts (development, testing, production).
2. **Select Active Environment**: Choose which environment configuration should be active and available to the frontend.
3. **Configure JavaScript Variable Name**: Define the global JavaScript variable name that will be used to access environment variables in the browser (default: `ENV`).


### OAuth Configuration

The OAuth section allows you to:

1. **Configure Authentication Endpoints**: Set up the OAuth endpoint URLs and required credentials.
2. **Define OAuth Entry IDs**: Create unique identifiers for each OAuth endpoint.
3. **Configure Grant Types**: Support for various OAuth grant types, including Client Credentials.
4. **Manage Client IDs and Secrets**: Securely store authentication credentials.

### Proxy Configuration

The Proxy section allows you to:

1. **Create API Proxies**: Set up endpoints that act as intermediaries between your frontend and backend APIs.
2. **Link to OAuth Entries**: Connect proxies to the OAuth configurations for automatic authentication.
3. **Control SSL Verification**: Enable or disable SSL certificate verification per proxy for development environments.
4. **Secure API Access**: Keep sensitive authentication information on the server and out of frontend code.

Proxies help you maintain security by preventing exposure of credentials in client-side code. They automatically inject the correct authentication headers into API requests.

## How It Works

### 1. Environment Management

- Define multiple environments with different configuration values
- Set one environment as "active" to make it available in the frontend
- Access environment variables through a global JavaScript object
- Control which variable name is used (e.g., `window.ENV`, `window.CONFIG`, etc.)

### 2. OAuth Integration

- Configure OAuth endpoints with authentication details
- Support for different OAuth grant types
- Automatic token retrieval and renewal
- Secure storage of client secrets on the server

### 3. API Proxying

- Create proxy endpoints that forward requests to external APIs
- Automatically inject authentication headers into requests
- Keep sensitive credentials secure by handling authentication on the server
- Control SSL verification settings for each proxy individually

## Usage Examples

### Accessing Environment Variables in JavaScript

```javascript
// If using the default variable name "ENV"
console.log(window.ENV);

// Access a specific variable
const apiUrl = window.ENV.API_URL;

### Making Authenticated API Requests

Using the proxy (more secure):
```javascript
// The proxy will automatically add authentication headers
fetch('/index.php?rex-api-call=proxy_request&proxy=my-proxy-id&target=https://....');
```

The proxy approach is more secure because:
- Authentication credentials remain on the server
- Tokens are not exposed in frontend code
- The server can refresh expired tokens automatically

## Security Considerations

- Keep debug mode disabled in production environments
- SSL verification should only be disabled in development environments
- Use proxy endpoints instead of exposing tokens directly when possible
- Regularly rotate OAuth client secrets

## Support

For support, please create an issue on the [GitHub repository](https://github.com/oldjazjef/redaxo-addon-env-middleware).

## License

MIT License
