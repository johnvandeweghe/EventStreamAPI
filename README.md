# Event Stream API

This is a RESTful API for Event Streams.

# Installation

Use Docker.

## Dependencies

### Database

The Event Stream API requires a database. Migrations are provided for a Postgres DB, but the system is API agnostic.

#### Running migrations

Run this command to trigger migrations:
TODO

## Environmental Variables

### `DATABASE_URL`

This should be set to the connection uri (DSN) to the DB.

Example:
```postgresql://user:password@hostname:5432/dbname?serverVersion=11&charset=utf8```

### `JWKS_URI`

This should be set to the URI to fetch the JWK set from. 

Example:
```https://postchat.us.auth0.com/.well-known/jwks.json```

### `JWT_ISSUER`

This should be set to the issuer string that should be trusted in signed JWTs.

Example:
```https://postchat.us.auth0.com/```

### `JWT_AUDIENCE`

This should be set to the audience that represents this API. Tokens without this audience will be rejected.

Example:
```https://api.getpostchat.com/```

### `CORS_ALLOW_ORIGIN`

This is the origins allowed for CORS. It is a regex string.