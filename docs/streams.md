# Streams
Streams are an abstract resource that cover a few different use-case specific concepts. The general purpose of a Stream is to denote an event stream and users that have access to it. Streams are organized into a non binary tree as detailed below.

## Visibility and Access
With streams the two core principles for authorization control are visibility and access. With visibility of a stream we ask the question “who can see that this stream exists and it’s metadata?”. With access we ask the question “who can access this stream’s events”. Many systems combine these two concepts; we’ve found separating them allows a much more dynamic system.

Streams have four features that control their visibility and access:
- Streams have a boolean field called “discoverable”.
- Streams have a boolean field called “private”.
- Streams are organized into a non-binary tree by having a nullable parent stream reference.
- Streams have user role access lists (WIP).

The “discoverable” field is used to mark if users that have access to a parent stream have visibility of the stream in question. Having visibility of a stream grants a user access to it’s name, and values of the stream’s discoverable and private fields. It also grants access to the list of users that have access to the stream.

The “private” field is used to mark if further access controls should apply to the stream for users who have access to the stream’s parent. If it’s enabled there are two ways for a user to gain access to the stream: either the user can be invited by a user who has access, or the user must have a role in the parent stream that is on the access list for the stream in question.

Root streams (those without a parent) are private and undiscoverable by default.

Users can gain access to undiscovered streams by being given the UUID of the stream, as long as it’s not private, in which the above access controls also apply. Users can also grant access to other users to any stream they have access to, so long as their role in the stream in question allows this.

## User Invites
Users are able to create invites for private streams that are used to grant another user access. When the invited user attempts to join a stream that is private, they must provide the invite code that the inviting user created. Auxiliary systems can be used to transmit this code and enforce the right user is using it, as the assumption that the user already exists at code creation time can not be confirmed.

## Stream lifecycle
Any user can create a root stream. Users can also create children streams to any stream they have access to.

Streams are never deleted currently. This might change in the future, in particular empty streams may be cleaned up.

Note: Users are automatically granted access to a stream they create when they do so.