# Role Based Access Control
A Stream can define roles that grant permissions to users who have those roles in the stream.
Users are granted the roles they have at a parent when joining a child.
They can also be assigned roles at a child that they do not have at the parent.

Default roles are defined for new root streams, one of which is admin.
The user that creates a root stream is automatically granted the admin role.
A stream can optionally define which role users should be granted by default when joining a stream. The default is the “User” role.

Permissions can not be applied directly to users, they must be applied through a role. This ensures easy determination of access for administrators of any given user.

# Permissions
Permissions available to be assigned to roles are as follows:

- Streams
  - Archive stream (stream:archive)
  - Create children streams (stream:create)
  - Edit/assign roles (stream:roles)
  - Edit name/description (stream:edit)
  - Edit discoverable/private field (stream:access)
  - Allow inviting users (stream:invite)
  - Allow joining child streams (stream:join)
      - This can be used to allow a guest user use case
  - Allow removing users (stream:kick)
  - Allow sending events (stream:write)
  - Allow reading events (stream:read)

# Default roles
“Admin” is default for the user that creates a stream.
“User” is the stream’s default role for new users by default.

| Permission | Admin | User |
| ------------- |:---:|:---:
| stream:archive | ✓ | |
| stream:create | ✓ | ✓ |
| stream:roles | ✓ | |
| stream:edit | ✓ | |
| stream:invite | ✓ | |
| stream:join | ✓ | ✓ |
| stream:kick | ✓ | |
| stream:write | ✓ | ✓ |
| stream:read | ✓ | ✓ |

