# Authentication
- The API requires a signed JWT (RSA) for all requests as an Authentication header.
- Separate Identity Provider (IP) is responsible for authentication.
- The `sub` field on the JWT is used as a unique identifier that is used to determine access for the JWT.
- The `aud` and `iss` are validated against environment variables at run time.
